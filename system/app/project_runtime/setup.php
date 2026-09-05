<?php
declare(strict_types=1);
session_start();
$root=__DIR__;

function importSqlScript(PDO $pdo,string $sql): void {
    $delimiter=';'; $buffer='';
    foreach(preg_split('/\R/',$sql)?:[] as $line){
        $trim=trim($line);
        if($trim==='' || str_starts_with($trim,'-- ')) continue;
        if(preg_match('/^DELIMITER\s+(.+)$/i',$trim,$m)){ if(trim($buffer)!==''){$pdo->exec($buffer);$buffer='';} $delimiter=trim($m[1]); continue; }
        $buffer.=$line."\n";
        $rtrim=rtrim($buffer);
        if($delimiter!=='' && str_ends_with($rtrim,$delimiter)){
            $statement=substr($rtrim,0,-strlen($delimiter));
            if(trim($statement)!=='')$pdo->exec($statement);
            $buffer='';
        }
    }
    if(trim($buffer)!=='')$pdo->exec($buffer);
}

$manifest=json_decode((string)@file_get_contents($root.'/install/manifest.json'),true)?:[];$project=$manifest['project']??[];$msg='';$err='';
if(is_file($root.'/config.php')){header('Location: index.php');exit;}
if(empty($_SESSION['setup_csrf']))$_SESSION['setup_csrf']=bin2hex(random_bytes(32));
if($_SERVER['REQUEST_METHOD']==='POST')try{
 if(!hash_equals((string)$_SESSION['setup_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Sicherheitsprüfung fehlgeschlagen.');
 $host=trim((string)$_POST['host']);$port=max(1,(int)$_POST['port']);$db=trim((string)$_POST['database']);$user=trim((string)$_POST['user']);$pass=(string)$_POST['password'];if(preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/',$db)!==1)throw new RuntimeException('Ungültiger Datenbankname.');
 $server=new PDO('mysql:host='.$host.';port='.$port.';charset=utf8mb4',$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
 if(!empty($_POST['create_db']))$server->exec('CREATE DATABASE IF NOT EXISTS `'.$db.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
 $opts=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]; if(defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS'))$opts[PDO::MYSQL_ATTR_MULTI_STATEMENTS]=true; $pdo=new PDO('mysql:host='.$host.';port='.$port.';dbname='.$db.';charset=utf8mb4',$user,$pass,$opts);
 $sql=(string)file_get_contents($root.'/install/database.sql');importSqlScript($pdo,$sql);
 $cfg=['db_host'=>$host,'db_port'=>$port,'db_name'=>$db,'db_user'=>$user,'db_password'=>$pass,'project_id'=>(int)($project['source_project_id']??$project['id']??1),'project_name'=>(string)($project['name']??'Projekt')];
 $content="<?php\ndeclare(strict_types=1);\nreturn ".var_export($cfg,true).";\n";if(file_put_contents($root.'/config.php',$content,LOCK_EX)===false)throw new RuntimeException('config.php konnte nicht geschrieben werden.');@chmod($root.'/config.php',0600);header('Location: index.php');exit;
}catch(Throwable $e){$err=$e->getMessage();}
?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Projekt einrichten</title><link rel="stylesheet" href="assets/app.css"></head><body><main class="setup"><img class="setup-logo" src="assets/img/easyit-epManager-logo.png" alt="easyIT"><h1><?=htmlspecialchars((string)($project['name']??'Projekt'))?> einrichten</h1><?php if($err):?><div class="notice error"><?=htmlspecialchars($err)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars((string)$_SESSION['setup_csrf'])?>"><label>DB-Host<input name="host" value="localhost" required></label><label>Port<input name="port" type="number" value="3306" required></label><label>Datenbank<input name="database" value="<?=htmlspecialchars((string)($project['database_name']??'projekt'))?>" required></label><label>DB-Benutzer<input name="user" required></label><label>DB-Kennwort<input name="password" type="password"></label><label class="check"><input type="checkbox" name="create_db" value="1" checked> Datenbank anlegen, falls sie noch nicht existiert</label><button>Installation starten</button></form><p>Das Paket installiert ausschließlich dieses Projekt und seine DataForms.</p></main></body></html>
