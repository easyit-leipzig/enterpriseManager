<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require dirname(__DIR__, 2) . '/system/ui/layout.php';

$user = enterprise_require_auth('../../');
$pdo = enterprise_pdo();
enterprise_upgrade($pdo);
$env = enterprise_env(dirname(__DIR__, 2) . '/DataForm5-Core/.env');
$error = '';
$messages = [];
$form = [
    'name' => '',
    'slug' => '',
    'product_type' => 'dataform',
    'database_name' => '',
];

function project_provision_valid_db_name(string $name): bool
{
    return preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/', $name) === 1;
}
function project_provision_quote(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}
function project_provision_server(array $env): PDO
{
    foreach (['PROJECT_DB_HOST','PROJECT_DB_PORT','PROJECT_DB_USERNAME'] as $key) {
        if (trim((string)($env[$key] ?? '')) === '') {
            throw new RuntimeException("Projekt-DB-Konfiguration {$key} fehlt. Führen Sie zuerst Setup-Schritt 6 aus.");
        }
    }
    return new PDO(
        'mysql:host=' . $env['PROJECT_DB_HOST'] . ';port=' . (int)$env['PROJECT_DB_PORT'] . ';charset=utf8mb4',
        (string)$env['PROJECT_DB_USERNAME'],
        (string)($env['PROJECT_DB_PASSWORD'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
function project_provision_database_exists(PDO $server, string $database): bool
{
    $st = $server->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=?');
    $st->execute([$database]);
    return $st->fetchColumn() !== false;
}
function project_provision_create_database(PDO $server, string $database): void
{
    $q = project_provision_quote($database);
    try {
        $server->exec("CREATE DATABASE {$q} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        if (str_contains(strtolower($e->getMessage()), 'collation')) {
            $server->exec("CREATE DATABASE {$q} CHARACTER SET utf8mb4");
        } else {
            throw $e;
        }
    }
    if (!project_provision_database_exists($server, $database)) {
        throw new RuntimeException('Die neue Projektdatenbank konnte nach CREATE DATABASE nicht verifiziert werden.');
    }
}
function project_provision_migration_table_exists(PDO $db): bool
{
    return (int)$db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='migrations'")->fetchColumn() > 0;
}
function project_provision_migration_record(PDO $db, string $name): ?array
{
    if (!project_provision_migration_table_exists($db)) return null;
    $st=$db->prepare('SELECT migration,checksum FROM migrations WHERE migration=? LIMIT 1');
    $st->execute([$name]);
    $row=$st->fetch(PDO::FETCH_ASSOC);
    return is_array($row)?$row:null;
}
function project_provision_register_migration(PDO $db, string $name, string $checksum): void
{
    if (!project_provision_migration_table_exists($db)) throw new RuntimeException("Migrationstabelle fehlt nach {$name}.");
    $st=$db->prepare('INSERT INTO migrations(migration,checksum) VALUES (?,?) ON DUPLICATE KEY UPDATE checksum=checksum');
    $st->execute([$name,$checksum]);
}
function project_provision_install_schema(PDO $server, string $database, string $directory): array
{
    $server->exec('USE ' . project_provision_quote($database));
    $files=glob(rtrim($directory,'/\\').'/*.php')?:[];
    sort($files,SORT_NATURAL);
    if ($files===[]) throw new RuntimeException('Keine Projekt-Schema-Dateien gefunden.');
    $done=[];
    foreach($files as $file){
        $name=basename($file);
        $checksum=hash_file('sha256',$file);
        if(!is_string($checksum)||strlen($checksum)!==64) throw new RuntimeException("Checksum für {$name} konnte nicht bestimmt werden.");
        $existing=project_provision_migration_record($server,$name);
        if($existing!==null){
            if(!hash_equals((string)$existing['checksum'],$checksum)) throw new RuntimeException("Migration {$name} wurde verändert.");
            $done[]='SKIP '.$name.' (Checksum OK)';
            continue;
        }
        $installer=require $file;
        if(!is_callable($installer)) throw new RuntimeException('Ungültige Schema-Datei: '.$name);
        $installer($server);
        project_provision_register_migration($server,$name,$checksum);
        $done[]='APPLY '.$name;
    }
    return $done;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        enterprise_check_csrf((string)($_POST['csrf_token'] ?? ''));
        foreach(array_keys($form) as $key) $form[$key]=trim((string)($_POST[$key] ?? $form[$key]));

        if ($form['name']==='') throw new RuntimeException('Projektname ist erforderlich.');
        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,158}$/',$form['slug'])) throw new RuntimeException('Der technische Slug ist ungültig.');
        if ($form['product_type']!=='dataform') throw new RuntimeException('Aktuell kann nur DataForm provisioniert werden.');
        if (!project_provision_valid_db_name($form['database_name'])) throw new RuntimeException('Der Datenbankname ist ungültig.');

        $dup=$pdo->prepare('SELECT id,name,database_name FROM projects WHERE slug=? OR database_name=? LIMIT 1');
        $dup->execute([$form['slug'],$form['database_name']]);
        if($existing=$dup->fetch()) throw new RuntimeException('Slug oder Datenbankname wird bereits vom Projekt „'.(string)$existing['name'].'“ verwendet.');

        $server=project_provision_server($env);
        if(project_provision_database_exists($server,$form['database_name'])) {
            throw new RuntimeException('Die Datenbank `'.$form['database_name'].'` existiert bereits. Verwenden Sie dafür „Vorhandenes Projekt registrieren“ oder wählen Sie einen neuen Datenbanknamen.');
        }

        $created=false;
        try {
            project_provision_create_database($server,$form['database_name']);
            $created=true;
            $schemaResults=project_provision_install_schema($server,$form['database_name'],dirname(__DIR__,2).'/installer/schema/project');

            // Projekt erst nach erfolgreicher DB- und Schema-Provisionierung registrieren.
            $stmt=$pdo->prepare("INSERT INTO projects(name,slug,product_type,database_driver,database_name,status) VALUES (?,?,?,'mysql',?,'active')");
            $stmt->execute([$form['name'],$form['slug'],$form['product_type'],$form['database_name']]);
            $id=(int)$pdo->lastInsertId();

            enterprise_audit($pdo,(int)$user['id'],'project.provision','project',(string)$id,[
                'name'=>$form['name'],'slug'=>$form['slug'],'product_type'=>$form['product_type'],
                'database_name'=>$form['database_name'],'database_created'=>true,'schema'=>$schemaResults,
            ]);
            enterprise_event_dispatch('project.provisioned',[
                'project_id'=>$id,'name'=>$form['name'],'slug'=>$form['slug'],
                'product_type'=>$form['product_type'],'database_name'=>$form['database_name'],
            ],['user_id'=>(int)$user['id']]);

            header('Location: view.php?id='.$id);
            exit;
        } catch(Throwable $e) {
            // Wenn DB neu erzeugt wurde, aber Provisionierung/Registrierung scheitert,
            // die neu erzeugte DB entfernen, damit kein halbfertiger Projektstand bleibt.
            if($created){
                try{$server->exec('DROP DATABASE IF EXISTS '.project_provision_quote($form['database_name']));}catch(Throwable){}
            }
            throw $e;
        }
    }
} catch(Throwable $e) {
    $error=$e->getMessage();
}

ob_start();
render_breadcrumbs([['label'=>'Enterprise','href'=>'../dashboard.php'],['label'=>'Projekte','href'=>'index.php'],['label'=>'Neues Projekt anlegen','href'=>'']]);
?>
<section class="hero"><span class="badge">HF11 · Project Provisioning</span><h1>Neues Projekt anlegen</h1>
<p>Erzeugt eine neue Projektdatenbank, installiert das DataForm-Basisschema und registriert das Projekt anschließend in der Enterprise-Administration.</p>
<div class="actions"><a class="button secondary" href="register.php">Vorhandenes Projekt registrieren</a><a class="button secondary" href="restore.php">Projektsicherung wiederherstellen</a></div></section>
<?php if($error):?><div class="notice error" role="alert"><?=e($error)?></div><?php endif;?>
<section class="card"><form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?=e(enterprise_csrf())?>">
<label>Projektname<input name="name" required value="<?=e($form['name'])?>" placeholder="Testprojekt 63"></label>
<label>Technischer Slug<input name="slug" required pattern="[a-z0-9][a-z0-9-]{1,158}" value="<?=e($form['slug'])?>" placeholder="testprojekt-63"><small>Kleinbuchstaben, Ziffern und Bindestriche.</small></label>
<label>Produkt<select name="product_type"><option value="dataform" selected>DataForm</option></select></label>
<label>Datenbankname<input name="database_name" required pattern="[A-Za-z][A-Za-z0-9_]{1,62}" value="<?=e($form['database_name'])?>" placeholder="easyit_testprojekt_63"><small>Die Datenbank muss noch nicht existieren.</small></label>
<div class="form-span notice"><strong>Ablauf:</strong> Datenbank erzeugen → Projektschema installieren → Projekt registrieren. Bei einem Fehler vor Abschluss wird die neu erzeugte Datenbank wieder entfernt.</div>
<div class="form-span button-row"><button class="button" type="submit">Projekt vollständig anlegen</button><a class="button secondary" href="index.php">Abbrechen</a></div>
</form></section>
<?php
$content=ob_get_clean();
render_page(['title'=>'Neues Projekt anlegen','active'=>'projects','base'=>'../../','content'=>$content,'app_nav'=>true,'user'=>$user,'help'=>[
'title'=>'Projekt-Provisionierung','location'=>'Enterprise → Projekte → Neues Projekt anlegen','short'=>'Neue DataForm-Projekte inklusive eigener Datenbank und Basisschema anlegen.','goal'=>'Projekt ohne manuellen Datenbank-Assistenten vollständig provisionieren.','steps'=>['Projektname und Slug festlegen.','Neuen Datenbanknamen wählen.','Projekt vollständig anlegen.','Ergebnis in Projektliste und Datenbankserver prüfen.'],'tips'=>['Existierende Datenbanken werden nicht überschrieben.','Für bestehende Datenbanken verwenden Sie „Vorhandenes Projekt registrieren“.','Die Registrierung erfolgt erst nach erfolgreicher Schema-Installation.']
]]);
