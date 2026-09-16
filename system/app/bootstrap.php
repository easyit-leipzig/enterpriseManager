<?php
declare(strict_types=1);

require_once __DIR__ . '/EnterpriseCsvPdo.php';
require_once __DIR__ . '/EnterpriseSqlitePdo.php';
require_once __DIR__ . '/EnterprisePgsqlPdo.php';
require_once __DIR__ . '/EnterpriseOraclePdo.php';
require_once __DIR__ . '/EnterpriseMssqlPdo.php';
require_once __DIR__ . '/project_store.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax','path'=>'/']);
    session_start();
}

function enterprise_env(string $path): array {
    $values=[];
    if (!is_file($path) || !is_readable($path)) return $values;
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line=trim($line);
        if ($line==='' || str_starts_with($line,'#') || !str_contains($line,'=')) continue;
        [$key,$value]=explode('=',$line,2);
        $values[trim($key)] = trim(trim($value), "\"'");
    }
    return $values;
}
function enterprise_pdo(): PDO {
    static $pdo=null;
    if ($pdo instanceof PDO) return $pdo;
    $env=enterprise_env(dirname(__DIR__,2).'/DataForm5-Core/.env');
    $driver=strtolower(trim((string)($env['ADMIN_DB_DRIVER'] ?? 'mysql')));
    if ($driver==='csv') {
        $database=trim((string)($env['ADMIN_DB_DATABASE'] ?? 'easyit_admin'));
        if ($database==='') throw new RuntimeException('Konfiguration ADMIN_DB_DATABASE fehlt. Bitte Installer abschließen.');
        $base=trim((string)($env['ADMIN_DB_CSV_BASE_PATH'] ?? ''));
        if ($base==='') $base=dirname(__DIR__,2).'/storage/admin-csv';
        elseif (!preg_match('~^(?:[A-Za-z]:[\\/]|/)~',$base)) $base=dirname(__DIR__,2).'/'.ltrim(str_replace('\\','/',$base),'/');
        $pdo=new EnterpriseCsvPdo($base,$database);
        return $pdo;
    }
    if ($driver==='sqlite') {
        $database=trim((string)($env['ADMIN_DB_DATABASE'] ?? 'easyit_admin'));
        if ($database==='' || preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/',$database)!==1) throw new RuntimeException('Konfiguration ADMIN_DB_DATABASE ist für SQLite ungültig. Bitte Installer abschließen.');
        $base=trim((string)($env['ADMIN_DB_SQLITE_BASE_PATH'] ?? 'storage/admin-sqlite'));
        if ($base==='') $base='storage/admin-sqlite';
        $base=str_replace('\\','/',$base);
        if (!preg_match('~^(?:[A-Za-z]:/|/)~',$base)) $base=dirname(__DIR__,2).'/'.ltrim($base,'/');
        $pdo=new EnterpriseSqlitePdo(rtrim($base,'/').'/'.$database.'.sqlite');
        return $pdo;
    }
    if ($driver==='mssql') {
        return new EnterpriseMssqlPdo((string)($env['ADMIN_DB_HOST']??'127.0.0.1'),(int)($env['ADMIN_DB_PORT']??1433),(string)($env['ADMIN_DB_DATABASE']??'easyit_admin'),(string)($env['ADMIN_DB_USERNAME']??''),(string)($env['ADMIN_DB_PASSWORD']??''),(string)($env['ADMIN_DB_ENCRYPT']??'1')!=='0',(string)($env['ADMIN_DB_TRUST_SERVER_CERTIFICATE']??'0')==='1');
    }
    if ($driver==='oracle') {
        foreach (['ADMIN_DB_HOST','ADMIN_DB_PORT','ADMIN_DB_USERNAME'] as $key) { if (empty($env[$key])) throw new RuntimeException("Konfiguration {$key} fehlt. Bitte Installer abschließen."); }
        $service=trim((string)($env['ADMIN_DB_ORACLE_SERVICE'] ?? 'XEPDB1')) ?: 'XEPDB1';
        $pdo=new EnterpriseOraclePdo((string)$env['ADMIN_DB_HOST'],(int)($env['ADMIN_DB_PORT']??1521),$service,(string)$env['ADMIN_DB_USERNAME'],(string)($env['ADMIN_DB_PASSWORD']??''));
        return $pdo;
    }
    if (in_array($driver,['pgsql','postgres','postgresql'],true)) {
        foreach (['ADMIN_DB_HOST','ADMIN_DB_PORT','ADMIN_DB_DATABASE','ADMIN_DB_USERNAME'] as $key) {
            if (empty($env[$key])) throw new RuntimeException("Konfiguration {$key} fehlt. Bitte Installer abschließen.");
        }
        $host=(string)$env['ADMIN_DB_HOST'];
        $port=(int)$env['ADMIN_DB_PORT'];
        $database=trim((string)$env['ADMIN_DB_DATABASE']);
        $username=(string)$env['ADMIN_DB_USERNAME'];
        $password=(string)($env['ADMIN_DB_PASSWORD']??'');
        $schema=trim((string)($env['ADMIN_DB_SCHEMA']??'public')) ?: 'public';
        try {
            $pdo=new EnterprisePgsqlPdo($host,$port,$database,$username,$password,$schema);
            return $pdo;
        } catch (PDOException $e) {
            // Eine fehlende Ziel-Datenbank ist bei PostgreSQL kein Grund für
            // einen ungefangenen Fatal Error. Über die Maintenance-Datenbank
            // lässt sich eindeutig unterscheiden, ob die Admin-Datenbank
            // tatsächlich fehlt oder die Verbindung aus anderem Grund scheitert.
            try {
                $maintenance=trim((string)($env['ADMIN_DB_MAINTENANCE_DATABASE']??'postgres')) ?: 'postgres';
                $server=new EnterprisePgsqlPdo($host,$port,$maintenance,$username,$password,'public');
                $st=$server->prepare('SELECT 1 FROM pg_database WHERE datname=?');
                $st->execute([$database]);
                if ($st->fetchColumn()===false) {
                    throw new RuntimeException(
                        'Die PostgreSQL-Administrationsdatenbank `'.$database.'` existiert nicht. Führen Sie das Setup bzw. den Datenbank-Assistenten aus, bevor Projekte verwaltet oder neu aufgebaut werden.',
                        0,
                        $e
                    );
                }
            } catch (RuntimeException $diagnostic) {
                throw $diagnostic;
            } catch (Throwable) {
                // Falls selbst die Maintenance-Verbindung nicht möglich ist,
                // bleibt die ursprüngliche PDO-Ursache maßgeblich.
            }
            throw new RuntimeException('PostgreSQL-Administrationsspeicher konnte nicht geöffnet werden: '.$e->getMessage(),0,$e);
        }
    }
    foreach (['ADMIN_DB_HOST','ADMIN_DB_PORT','ADMIN_DB_DATABASE','ADMIN_DB_USERNAME'] as $key) {
        if (empty($env[$key])) throw new RuntimeException("Konfiguration {$key} fehlt. Bitte Installer abschließen.");
    }
    $pdo=new PDO(
        'mysql:host='.$env['ADMIN_DB_HOST'].';port='.(int)$env['ADMIN_DB_PORT'].';dbname='.$env['ADMIN_DB_DATABASE'].';charset=utf8mb4',
        $env['ADMIN_DB_USERNAME'], $env['ADMIN_DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]
    );
    return $pdo;
}

function enterprise_upgrade(PDO $pdo): void {
    if ($pdo instanceof EnterpriseSqlitePdo) { enterprise_upgrade_sqlite($pdo); return; }
    $cols=$pdo->query("SHOW COLUMNS FROM projects LIKE 'product_type'")->fetch();
    if (!$cols) $pdo->exec("ALTER TABLE projects ADD product_type VARCHAR(60) NOT NULL DEFAULT 'dataform' AFTER slug");
    $cols=$pdo->query("SHOW COLUMNS FROM projects LIKE 'description'")->fetch();
    if (!$cols) $pdo->exec("ALTER TABLE projects ADD description TEXT NULL AFTER database_name");
    $pdo->exec("CREATE TABLE IF NOT EXISTS installed_products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_key VARCHAR(80) NOT NULL UNIQUE, label VARCHAR(120) NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'available', version VARCHAR(40) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS enterprise_module_settings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,module_name VARCHAR(190) NOT NULL,scope_type VARCHAR(30) NOT NULL DEFAULT 'enterprise',scope_id VARCHAR(190) NOT NULL DEFAULT 'global',config_key VARCHAR(190) NOT NULL,config_value_json LONGTEXT NULL,updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_module_setting(module_name,scope_type,scope_id,config_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS enterprise_module_secrets (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,module_name VARCHAR(190) NOT NULL,scope_type VARCHAR(30) NOT NULL DEFAULT 'enterprise',scope_id VARCHAR(190) NOT NULL DEFAULT 'global',config_key VARCHAR(190) NOT NULL,ciphertext LONGTEXT NOT NULL,nonce VARCHAR(255) NOT NULL,tag VARCHAR(255) NOT NULL,updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_module_secret(module_name,scope_type,scope_id,config_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS capabilities (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(190) NOT NULL UNIQUE,module_name VARCHAR(190) NULL,label VARCHAR(190) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,KEY idx_cap_module(module_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS role_capabilities (role_id BIGINT UNSIGNED NOT NULL,capability_id BIGINT UNSIGNED NOT NULL,PRIMARY KEY(role_id,capability_id),CONSTRAINT fk_rc_role FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE,CONSTRAINT fk_rc_cap FOREIGN KEY(capability_id) REFERENCES capabilities(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT IGNORE INTO roles(name,label) VALUES ('superadmin','Superadministrator')");
    if ($pdo instanceof EnterpriseOraclePdo) { $capSeed=$pdo->prepare('INSERT IGNORE INTO capabilities(name,module_name,label) VALUES (?,?,?)'); foreach ([['projects.view','core','Projekte anzeigen'],['projects.create','core','Projekte anlegen'],['projects.update','core','Projekte bearbeiten'],['projects.delete','core','Projekte löschen'],['projects.backup','core','Projekte sichern'],['projects.restore','core','Projekte wiederherstellen'],['modules.view','core','Module anzeigen'],['modules.manage','core','Module verwalten'],['modules.configure','core','Module konfigurieren'],['permissions.manage','core','Berechtigungen verwalten'],['background.view','core','Hintergrundprozesse anzeigen'],['background.manage','core','Hintergrundprozesse verwalten'],['monitoring.view','core','Monitoring anzeigen'],['monitoring.manage','core','Monitoring verwalten'],['cluster.view','core','Cluster anzeigen'],['cluster.manage','core','Cluster verwalten'],['storage.view','core','Storage anzeigen'],['storage.manage','core','Storage verwalten'],['replication.view','core','Replikation anzeigen'],['replication.manage','core','Replikation verwalten'],['cluster.security','core','Cluster-Sicherheit verwalten'],['developer.view','core','Developer Mode anzeigen']] as $capRow) $capSeed->execute($capRow); } else { $pdo->exec("INSERT IGNORE INTO capabilities(name,module_name,label) VALUES ('projects.view','core','Projekte anzeigen'),('projects.create','core','Projekte anlegen'),('projects.update','core','Projekte bearbeiten'),('projects.delete','core','Projekte löschen'),('projects.backup','core','Projekte sichern'),('projects.restore','core','Projekte wiederherstellen'),('modules.view','core','Module anzeigen'),('modules.manage','core','Module verwalten'),('modules.configure','core','Module konfigurieren'),('permissions.manage','core','Berechtigungen verwalten'),('background.view','core','Hintergrundprozesse anzeigen'),('background.manage','core','Hintergrundprozesse verwalten'),('monitoring.view','core','Monitoring anzeigen'),('monitoring.manage','core','Monitoring verwalten'),('cluster.view','core','Cluster anzeigen'),('cluster.manage','core','Cluster verwalten'),('storage.view','core','Storage anzeigen'),('storage.manage','core','Storage verwalten'),('replication.view','core','Replikation anzeigen'),('replication.manage','core','Replikation verwalten'),('cluster.security','core','Cluster-Sicherheit verwalten'),('developer.view','core','Developer Mode anzeigen')"); }
    if ($pdo instanceof EnterpriseOraclePdo) {
        foreach (['admin','superadmin'] as $roleName) {
            $roleSql="INSERT INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='".$roleName."' AND NOT EXISTS (SELECT 1 FROM role_capabilities rc WHERE rc.role_id=r.id AND rc.capability_id=c.id)";
            $pdo->exec($roleSql);
        }
    } else {
        $pdo->exec("INSERT IGNORE INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='admin'");
        $pdo->exec("INSERT IGNORE INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='superadmin'");
    }
    enterprise_ensure_superadmin_assignment($pdo);
    $pdo->exec("CREATE TABLE IF NOT EXISTS enterprise_module_migrations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, module_name VARCHAR(190) NOT NULL, migration_version VARCHAR(190) NOT NULL, checksum CHAR(64) NOT NULL, batch INT UNSIGNED NOT NULL DEFAULT 1, applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_module_migration(module_name,migration_version), KEY idx_module_migration_batch(module_name,batch)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS enterprise_licenses (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, license_key VARCHAR(190) NOT NULL UNIQUE, holder VARCHAR(190) NOT NULL, edition VARCHAR(80) NOT NULL DEFAULT 'community', capabilities_json LONGTEXT NULL, products_json LONGTEXT NULL, valid_from DATE NULL, expires_at DATE NULL, grace_days INT UNSIGNED NOT NULL DEFAULT 0, enabled TINYINT(1) NOT NULL DEFAULT 1, is_primary TINYINT(1) NOT NULL DEFAULT 0, metadata_json LONGTEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_license_enabled(enabled), INDEX idx_license_primary(is_primary), INDEX idx_license_expires(expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $stmt=$pdo->prepare("INSERT IGNORE INTO installed_products(product_key,label,status,version) VALUES (?,?,?,?)");
    foreach ([['dataform','DataForm','available','5-dev'],['dialog','Dialog','planned',null],['nachhilfe','Nachhilfe','planned',null],['csv-engine','CSV-Engine','available','phase1'],['sqlite-engine','SQLite','available','phase2'],['pgsql-engine','PostgreSQL','available','phase3'],['oracle-engine','Oracle XE','available','phase4'],['mssql-engine','Microsoft SQL Server','available','phase5']] as $row) $stmt->execute($row);
}
function enterprise_upgrade_sqlite(PDO $pdo): void {
    $ddl=[
        "CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL UNIQUE,label TEXT NOT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT,username TEXT NOT NULL UNIQUE,email TEXT NULL UNIQUE,password_hash TEXT NOT NULL,is_active INTEGER NOT NULL DEFAULT 1,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS user_roles (user_id INTEGER NOT NULL,role_id INTEGER NOT NULL,PRIMARY KEY(user_id,role_id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE)",
        "CREATE TABLE IF NOT EXISTS projects (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,slug TEXT NOT NULL UNIQUE,product_type TEXT NOT NULL DEFAULT 'dataform',database_driver TEXT NOT NULL DEFAULT 'mysql',database_name TEXT NOT NULL,description TEXT NULL,status TEXT NOT NULL DEFAULT 'active',created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS audit_log (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NULL,action TEXT NOT NULL,object_type TEXT NULL,object_id TEXT NULL,context_json TEXT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL)",
        "CREATE TABLE IF NOT EXISTS installed_products (id INTEGER PRIMARY KEY AUTOINCREMENT,product_key TEXT NOT NULL UNIQUE,label TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'available',version TEXT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS enterprise_module_settings (id INTEGER PRIMARY KEY AUTOINCREMENT,module_name TEXT NOT NULL,scope_type TEXT NOT NULL DEFAULT 'enterprise',scope_id TEXT NOT NULL DEFAULT 'global',config_key TEXT NOT NULL,config_value_json TEXT NULL,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE(module_name,scope_type,scope_id,config_key))",
        "CREATE TABLE IF NOT EXISTS enterprise_module_secrets (id INTEGER PRIMARY KEY AUTOINCREMENT,module_name TEXT NOT NULL,scope_type TEXT NOT NULL DEFAULT 'enterprise',scope_id TEXT NOT NULL DEFAULT 'global',config_key TEXT NOT NULL,ciphertext TEXT NOT NULL,nonce TEXT NOT NULL,tag TEXT NOT NULL,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE(module_name,scope_type,scope_id,config_key))",
        "CREATE TABLE IF NOT EXISTS capabilities (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL UNIQUE,module_name TEXT NULL,label TEXT NOT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS role_capabilities (role_id INTEGER NOT NULL,capability_id INTEGER NOT NULL,PRIMARY KEY(role_id,capability_id),FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE,FOREIGN KEY(capability_id) REFERENCES capabilities(id) ON DELETE CASCADE)",
        "CREATE TABLE IF NOT EXISTS enterprise_module_migrations (id INTEGER PRIMARY KEY AUTOINCREMENT,module_name TEXT NOT NULL,migration_version TEXT NOT NULL,checksum TEXT NOT NULL,batch INTEGER NOT NULL DEFAULT 1,applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE(module_name,migration_version))",
        "CREATE TABLE IF NOT EXISTS enterprise_licenses (id INTEGER PRIMARY KEY AUTOINCREMENT,license_key TEXT NOT NULL UNIQUE,holder TEXT NOT NULL,edition TEXT NOT NULL DEFAULT 'community',capabilities_json TEXT NULL,products_json TEXT NULL,valid_from TEXT NULL,expires_at TEXT NULL,grace_days INTEGER NOT NULL DEFAULT 0,enabled INTEGER NOT NULL DEFAULT 1,is_primary INTEGER NOT NULL DEFAULT 0,metadata_json TEXT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)"
    ];
    foreach($ddl as $sql)$pdo->exec($sql);
    foreach([
        "CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_cap_module ON capabilities(module_name)",
        "CREATE INDEX IF NOT EXISTS idx_module_migration_batch ON enterprise_module_migrations(module_name,batch)",
        "CREATE INDEX IF NOT EXISTS idx_license_enabled ON enterprise_licenses(enabled)",
        "CREATE INDEX IF NOT EXISTS idx_license_primary ON enterprise_licenses(is_primary)",
        "CREATE INDEX IF NOT EXISTS idx_license_expires ON enterprise_licenses(expires_at)"
    ] as $sql)$pdo->exec($sql);
    $pdo->exec("INSERT OR IGNORE INTO roles(name,label) VALUES ('admin','Administrator'),('editor','Bearbeiter'),('viewer','Leser'),('superadmin','Superadministrator')");
    $caps=[['projects.view','core','Projekte anzeigen'],['projects.create','core','Projekte anlegen'],['projects.update','core','Projekte bearbeiten'],['projects.delete','core','Projekte löschen'],['projects.backup','core','Projekte sichern'],['projects.restore','core','Projekte wiederherstellen'],['modules.view','core','Module anzeigen'],['modules.manage','core','Module verwalten'],['modules.configure','core','Module konfigurieren'],['permissions.manage','core','Berechtigungen verwalten'],['background.view','core','Hintergrundprozesse anzeigen'],['background.manage','core','Hintergrundprozesse verwalten'],['monitoring.view','core','Monitoring anzeigen'],['monitoring.manage','core','Monitoring verwalten'],['cluster.view','core','Cluster anzeigen'],['cluster.manage','core','Cluster verwalten'],['storage.view','core','Storage anzeigen'],['storage.manage','core','Storage verwalten'],['replication.view','core','Replikation anzeigen'],['replication.manage','core','Replikation verwalten'],['cluster.security','core','Cluster-Sicherheit verwalten'],['developer.view','core','Developer Mode anzeigen']];
    $st=$pdo->prepare("INSERT OR IGNORE INTO capabilities(name,module_name,label) VALUES (?,?,?)"); foreach($caps as $cap)$st->execute($cap);
    $pdo->exec("INSERT OR IGNORE INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name IN ('admin','superadmin')");
    enterprise_ensure_superadmin_assignment($pdo);
    $st=$pdo->prepare("INSERT OR IGNORE INTO installed_products(product_key,label,status,version) VALUES (?,?,?,?)");
    foreach ([['dataform','DataForm','available','5-dev'],['dialog','Dialog','planned',null],['nachhilfe','Nachhilfe','planned',null],['csv-engine','CSV-Engine','available','phase1'],['sqlite-engine','SQLite','available','phase2'],['pgsql-engine','PostgreSQL','available','phase3'],['oracle-engine','Oracle XE','available','phase4'],['mssql-engine','Microsoft SQL Server','available','phase5']] as $row) $st->execute($row);
}

function enterprise_ensure_superadmin_assignment(PDO $pdo): void {
    $superRole=$pdo->query("SELECT id FROM roles WHERE name='superadmin' LIMIT 1")->fetchColumn();
    if($superRole===false) return;
    $count=$pdo->prepare('SELECT COUNT(*) FROM user_roles WHERE role_id=?');
    $count->execute([(int)$superRole]);
    if((int)$count->fetchColumn()>0) return;
    $adminRole=$pdo->query("SELECT id FROM roles WHERE name='admin' LIMIT 1")->fetchColumn();
    if($adminRole===false) return;
    $first=$pdo->prepare('SELECT user_id FROM user_roles WHERE role_id=? ORDER BY user_id LIMIT 1');
    $first->execute([(int)$adminRole]);
    $userId=$first->fetchColumn();
    if($userId===false) return;
    $assign=$pdo->prepare('INSERT IGNORE INTO user_roles(user_id,role_id) VALUES (?,?)');
    $assign->execute([(int)$userId,(int)$superRole]);
}
function enterprise_user(): ?array { return $_SESSION['enterprise_user'] ?? null; }
function enterprise_require_auth(string $base='../'): array {
    $user=enterprise_user();
    if (!$user) { header('Location: '.$base.'login.php?next='.rawurlencode($_SERVER['REQUEST_URI'] ?? '')); exit; }
    return $user;
}
function enterprise_is_superadmin(array $user): bool { return in_array('superadmin',$user['roles'] ?? [],true); }
function enterprise_is_admin(array $user): bool { return enterprise_is_superadmin($user) || in_array('admin',$user['roles'] ?? [],true); }
function enterprise_landing_path(array $user): string { return enterprise_is_admin($user) ? 'app/projects/index.php' : 'app/dashboard.php'; }
function enterprise_sync_module_capabilities(PDO $pdo,string $modulesRoot): void { if(!is_dir($modulesRoot)) return; $stmt=$pdo->prepare('INSERT IGNORE INTO capabilities(name,module_name,label) VALUES (?,?,?)'); foreach(glob(rtrim($modulesRoot,'/\\').'/*/module.json')?:[] as $file){$data=json_decode((string)@file_get_contents($file),true);if(!is_array($data))continue;$module=(string)($data['name']??basename(dirname($file)));foreach((array)($data['capabilities']??[]) as $cap){if(is_string($cap)&&$cap!=='')$stmt->execute([$cap,$module,$cap]);}} if($pdo instanceof EnterpriseOraclePdo){foreach(['admin','superadmin'] as $roleName){$pdo->exec("INSERT INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='".$roleName."' AND NOT EXISTS (SELECT 1 FROM role_capabilities rc WHERE rc.role_id=r.id AND rc.capability_id=c.id)");}}else{$pdo->exec("INSERT IGNORE INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='admin'");$pdo->exec("INSERT IGNORE INTO role_capabilities(role_id,capability_id) SELECT r.id,c.id FROM roles r CROSS JOIN capabilities c WHERE r.name='superadmin'");} }
function enterprise_permissions(PDO $pdo,int $userId): array { $st=$pdo->prepare('SELECT DISTINCT c.name FROM capabilities c JOIN role_capabilities rc ON rc.capability_id=c.id JOIN user_roles ur ON ur.role_id=rc.role_id WHERE ur.user_id=?'); $st->execute([$userId]); return array_column($st->fetchAll(PDO::FETCH_ASSOC),'name'); }
function enterprise_can(array $user,string $capability): bool { return enterprise_is_superadmin($user) || enterprise_is_admin($user) || in_array($capability,$user['permissions']??[],true); }
function enterprise_require_capability(array $user,string $capability): void { if(!enterprise_can($user,$capability)){http_response_code(403);exit('Berechtigung fehlt: '.htmlspecialchars($capability,ENT_QUOTES,'UTF-8'));} }
function enterprise_csrf(): string {
    if (empty($_SESSION['enterprise_csrf'])) $_SESSION['enterprise_csrf']=bin2hex(random_bytes(32));
    return (string)$_SESSION['enterprise_csrf'];
}
function enterprise_check_csrf(string $token): void {
    if (!hash_equals(enterprise_csrf(),$token)) throw new RuntimeException('Sicherheitsprüfung fehlgeschlagen. Seite neu laden.');
}
function enterprise_audit(PDO $pdo, ?int $userId, string $action, ?string $type=null, ?string $id=null, array $context=[]): void {
    $stmt=$pdo->prepare('INSERT INTO audit_log(user_id,action,object_type,object_id,context_json) VALUES (?,?,?,?,?)');
    $stmt->execute([$userId,$action,$type,$id,$context?json_encode($context,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null]);
}


// RC1.7 Phase A – zentrale Enterprise Event Platform.
require_once dirname(__DIR__, 2) . '/DataForm5-Core/bootstrap/autoload.php';

function enterprise_event_config(): array {
    static $config = null;
    if (is_array($config)) return $config;
    $file = dirname(__DIR__, 2) . '/DataForm5-Core/config/events.php';
    $config = is_file($file) ? (require $file) : [];
    return is_array($config) ? $config : [];
}

function enterprise_events(): \DataForm5\Events\Core\EventDispatcher {
    static $dispatcher = null;
    if ($dispatcher instanceof \DataForm5\Events\Core\EventDispatcher) return $dispatcher;
    $dispatcher = new \DataForm5\Events\Core\EventDispatcher();
    return $dispatcher;
}

function enterprise_event_catalog(): \DataForm5\Events\Core\EventCatalog {
    static $catalog = null;
    if ($catalog instanceof \DataForm5\Events\Core\EventCatalog) return $catalog;
    $catalog = new \DataForm5\Events\Core\EventCatalog();
    foreach ((array)(enterprise_event_config()['catalog'] ?? []) as $name => $meta) {
        $meta = is_array($meta) ? $meta : [];
        $catalog->register((string)$name, (string)($meta['description'] ?? ''), (string)($meta['producer'] ?? 'enterprise'));
    }
    return $catalog;
}

function enterprise_event_listen(string $eventName, callable $listener, int $priority = 0): void {
    enterprise_events()->listen($eventName, $listener, $priority);
}

function enterprise_event_dispatch(string $eventName, array $payload = [], array $context = []): \DataForm5\Events\Core\NamedEvent {
    $event = new \DataForm5\Events\Core\NamedEvent($eventName, $payload, $context);
    $config = enterprise_event_config();
    if (($config['enabled'] ?? true) === false) return $event;
    enterprise_events()->dispatch($event, $eventName);
    if(function_exists('enterprise_developer_enabled') && enterprise_developer_enabled()){
        try{enterprise_developer_trace()->event($eventName,['payload_keys'=>array_keys($payload)]);}catch(\Throwable){}
    }
    enterprise_event_diagnostic($event);
    return $event;
}

function enterprise_event_diagnostic(\DataForm5\Events\Core\NamedEvent $event): void {
    $diag = (array)(enterprise_event_config()['diagnostics'] ?? []);
    if (($diag['enabled'] ?? false) !== true) return;
    $root = dirname(__DIR__, 2);
    $relative = (string)($diag['file'] ?? 'storage/logs/events.log');
    $file = $root . '/' . ltrim(str_replace('\\', '/', $relative), '/');
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (!is_dir($dir) || !is_writable($dir)) return;
    $max = max(65536, (int)($diag['max_bytes'] ?? 1048576));
    if (is_file($file) && filesize($file) > $max) @rename($file, $file . '.1');
    $redact = static function(array $data): array {
        $result=[];
        foreach ($data as $key=>$value) {
            $k=strtolower((string)$key);
            if (preg_match('/password|secret|token|api[_-]?key|session/', $k)) { $result[$key]='[REDACTED]'; continue; }
            $result[$key]=is_array($value) ? '[array]' : (is_scalar($value) || $value===null ? $value : '['.get_debug_type($value).']');
        }
        return $result;
    };
    $line = json_encode([
        'time'=>$event->occurredAt(),
        'event'=>$event->name(),
        'payload'=>$redact($event->payload()),
        'context'=>$redact($event->context()),
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if (is_string($line)) @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// RC1.7 Phase B – gemeinsamer Enterprise-Service-Container.
function enterprise_kernel(): \DataForm5\Core\Kernel {
    static $kernel = null;
    if ($kernel instanceof \DataForm5\Core\Kernel) return $kernel;
    $kernel = require dirname(__DIR__, 2) . '/DataForm5-Core/bootstrap/app.php';
    try {
        $pdo = enterprise_pdo();
        enterprise_upgrade($pdo);
        $kernel->container()->instance(\DataForm5\Licensing\Contracts\LicenseProviderInterface::class, new \DataForm5\Licensing\Core\PdoLicenseProvider($pdo));
        $kernel->container()->instance(\DataForm5\Licensing\Core\LicenseRegistry::class, new \DataForm5\Licensing\Core\LicenseRegistry($pdo));
    } catch (\Throwable) {
        // Vor Abschluss des Installers bleibt der konfigurierte Community-Provider aktiv.
    }
    return $kernel;
}
function enterprise_container(): \DataForm5\Core\Contracts\ContainerInterface {
    return enterprise_kernel()->container();
}


function enterprise_module_ui(): \DataForm5\Modules\UI\ModuleUiRegistry {
    static $ui=null;
    if($ui instanceof \DataForm5\Modules\UI\ModuleUiRegistry) return $ui;
    $kernel=enterprise_kernel();
    $manager=$kernel->container()->get(\DataForm5\Modules\Core\ModuleManager::class);
    $ui=new \DataForm5\Modules\UI\ModuleUiRegistry($manager);
    return $ui;
}


function enterprise_module_routes(): \DataForm5\Modules\Routing\ModuleRouteRegistry {
    return enterprise_container()->get(\DataForm5\Modules\Routing\ModuleRouteRegistry::class);
}
function enterprise_module_dispatcher(): \DataForm5\Modules\Routing\ModuleRouteDispatcher {
    return enterprise_container()->get(\DataForm5\Modules\Routing\ModuleRouteDispatcher::class);
}

function enterprise_module_api_routes(): \DataForm5\Modules\Routing\ModuleApiRegistry { return enterprise_container()->get(\DataForm5\Modules\Routing\ModuleApiRegistry::class); }
function enterprise_module_api_dispatcher(): \DataForm5\Modules\Routing\ModuleApiDispatcher { return enterprise_container()->get(\DataForm5\Modules\Routing\ModuleApiDispatcher::class); }


function enterprise_module_background(): \DataForm5\Modules\Background\ModuleBackgroundRegistry {
    return enterprise_container()->get(\DataForm5\Modules\Background\ModuleBackgroundRegistry::class);
}

function enterprise_module_background_admin(): \DataForm5\Modules\Background\ModuleBackgroundAdmin {
    return enterprise_container()->get(\DataForm5\Modules\Background\ModuleBackgroundAdmin::class);
}

function enterprise_monitoring(): \DataForm5\Monitoring\Core\HealthManager { return enterprise_container()->get(\DataForm5\Monitoring\Core\HealthManager::class); }
function enterprise_monitoring_heartbeat(): \DataForm5\Monitoring\Core\WorkerHeartbeat { return enterprise_container()->get(\DataForm5\Monitoring\Core\WorkerHeartbeat::class); }

function enterprise_cluster(): \DataForm5\Cluster\Core\ClusterManager {
    return enterprise_container()->get(\DataForm5\Cluster\Core\ClusterManager::class);
}

function enterprise_cluster_lock(): \DataForm5\Cluster\Core\ClusterLock {
    return enterprise_container()->get(\DataForm5\Cluster\Core\ClusterLock::class);
}

function enterprise_storage(): \DataForm5\Core\Filesystem\StorageManager {
    return enterprise_container()->get(\DataForm5\Core\Filesystem\StorageManager::class);
}

function enterprise_replication(): \DataForm5\Replication\Core\ClusterSynchronizer {
    return enterprise_container()->get(\DataForm5\Replication\Core\ClusterSynchronizer::class);
}
function enterprise_replication_snapshots(): \DataForm5\Replication\Core\ReplicationSnapshotService {
    return enterprise_container()->get(\DataForm5\Replication\Core\ReplicationSnapshotService::class);
}

function enterprise_cluster_trust_store(): \DataForm5\Cluster\Security\NodeTrustStore {
    return enterprise_container()->get(\DataForm5\Cluster\Security\NodeTrustStore::class);
}


function enterprise_dashboard_snapshot(): array {
    static $snapshot=null;
    if(is_array($snapshot)) return $snapshot;
    try {
        $dashboard=enterprise_container()->get(\DataForm5\Core\Dashboard\EnterpriseDashboard::class);
        return $snapshot=$dashboard->snapshot();
    } catch (\Throwable $e) {
        return $snapshot=[
            'overall'=>'failed',
            'generated_at'=>date(DATE_ATOM),
            'error'=>$e->getMessage(),
            'modules'=>['status'=>'unknown','active'=>0,'total'=>0,'issues'=>0],
            'jobs'=>['status'=>'unknown','pending'=>0,'failed'=>0],
            'monitoring'=>['status'=>'unknown','alerts'=>0],
            'cluster'=>['status'=>'unknown','online'=>0,'nodes'=>0,'leader'=>null],
            'storage'=>['status'=>'unknown','healthy'=>0,'total'=>0],
            'replication'=>['status'=>'unknown','channel'=>'','security'=>false],
        ];
    }
}


function enterprise_developer_config(): array {
    static $config=null;
    if(is_array($config)) return $config;
    $file=dirname(__DIR__,2).'/DataForm5-Core/config/developer.php';
    $config=is_file($file)?require $file:[];
    return is_array($config)?$config:[];
}
function enterprise_developer_enabled(?array $user=null): bool {
    $config=enterprise_developer_config();
    if(($config['enabled']??false)!==true) return false;
    if($user===null) return true;
    $roles=(array)($user['roles']??[]);
    foreach((array)($config['allowed_roles']??['admin']) as $role)
        if(in_array($role,$roles,true)) return true;
    return false;
}
function enterprise_developer_trace(): \DataForm5\Core\Developer\DeveloperTrace {
    return enterprise_container()->get(\DataForm5\Core\Developer\DeveloperTrace::class);
}
function enterprise_developer_inspector(): \DataForm5\Core\Developer\DeveloperInspector {
    return enterprise_container()->get(\DataForm5\Core\Developer\DeveloperInspector::class);
}

function enterprise_container_inspector(): \DataForm5\Core\Developer\ContainerInspector {
    return enterprise_container()->get(\DataForm5\Core\Developer\ContainerInspector::class);
}

function enterprise_event_inspector(): \DataForm5\Core\Developer\EventInspector {
    return enterprise_container()->get(\DataForm5\Core\Developer\EventInspector::class);
}

function enterprise_hook_inspector(): \DataForm5\Core\Developer\HookInspector {
    return enterprise_container()->get(\DataForm5\Core\Developer\HookInspector::class);
}

function enterprise_profiler(): \DataForm5\Core\Developer\RequestProfiler {
    return enterprise_container()->get(\DataForm5\Core\Developer\RequestProfiler::class);
}

function enterprise_quality_center(): \DataForm5\Testing\Core\DeveloperQualityCenter {
    return enterprise_container()->get(\DataForm5\Testing\Core\DeveloperQualityCenter::class);
}
