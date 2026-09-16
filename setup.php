<?php
declare(strict_types=1);

if(session_status()!==PHP_SESSION_ACTIVE)session_start();
require __DIR__.'/system/ui/layout.php';

$kernel=require __DIR__.'/DataForm5-Core/bootstrap/app.php';
$installer=$kernel->container()->get(\DataForm5\Installer\Core\EnterpriseInstaller::class);
if(empty($_SESSION['setup_csrf']))$_SESSION['setup_csrf']=bin2hex(random_bytes(32));
$csrf=(string)$_SESSION['setup_csrf'];
$status=$installer->status();
$message='';$error='';$result=null;

/**
 * Liefert ausschließlich die in der aktuellen PHP-Laufzeit tatsächlich
 * nutzbaren Speicher-/Datenbanktreiber. CSV benötigt keinen PDO-Treiber.
 */
function setup_runtime_driver_options(): array
{
    $availablePdo = class_exists(PDO::class) ? PDO::getAvailableDrivers() : [];
    $all = [
        'mysql' => ['label' => 'MariaDB / MySQL', 'pdo' => 'mysql'],
        'pgsql' => ['label' => 'PostgreSQL', 'pdo' => 'pgsql'],
        'oracle' => ['label' => 'Oracle XE', 'pdo' => 'oci'],
        'mssql' => ['label' => 'Microsoft SQL Server', 'pdo' => 'sqlsrv'],
        'csv' => ['label' => 'CSV', 'pdo' => null],
        'sqlite' => ['label' => 'SQLite', 'pdo' => 'sqlite'],
    ];
    $options = [];
    foreach ($all as $driver => $meta) {
        if ($meta['pdo'] === null || in_array($meta['pdo'], $availablePdo, true)) {
            $options[$driver] = $meta['label'];
        }
    }
    return $options;
}

$driverOptions = setup_runtime_driver_options();
$defaultDriver = isset($driverOptions['mysql']) ? 'mysql' : (array_key_first($driverOptions) ?? 'csv');
$unavailableSelections = [];
// RC1.1 legacy installer driver marker: ['mysql','csv','sqlite','pgsql','oracle','mssql']
$form=[
 'admin_driver'=>$defaultDriver,'admin_csv_base'=>'storage/admin-csv','admin_sqlite_base'=>'storage/admin-sqlite',
 'admin_db_host'=>'127.0.0.1','admin_db_port'=>'3306','admin_oracle_service'=>'XEPDB1','admin_db_maintenance_database'=>'postgres','db_database'=>'easyit_admin','admin_db_schema'=>'easyit_admin','admin_db_username'=>'root','admin_db_password'=>'',
 'project_driver'=>$defaultDriver,'project_csv_base'=>'storage/project-csv','project_sqlite_base'=>'storage/project-sqlite','project_name'=>'Demo','project_database'=>'easyit_project_demo',
 'project_db_host'=>'127.0.0.1','project_db_port'=>'3306','project_oracle_service'=>'XEPDB1','project_db_maintenance_database'=>'postgres','project_db_schema'=>'easyit_project_demo','project_db_username'=>'root','project_db_password'=>'',
 'admin_username'=>'admin','admin_email'=>'','admin_password'=>'','admin_password_confirm'=>'',
 'products'=>['dataform'],'environment'=>'production','timezone'=>'Europe/Berlin',
];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $adminDriver=strtolower((string)($_POST['admin_driver']??$defaultDriver));if(in_array($adminDriver,['postgres','postgresql'],true))$adminDriver='pgsql';
 $projectDriver=strtolower((string)($_POST['project_driver']??$defaultDriver));if(in_array($projectDriver,['postgres','postgresql'],true))$projectDriver='pgsql';
 if(!isset($driverOptions[$adminDriver]))$unavailableSelections[]='Administrationsspeicher: '.$adminDriver;
 if(!isset($driverOptions[$projectDriver]))$unavailableSelections[]='Projektdatenspeicher: '.$projectDriver;
 $form=[
  'admin_driver'=>isset($driverOptions[$adminDriver])?$adminDriver:$defaultDriver,
  'admin_csv_base'=>trim((string)($_POST['admin_csv_base']??'storage/admin-csv')),
  'admin_sqlite_base'=>trim((string)($_POST['admin_sqlite_base']??'storage/admin-sqlite')),
  'admin_db_host'=>trim((string)($_POST['admin_db_host']??$_POST['db_host']??'127.0.0.1')),
  'admin_db_port'=>(string)($_POST['admin_db_port']??$_POST['db_port']??($adminDriver==='pgsql'?'5432':($adminDriver==='oracle'?'1521':($adminDriver==='mssql'?'1433':'3306')))),
  'db_database'=>trim((string)($_POST['db_database']??'easyit_admin')),
  'admin_db_schema'=>trim((string)($_POST['admin_db_schema']??'easyit_admin')),
  'admin_db_maintenance_database'=>trim((string)($_POST['admin_db_maintenance_database']??'postgres')),
  'admin_oracle_service'=>trim((string)($_POST['admin_oracle_service']??'XEPDB1')),
  'admin_db_username'=>trim((string)($_POST['admin_db_username']??$_POST['db_username']??($adminDriver==='pgsql'?'postgres':($adminDriver==='oracle'?'easyit_admin':'root')))),
  'admin_db_password'=>(string)($_POST['admin_db_password']??$_POST['db_password']??''),
  'project_driver'=>isset($driverOptions[$projectDriver])?$projectDriver:$defaultDriver,
  'project_csv_base'=>trim((string)($_POST['project_csv_base']??'storage/project-csv')),
  'project_name'=>trim((string)($_POST['project_name']??'Demo')),
  'project_sqlite_base'=>trim((string)($_POST['project_sqlite_base']??'storage/project-sqlite')),
  'project_database'=>trim((string)($_POST['project_database']??'easyit_project_demo')),
  'project_db_host'=>trim((string)($_POST['project_db_host']??$_POST['db_host']??'127.0.0.1')),
  'project_db_port'=>(string)($_POST['project_db_port']??$_POST['db_port']??($projectDriver==='pgsql'?'5432':($projectDriver==='oracle'?'1521':($projectDriver==='mssql'?'1433':'3306')))),
  'project_oracle_service'=>trim((string)($_POST['project_oracle_service']??'XEPDB1')),
  'project_db_schema'=>trim((string)($_POST['project_db_schema']??'easyit_project_demo')),
  'project_db_maintenance_database'=>trim((string)($_POST['project_db_maintenance_database']??'postgres')),
  'project_db_username'=>trim((string)($_POST['project_db_username']??$_POST['db_username']??($projectDriver==='pgsql'?'postgres':($projectDriver==='oracle'?'easyit_project':'root')))),
  'project_db_password'=>(string)($_POST['project_db_password']??$_POST['db_password']??''),
  'admin_username'=>trim((string)($_POST['admin_username']??'admin')),
  'admin_email'=>trim((string)($_POST['admin_email']??'')),'admin_password'=>(string)($_POST['admin_password']??''),
  'admin_password_confirm'=>(string)($_POST['admin_password_confirm']??''),
  'products'=>array_values(array_intersect(['dataform','dialog','nachhilfe','csv-engine','sqlite-engine','pgsql-engine','oracle-engine','mssql-engine'],(array)($_POST['products']??[]))),
  'environment'=>in_array((string)($_POST['environment']??''),['production','development'],true)?(string)$_POST['environment']:'production',
  'timezone'=>trim((string)($_POST['timezone']??'Europe/Berlin')),
 ];
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        if(!hash_equals($csrf,(string)($_POST['csrf_token']??'')))throw new RuntimeException('CSRF-Prüfung fehlgeschlagen.');
        if($unavailableSelections!==[])throw new RuntimeException('Nicht verfügbarer Datenbanktreiber wurde übermittelt. Fehlende PDO-Treiber dürfen nicht als Datenquelle gewählt werden: '.implode(', ',$unavailableSelections));
        $password=(string)($_POST['admin_password']??'');
        $confirm=(string)($_POST['admin_password_confirm']??'');
        if($password!==$confirm)throw new RuntimeException('Superadmin-Kennwörter stimmen nicht überein.');
        if($form['project_name']==='')throw new RuntimeException('Projektname ist erforderlich.');
        if($form['admin_driver']==='pgsql' && preg_match('/^[A-Za-z][A-Za-z0-9_]{0,62}$/',$form['admin_db_schema'])!==1)throw new RuntimeException('Ungültiges PostgreSQL-Administrationsschema.');
        if($form['project_driver']==='pgsql' && preg_match('/^[A-Za-z][A-Za-z0-9_]{0,62}$/',$form['project_db_schema'])!==1)throw new RuntimeException('Ungültiges PostgreSQL-Projektschema.');
        $result=$installer->install([
            'environment'=>$form['environment'],
            'timezone'=>$form['timezone'],
            'project_name'=>$form['project_name'],
            'database'=>[
                'driver'=>$form['admin_driver'],
                'base_path'=>$form['admin_csv_base'],
                'sqlite_base_path'=>$form['admin_sqlite_base'],
                'host'=>$form['admin_db_host'],
                'service'=>$form['admin_oracle_service'],
                'port'=>(int)$form['admin_db_port'],
                'database'=>$form['db_database'],
                'schema'=>$form['admin_db_schema'],
                'maintenance_database'=>$form['admin_db_maintenance_database'],
                'username'=>$form['admin_db_username'],
                'password'=>$form['admin_db_password'],
            ],
            'project_database'=>[
                'driver'=>$form['project_driver'],
                'base_path'=>$form['project_csv_base'],
                'sqlite_base_path'=>$form['project_sqlite_base'],
                'database'=>$form['project_database'],
                'schema'=>$form['project_db_schema'],
                'maintenance_database'=>$form['project_db_maintenance_database'],
                'host'=>$form['project_db_host'],
                'service'=>$form['project_oracle_service'],
                'port'=>(int)$form['project_db_port'],
                'username'=>$form['project_db_username'],
                'password'=>$form['project_db_password'],
            ],
            'admin'=>[
                'username'=>$form['admin_username'],
                'email'=>$form['admin_email'],
                'password'=>$password,
            ],
            'products'=>$form['products'],
        ]);
        $message='Installation erfolgreich abgeschlossen. Das Health-Gate ist grün und der Install-Lock wurde gesetzt.';
        $status=$installer->status();
    }catch(Throwable $e){$error=$e->getMessage();}
}

ob_start();
?>
<section class="hero"><span class="badge">RC1.1 · STAND 4 MSSQL + DF/DS</span><h1>Installation & Administration</h1>
<p>Neuinstallation, Konfigurationsreparatur und Datenbankprüfung bleiben auch nach einer abgeschlossenen Installation erreichbar.</p>
<div class="actions">
<a class="button" href="installer/database.php">Datenbank-Assistent öffnen</a>
<a class="button secondary" href="recovery.php">Recovery / Reset</a>
<a class="button secondary" href="app/dashboard.php">Enterprise-Dashboard</a>
</div>
</section>

<?php if($message):?><div class="notice success"><?=e($message)?></div><?php endif;?>
<?php if($error):?><div class="notice error"><?=e($error)?></div><?php endif;?>

<section class="card"><h2>1. Systemprüfung</h2>
<table><thead><tr><th>Prüfung</th><th>Status</th></tr></thead><tbody>
<?php foreach((array)($status['inspection']['checks']??$status['inspection']['requirements']??[]) as $index=>$value):
if(is_array($value)){
    $name=(string)($value['name']??$index);
    $ok=(bool)($value['passed']??$value['ok']??false);
    $required=(bool)($value['required']??true);
    $detail=trim((string)($value['message']??''));
}else{
    $name=(string)$index;
    $ok=(bool)$value;
    $required=true;
    $detail='';
}
$statusLabel=$ok?'<strong>OK</strong>':($required?'Fehler':'Optional');?>
<tr><td><?=e($name)?><?php if($detail!==''):?><br><small><?=e($detail)?></small><?php endif;?></td><td><?=$statusLabel?></td></tr>
<?php endforeach;?>
<?php foreach((array)($status['inspection']['required_extensions']??[]) as $name=>$ok):?>
<tr><td>Extension <?=e((string)$name)?></td><td><?=$ok?'<strong>OK</strong>':'Fehlt'?></td></tr>
<?php endforeach;?>
</tbody></table></section>

<?php if($status['installed']??false):?>
<section class="card"><h2>Installation abgeschlossen – Wartungsmodus verfügbar</h2>
<p>Der Install-Lock ist gesetzt. Eine Neuinstallation wird nicht automatisch gestartet. Sie können Konfiguration und Datenbanken trotzdem kontrolliert prüfen oder reparieren.</p>
<div class="actions">
<a class="button" <?= easyit_button_attributes('system_reparieren') ?> href="installer/database.php">Datenbanken / .env prüfen und reparieren</a>
<a class="button secondary" <?= easyit_button_attributes('suchen') ?> href="health.php">Systemprüfung</a>
<a class="button secondary" href="recovery.php">Recovery / Reset</a>
<a class="button secondary" href="login.php">Zur Anmeldung</a>
</div>
<p><strong>Sicherheitsregel:</strong> Bestehende Datenbanken werden vom Datenbank-Assistenten nicht ungefragt überschrieben; registrierte Migrationen werden anhand ihrer Checksums mit <code>SKIP … Checksum OK</code> erkannt.</p>
</section>
<?php else:?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?=e($csrf)?>">
<section class="card"><h2>2. Administrations- und Projektspeicher</h2>
<p>Administrationsspeicher und Projektdatenspeicher werden unabhängig gewählt. In dieser PHP-Laufzeit verfügbar: <?=e(implode(', ', array_values($driverOptions)))?>. Datenbanktypen mit fehlendem PDO-Treiber werden nicht angeboten.</p>
<h3>Administrationsspeicher</h3>
<div class="form-grid">
<label>Treiber<select name="admin_driver" id="admin_driver">
<?php foreach($driverOptions as $driver=>$label):?><option value="<?=e($driver)?>" <?=$form['admin_driver']===$driver?'selected':''?>><?=e($label)?></option><?php endforeach;?>
</select></label>
<label>Administrationsdatenbank<input name="db_database" value="<?=e($form['db_database'])?>" required pattern="[A-Za-z][A-Za-z0-9_]{1,62}"></label>
<label data-admin-pgsql>PostgreSQL-Schema<input name="admin_db_schema" value="<?=e($form['admin_db_schema'])?>" required pattern="[A-Za-z][A-Za-z0-9_]{0,62}"></label>
<label data-admin-csv>CSV-Basisordner<input name="admin_csv_base" value="<?=e($form['admin_csv_base'])?>" placeholder="storage/admin-csv"></label>
<label data-admin-sqlite>SQLite-Basisordner<input name="admin_sqlite_base" value="<?=e($form['admin_sqlite_base'])?>" placeholder="storage/admin-sqlite"></label>
</div>
<div class="form-grid" data-admin-sql>
<label>Admin-DB Host<input name="admin_db_host" value="<?=e($form['admin_db_host'])?>"></label>
<label>Admin-DB Port<input name="admin_db_port" type="number" min="1" max="65535" value="<?=e($form['admin_db_port'])?>"></label>
<label data-admin-pgsql>PostgreSQL-Wartungsdatenbank<input name="admin_db_maintenance_database" value="<?=e($form['admin_db_maintenance_database'])?>" placeholder="postgres"></label>
<label data-admin-oracle>Oracle Service/PDB<input name="admin_oracle_service" value="<?=e($form['admin_oracle_service'])?>" placeholder="XEPDB1"></label>
<label>Admin-DB Benutzer<input name="admin_db_username" value="<?=e($form['admin_db_username'])?>"></label>
<label>Admin-DB Kennwort<input name="admin_db_password" type="password" value="<?=e($form['admin_db_password'])?>" autocomplete="new-password" data-password-field></label>
</div>
<p data-admin-csv><small>CSV speichert Benutzer, Rollen, Projekte, Audit-Protokoll, Lizenzen, Modulkonfiguration und Migrationsstatus persistent unter <code>Basisordner/Name</code>.</small></p>
<p data-admin-sqlite><small>SQLite speichert die vollständige Enterprise-Administration in <code>Basisordner/Name.sqlite</code>. Benötigt wird <code>pdo_sqlite</code>.</small></p>
<p data-admin-pgsql><small>PostgreSQL verwendet immer die Kombination <strong>Datenbank + Schema</strong>. Die Datenbank und das angegebene Schema werden bei ausreichenden Rechten automatisch angelegt und anschließend verifiziert. Benötigt wird <code>pdo_pgsql</code>; Standardport ist 5432.</small></p>
<p data-admin-mssql><small>Microsoft SQL Server benötigt <code>pdo_sqlsrv</code>; Standardport ist 1433. Verschlüsselte Verbindungen sind die Voreinstellung.</small></p>
<p data-admin-oracle><small>Oracle XE verwendet ein vorhandenes Schema/User im PDB (Standardservice <code>XEPDB1</code>). Benötigt wird <code>pdo_oci</code>; Standardport ist 1521.</small></p>

<h3>Projektdatenspeicher</h3>
<div class="form-grid">
<label>Projektname<input name="project_name" value="<?=e($form['project_name'])?>" required placeholder="Demo"><small>Dieses Projekt wird nach erfolgreicher Installation sofort in der Projektliste registriert.</small></label>
<label>Treiber<select name="project_driver" id="project_driver">
<?php foreach($driverOptions as $driver=>$label):?><option value="<?=e($driver)?>" <?=$form['project_driver']===$driver?'selected':''?>><?=e($label)?></option><?php endforeach;?>
</select></label>
<label>Projekt-Datenbank-/Speichername<input name="project_database" value="<?=e($form['project_database'])?>" required pattern="[A-Za-z][A-Za-z0-9_]{1,62}"></label>
<label data-project-pgsql>PostgreSQL-Schema<input name="project_db_schema" value="<?=e($form['project_db_schema'])?>" required pattern="[A-Za-z][A-Za-z0-9_]{0,62}"></label>
<label data-project-csv>CSV-Basisordner<input name="project_csv_base" value="<?=e($form['project_csv_base'])?>" placeholder="storage/project-csv"></label>
<label data-project-sqlite>SQLite-Basisordner<input name="project_sqlite_base" value="<?=e($form['project_sqlite_base'])?>" placeholder="storage/project-sqlite"></label>
</div>
<div class="form-grid" data-project-sql>
<label>Projekt-DB Host<input name="project_db_host" value="<?=e($form['project_db_host'])?>"></label>
<label>Projekt-DB Port<input name="project_db_port" type="number" min="1" max="65535" value="<?=e($form['project_db_port'])?>"></label>
<label data-project-pgsql>PostgreSQL-Wartungsdatenbank<input name="project_db_maintenance_database" value="<?=e($form['project_db_maintenance_database'])?>" placeholder="postgres"></label>
<label data-project-oracle>Oracle Service/PDB<input name="project_oracle_service" value="<?=e($form['project_oracle_service'])?>" placeholder="XEPDB1"></label>
<label>Projekt-DB Benutzer<input name="project_db_username" value="<?=e($form['project_db_username'])?>"></label>
<label>Projekt-DB Kennwort<input name="project_db_password" type="password" value="<?=e($form['project_db_password'])?>" autocomplete="new-password" data-password-field></label>
</div>
<p data-project-csv><small>DataForms, Bindings und physische Datensätze werden als <code>|</code>-getrennte CSV-Dateien mit Pflicht-ID <code>id</code> gespeichert.</small></p>
<p data-project-sqlite><small>DataForms, Bindings und physische Datensätze liegen in <code>Basisordner/Name.sqlite</code>.</small></p>
<p data-project-mssql><small>DataForms, Bindings, Tabellen und Datensätze können vollständig in Microsoft SQL Server liegen.</small></p>
<p data-project-pgsql><small>DataForms, Bindings, Tabellen und Datensätze liegen vollständig in der konfigurierten PostgreSQL-Kombination <strong>Datenbank + Schema</strong>. Datenbank und Schema werden bei ausreichenden Rechten automatisch angelegt und verifiziert.</small></p>
<p data-project-oracle><small>DataForms, Bindings, Tabellen und Datensätze liegen vollständig im gewählten Oracle-XE-Schema/User.</small></p>
<div class="notice"><strong>Treiberregel:</strong> Ein SQL-Datenbanktyp wird nur angeboten, wenn der zugehörige PDO-Treiber in der aktuell laufenden PHP-Version registriert ist. CSV bleibt ohne PDO verfügbar.</div>
</section>
<section class="card"><h2>3. Erster Superadministrator</h2>
<div class="form-grid">
<label>Benutzername<input name="admin_username" value="<?=e($form['admin_username'])?>" required></label>
<label>E-Mail<input name="admin_email" type="email" value="<?=e($form['admin_email'])?>"></label>
<label>Kennwort<input name="admin_password" type="password" value="<?=e($form['admin_password'])?>" required data-password-field minlength="12" autocomplete="new-password"></label>
<label>Kennwort wiederholen<input name="admin_password_confirm" type="password" value="<?=e($form['admin_password_confirm'])?>" required data-password-field minlength="12" autocomplete="new-password"></label>
</div></section>

<section class="card"><h2>4. Produkte</h2>
<label><input type="checkbox" name="products[]" value="dataform" <?=in_array('dataform',$form['products'],true)?'checked':''?>> DataForm</label><br>
<label><input type="checkbox" name="products[]" value="dialog" <?=in_array('dialog',$form['products'],true)?'checked':''?>> Dialog</label><br>
<label><input type="checkbox" name="products[]" value="nachhilfe" <?=in_array('nachhilfe',$form['products'],true)?'checked':''?>> Nachhilfe</label><br>
<label><input type="checkbox" name="products[]" value="csv-engine" <?=in_array('csv-engine',$form['products'],true)?'checked':''?>> CSV-Engine</label><br>
<?php if(isset($driverOptions['sqlite'])):?><label><input type="checkbox" name="products[]" value="sqlite-engine" <?=in_array('sqlite-engine',$form['products'],true)?'checked':''?>> SQLite-Engine</label><br><?php endif;?>
<?php if(isset($driverOptions['pgsql'])):?><label><input type="checkbox" name="products[]" value="pgsql-engine" <?=in_array('pgsql-engine',$form['products'],true)?'checked':''?>> PostgreSQL-Engine</label><br><?php endif;?>
<?php if(isset($driverOptions['oracle'])):?><label><input type="checkbox" name="products[]" value="oracle-engine" <?=in_array('oracle-engine',$form['products'],true)?'checked':''?>> Oracle-XE-Engine</label><br><?php endif;?>
<?php if(isset($driverOptions['mssql'])):?><label><input type="checkbox" name="products[]" value="mssql-engine" <?=in_array('mssql-engine',$form['products'],true)?'checked':''?>> Microsoft-SQL-Server-Engine</label><?php endif;?>
</section>

<section class="card"><h2>5. Umgebung & Abschluss</h2>
<div class="form-grid">
<label>Umgebung<select name="environment"><option value="production" <?=$form['environment']==='production'?'selected':''?>>production</option><option value="development" <?=$form['environment']==='development'?'selected':''?>>development</option></select></label>
<label>Zeitzone<input name="timezone" value="<?=e($form['timezone'])?>"></label>
</div>
<p>Der Install-Lock wird erst gesetzt, wenn das abschließende Health-Gate erfolgreich ist.</p>
<button class="button" <?= easyit_button_attributes('bestaetigen', 'install') ?> type="submit">Installation ausführen</button>
</section>
</form>
<script>
(function(){
 var admin=document.getElementById('admin_driver'), project=document.getElementById('project_driver');
 function isSql(v){return v==='mysql'||v==='pgsql'||v==='oracle'||v==='mssql';}
 function syncStores(){
  var av=admin?admin.value:'mysql', pv=project?project.value:'mysql';
  document.querySelectorAll('[data-admin-csv]').forEach(function(el){el.style.display=av==='csv'?'':'none';});
  document.querySelectorAll('[data-project-csv]').forEach(function(el){el.style.display=pv==='csv'?'':'none';});
  document.querySelectorAll('[data-admin-sqlite]').forEach(function(el){el.style.display=av==='sqlite'?'':'none';});
  document.querySelectorAll('[data-project-sqlite]').forEach(function(el){el.style.display=pv==='sqlite'?'':'none';});
  document.querySelectorAll('[data-admin-sql]').forEach(function(el){el.style.display=isSql(av)?'':'none';});
  document.querySelectorAll('[data-project-sql]').forEach(function(el){el.style.display=isSql(pv)?'':'none';});
  document.querySelectorAll('[data-admin-pgsql]').forEach(function(el){el.style.display=av==='pgsql'?'':'none';});
  document.querySelectorAll('[data-admin-oracle]').forEach(function(el){el.style.display=av==='oracle'?'':'none';});
  document.querySelectorAll('[data-admin-mssql]').forEach(function(el){el.style.display=av==='mssql'?'':'none';});
  document.querySelectorAll('[data-project-pgsql]').forEach(function(el){el.style.display=pv==='pgsql'?'':'none';});
  document.querySelectorAll('[data-project-oracle]').forEach(function(el){el.style.display=pv==='oracle'?'':'none';});
  document.querySelectorAll('[data-project-mssql]').forEach(function(el){el.style.display=pv==='mssql'?'':'none';});
  var ap=document.querySelector('input[name=admin_db_port]'), pp=document.querySelector('input[name=project_db_port]');
  if(ap){if(av==='pgsql')ap.value='5432';else if(av==='oracle')ap.value='1521';else if(av==='mssql')ap.value='1433';else if(av==='mysql')ap.value='3306';}
  if(pp){if(pv==='pgsql')pp.value='5432';else if(pv==='oracle')pp.value='1521';else if(pv==='mssql')pp.value='1433';else if(pv==='mysql')pp.value='3306';}
 }
 if(admin)admin.addEventListener('change',syncStores);if(project)project.addEventListener('change',syncStores);syncStores();
})();;
document.querySelectorAll('input[data-password-field]').forEach(function(input){
 var b=document.createElement('button'); b.type='button'; b.className='password-toggle';
 b.setAttribute('data-button','anzeigen'); b.setAttribute('data-button-context','password_show');
 input.insertAdjacentElement('afterend',b);
 b.addEventListener('click',function(){
  var show=input.type==='password'; input.type=show?'text':'password';
  b.setAttribute('data-button-context',show?'password_hide':'password_show');
  if(window.EasyITButtons && typeof window.EasyITButtons.decorate==='function') window.EasyITButtons.decorate(b);
 });
});
</script>
<style>.password-toggle{margin-left:.35rem;border:1px solid #ccd5e2;border-radius:.4rem;padding:.35rem .55rem;cursor:pointer}</style>
<?php endif;?>
<?php
$content=ob_get_clean();
render_page(['title'=>'Installer 2.0','active'=>'setup','content'=>$content,'help'=>[
'title'=>'Installer 2.0','location'=>'Setup → Installer',
'short'=>'Installiert easyIT Enterprise über einen konsolidierten Ablauf.',
'goal'=>'Einen reproduzierbaren und überprüften Ausgangszustand herstellen.',
'next'=>'Systemprüfung kontrollieren und anschließend Datenbank- und Adminkonto eingeben.',
'steps'=>['Systemprüfung prüfen.','Administrationsspeicher und Projektdatenspeicher unabhängig konfigurieren.','Ersten Superadministrator anlegen.','Produkte auswählen.','Health-Gate ausführen und Lock setzen.'],
'tips'=>['Das Superadmin-Kennwort ist nicht das Datenbankkennwort.','Ein bestehender Install-Lock verhindert versehentliche Neuinstallation.','Das Kennwort wird nur als Passwort-Hash gespeichert.']
]]);
