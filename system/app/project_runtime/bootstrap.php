<?php
declare(strict_types=1);

const DF_APP_ROOT = __DIR__ . '/..';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

foreach ([__DIR__.'/EnterprisePgsqlPdo.php', dirname(__DIR__).'/EnterprisePgsqlPdo.php'] as $compat) { if (is_file($compat)) { require_once $compat; break; } }
foreach ([__DIR__.'/EnterpriseOraclePdo.php', dirname(__DIR__).'/EnterpriseOraclePdo.php'] as $compat) { if (is_file($compat)) { require_once $compat; break; } }
foreach ([__DIR__.'/EnterpriseMssqlAdapter.php', dirname(__DIR__).'/EnterpriseMssqlAdapter.php'] as $compat) { if (is_file($compat)) { require_once $compat; break; } }
foreach ([__DIR__.'/EnterpriseMssqlPdo.php', dirname(__DIR__).'/EnterpriseMssqlPdo.php'] as $compat) { if (is_file($compat)) { require_once $compat; break; } }

function df_e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function df_config(): array {
    static $cfg=null;
    if ($cfg!==null) return $cfg;
    $file=DF_APP_ROOT.'/config.php';
    if (!is_file($file)) {
        $script=str_replace('\\','/', (string)($_SERVER['SCRIPT_NAME']??''));
        $depth=str_contains($script,'/dataforms/')?'../':'';
        header('Location: '.$depth.'setup.php'); exit;
    }
    $loaded=require $file;
    if (!is_array($loaded)) throw new RuntimeException('config.php ist ungültig.');
    return $cfg=$loaded;
}
function df_db_driver(): string {
    $driver=strtolower(trim((string)(df_config()['db_driver']??'mysql')));
    if(in_array($driver,['postgres','postgresql'],true))$driver='pgsql';
    return $driver;
}
function df_pdo(): PDO {
    static $pdo=null;
    if ($pdo instanceof PDO) return $pdo;
    $c=df_config();$driver=df_db_driver();
    if($driver==='mssql'){if(!class_exists('EnterpriseMssqlPdo'))throw new RuntimeException('MSSQL-Kompatibilitätsklasse fehlt im Anwenderpaket.');return $pdo=new EnterpriseMssqlPdo((string)($c['db_host']??'127.0.0.1'),(int)($c['db_port']??1433),(string)($c['db_name']??''),(string)($c['db_user']??''),(string)($c['db_password']??''),(bool)($c['db_encrypt']??true),(bool)($c['db_trust_server_certificate']??false));}
    if($driver==='oracle'){
        if(!class_exists('EnterpriseOraclePdo'))throw new RuntimeException('Oracle-XE-Kompatibilitätsklasse fehlt im Anwenderpaket.');
        return $pdo=new EnterpriseOraclePdo((string)$c['db_host'],(int)$c['db_port'],(string)($c['db_service']??'XEPDB1'),(string)$c['db_user'],(string)$c['db_password']);
    }
    if($driver==='pgsql'){
        if(!class_exists('EnterprisePgsqlPdo'))throw new RuntimeException('PostgreSQL-Kompatibilitätsklasse fehlt im Anwenderpaket.');
        return $pdo=new EnterprisePgsqlPdo((string)$c['db_host'],(int)$c['db_port'],(string)$c['db_name'],(string)$c['db_user'],(string)$c['db_password']);
    }
    if($driver!=='mysql')throw new RuntimeException('Nicht unterstützter Projektdatenbanktreiber im Anwenderpaket: '.$driver);
    $dsn='mysql:host='.(string)$c['db_host'].';port='.(int)$c['db_port'].';dbname='.(string)$c['db_name'].';charset=utf8mb4';
    return $pdo=new PDO($dsn,(string)$c['db_user'],(string)$c['db_password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
}
function df_csrf(): string {
    if (empty($_SESSION['df_csrf'])) $_SESSION['df_csrf']=bin2hex(random_bytes(32));
    return (string)$_SESSION['df_csrf'];
}
function df_csrf_check(): void {
    if (!hash_equals(df_csrf(),(string)($_POST['csrf']??''))) throw new RuntimeException('Sicherheitsprüfung fehlgeschlagen.');
}
function df_project_id(): int { return max(1,(int)(df_config()['project_id']??1)); }
function df_ident(string $name): string {
    if (preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/',$name)!==1) throw new RuntimeException('Ungültiger SQL-Bezeichner: '.$name);
    return '`'.$name.'`'; // PostgreSQL-/Oracle-Kompatibilitätsschichten normalisieren Backticks.
}
