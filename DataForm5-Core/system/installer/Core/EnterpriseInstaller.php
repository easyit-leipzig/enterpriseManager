<?php
declare(strict_types=1);

namespace DataForm5\Installer\Core;

use DataForm5\Installer\Exceptions\InstallerException;
use PDO;

final class EnterpriseInstaller
{
    public function __construct(
        private readonly string $enterprisePath,
        private readonly string $corePath,
        private readonly SystemInspector $inspector,
        private readonly InstallationLock $lock,
        private readonly EnvironmentWriter $envWriter,
        private readonly AdminDatabaseInstaller $database,
        private readonly InstallationHealthGate $health
    ) {}

    public function inspect(): array
    {
        $base=$this->inspector->inspect();
        $required=['pdo_mysql','json','openssl'];
        $extensions=[];
        foreach($required as $ext)$extensions[$ext]=extension_loaded($ext);
        return $base+['required_extensions'=>$extensions,'enterprise_ready'=>$base['ready']&&!in_array(false,$extensions,true)];
    }

    public function install(array $input): array
    {
        if($this->lock->exists()) throw new InstallerException('Installation ist bereits abgeschlossen. Entfernen Sie den Lock nicht manuell.');
        $inspection=$this->inspect();
        if(!($inspection['enterprise_ready']??false)) throw new InstallerException('Systemanforderungen sind nicht erfüllt.');

        $db=(array)($input['database']??[]);
        $admin=(array)($input['admin']??[]);
        $products=array_values(array_filter((array)($input['products']??['dataform']),'is_string'));

        $this->database->createDatabase($db);
        $pdo=$this->database->connect($db);
        $schema=$this->database->installSchema($pdo,$this->enterprisePath.'/installer/schema/admin');

        $env=[
            'APP_ENV'=>(string)($input['environment']??'production'),
            'APP_DEBUG'=>'false',
            'APP_TIMEZONE'=>(string)($input['timezone']??'Europe/Stockholm'),
            'DATAFORM_APP_KEY'=>'dfk1_'.rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'='),
            'ADMIN_DB_HOST'=>(string)$db['host'],
            'ADMIN_DB_PORT'=>(string)$db['port'],
            'ADMIN_DB_DATABASE'=>(string)$db['database'],
            'ADMIN_DB_USERNAME'=>(string)$db['username'],
            'ADMIN_DB_PASSWORD'=>(string)($db['password']??''),
            'PROJECT_DB_HOST'=>(string)$db['host'],
            'PROJECT_DB_PORT'=>(string)$db['port'],
            'PROJECT_DB_USERNAME'=>(string)$db['username'],
            'PROJECT_DB_PASSWORD'=>(string)($db['password']??''),
            'PROJECT_DB_CHARSET'=>'utf8mb4',
            'CACHE_STORE'=>'file',
            'QUEUE_CONNECTION'=>'file',
            'CLUSTER_ENABLED'=>'false',
            'CLUSTER_SECURITY_ENABLED'=>'false',
            'STORAGE_DISK'=>'local',
            'REPLICATION_ENABLED'=>'false',
        ];
        $this->envWriter->write($env);

        $userId=$this->database->createAdmin(
            $pdo,(string)($admin['username']??''),(string)($admin['email']??''),(string)($admin['password']??'')
        );
        $this->database->registerProducts($pdo,$products);
        $bundledProject=$this->installBundledProject($pdo,$db);

        $health=$this->health->check($this->corePath,$pdo);
        if(!$health['ready']) throw new InstallerException('Health-Gate fehlgeschlagen; Install-Lock wurde nicht gesetzt.');

        $metadata=[
            'enterprise_version'=>trim((string)@file_get_contents($this->enterprisePath.'/VERSION')),
            'installed_at'=>date(DATE_ATOM),
            'admin_user_id'=>$userId,
            'database'=>(string)$db['database'],
            'products'=>$products,
            'schema_files'=>$schema,
            'bundled_project'=>$bundledProject,
            'health'=>$health,
        ];
        $this->lock->create($metadata);

        $report=$this->enterprisePath.'/storage/logs/install-report.json';
        $dir=dirname($report);if(!is_dir($dir))@mkdir($dir,0775,true);
        @file_put_contents($report,json_encode($metadata,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

        return $metadata;
    }

    public function status(): array
    {
        return ['installed'=>$this->lock->exists(),'lock'=>$this->lock->read(),'inspection'=>$this->inspect()];
    }
    private function installBundledProject(PDO $adminPdo,array $db): ?array
    {
        $bundleDir=$this->enterprisePath.'/distribution/project';
        $manifestFile=$bundleDir.'/manifest.json';$projectFile=$bundleDir.'/project.json';$sqlFile=$bundleDir.'/database.sql';
        if(!is_file($manifestFile)||!is_file($projectFile)||!is_file($sqlFile)) return null;
        $manifest=json_decode((string)file_get_contents($manifestFile),true);
        $project=json_decode((string)file_get_contents($projectFile),true);
        $sql=(string)file_get_contents($sqlFile);
        if(!is_array($manifest)||($manifest['format']??'')!=='easyit-project-application-bundle'||!is_array($project)||$sql==='')
            throw new InstallerException('Das enthaltene Projekt-Anwenderbundle ist ungültig.');
        $database=trim((string)($project['database_name']??''));
        if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/',$database)) throw new InstallerException('Ungültige Projektdatenbank im Anwenderbundle.');
        require_once $this->enterprisePath.'/system/app/project_restore.php';
        $env=[
            'ADMIN_DB_DATABASE'=>(string)$db['database'],
            'PROJECT_DB_HOST'=>(string)$db['host'],'PROJECT_DB_PORT'=>(string)$db['port'],
            'PROJECT_DB_USERNAME'=>(string)$db['username'],'PROJECT_DB_PASSWORD'=>(string)($db['password']??''),
        ];
        try{
            $restore=\enterprise_project_restore_database($env,$database,$database,$sql,'restore');
        }catch(\Throwable $e){ throw new InstallerException('Enthaltenes Projekt konnte nicht installiert werden: '.$e->getMessage(),0,$e); }
        $columns=array_column($adminPdo->query('SHOW COLUMNS FROM projects')->fetchAll(PDO::FETCH_ASSOC),'Field');
        if(!in_array('product_type',$columns,true))$adminPdo->exec("ALTER TABLE projects ADD product_type VARCHAR(40) NOT NULL DEFAULT 'dataform' AFTER slug");
        if(!in_array('description',$columns,true))$adminPdo->exec("ALTER TABLE projects ADD description TEXT NULL AFTER database_name");
        $id=max(0,(int)($project['id']??0));
        $args=[(string)($project['name']??'Projekt'),(string)($project['slug']??'projekt'),(string)($project['product_type']??'dataform'),(string)($project['database_driver']??'mysql'),$database,(string)($project['description']??''),(string)($project['status']??'active')];
        if($id>0){
            $st=$adminPdo->prepare('INSERT INTO projects(id,name,slug,product_type,database_driver,database_name,description,status) VALUES (?,?,?,?,?,?,?,?)');
            $st->execute(array_merge([$id],$args));$projectId=$id;
        }else{
            $st=$adminPdo->prepare('INSERT INTO projects(name,slug,product_type,database_driver,database_name,description,status) VALUES (?,?,?,?,?,?,?)');
            $st->execute($args);$projectId=(int)$adminPdo->lastInsertId();
        }
        return ['id'=>$projectId,'name'=>(string)($project['name']??''),'slug'=>(string)($project['slug']??''),'database'=>$database,'restore'=>$restore];
    }

}
