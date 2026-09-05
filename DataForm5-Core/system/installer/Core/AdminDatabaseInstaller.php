<?php
declare(strict_types=1);

namespace DataForm5\Installer\Core;

use DataForm5\Installer\Exceptions\InstallerException;
use PDO;

final class AdminDatabaseInstaller
{
    public function connect(array $db,bool $withoutDatabase=false): PDO
    {
        foreach(['host','port','database','username'] as $key){
            if(!isset($db[$key])||$db[$key]==='') throw new InstallerException("DB-Konfiguration '{$key}' fehlt.");
        }
        $dsn='mysql:host='.(string)$db['host'].';port='.(int)$db['port'].';charset=utf8mb4';
        if(!$withoutDatabase) $dsn.=';dbname='.(string)$db['database'];
        return new PDO($dsn,(string)$db['username'],(string)($db['password']??''),[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
    }

    public function createDatabase(array $db): void
    {
        $name=(string)$db['database'];
        if(!preg_match('/^[A-Za-z0-9_]+$/',$name)) throw new InstallerException('Ungültiger Datenbankname.');
        $pdo=$this->connect($db,true);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public function installSchema(PDO $pdo,string $schemaDir): array
    {
        $files=glob(rtrim($schemaDir,'/\\').'/*.php')?:[];
        sort($files,SORT_STRING);
        $applied=[];
        foreach($files as $file){
            $migration=require $file;
            if(!is_callable($migration)) throw new InstallerException('Ungültige Schema-Datei: '.basename($file));
            $migration($pdo);
            $applied[]=basename($file);
        }
        return $applied;
    }

    public function createAdmin(PDO $pdo,string $username,string $email,string $password): int
    {
        if(!preg_match('/^[A-Za-z0-9._-]{3,120}$/',$username)) throw new InstallerException('Ungültiger Benutzername.');
        if($email!==''&&filter_var($email,FILTER_VALIDATE_EMAIL)===false) throw new InstallerException('Ungültige E-Mail-Adresse.');
        if(strlen($password)<12||!preg_match('/[A-Z]/',$password)||!preg_match('/[a-z]/',$password)||!preg_match('/\d/',$password)){
            throw new InstallerException('Admin-Kennwort muss mindestens 12 Zeichen, Groß-/Kleinbuchstaben und eine Ziffer enthalten.');
        }
        $hash=password_hash($password,PASSWORD_DEFAULT);
        if(!is_string($hash)) throw new InstallerException('Passwort-Hash konnte nicht erzeugt werden.');
        $pdo->beginTransaction();
        try{
            $stmt=$pdo->prepare("INSERT INTO users(username,email,password_hash,is_active) VALUES(?,?,?,1)
                ON DUPLICATE KEY UPDATE email=VALUES(email),password_hash=VALUES(password_hash),is_active=1");
            $stmt->execute([$username,$email!==''?$email:null,$hash]);
            $q=$pdo->prepare('SELECT id FROM users WHERE username=? LIMIT 1');$q->execute([$username]);$uid=(int)$q->fetchColumn();
            $rid=(int)$pdo->query("SELECT id FROM roles WHERE name='admin' LIMIT 1")->fetchColumn();
            if($uid<1||$rid<1) throw new InstallerException('Admin-Rolle oder Benutzer konnte nicht ermittelt werden.');
            $pdo->prepare('INSERT IGNORE INTO user_roles(user_id,role_id) VALUES(?,?)')->execute([$uid,$rid]);
            $pdo->commit();
            return $uid;
        }catch(\Throwable $e){
            if($pdo->inTransaction())$pdo->rollBack();
            throw $e;
        }
    }

    public function registerProducts(PDO $pdo,array $products): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS installed_products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_key VARCHAR(80) NOT NULL UNIQUE,label VARCHAR(120) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'available',version VARCHAR(40) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $known=[
            'dataform'=>['DataForm','5-dev'],
            'dialog'=>['Dialog',null],
            'nachhilfe'=>['Nachhilfe',null],
            'csv-engine'=>['CSV-Engine',null],
        ];
        $stmt=$pdo->prepare('INSERT INTO installed_products(product_key,label,status,version) VALUES(?,?,?,?)
            ON DUPLICATE KEY UPDATE label=VALUES(label),status=VALUES(status),version=VALUES(version)');
        foreach($known as $key=>[$label,$version]){
            $status=in_array($key,$products,true)?'available':'disabled';
            $stmt->execute([$key,$label,$status,$version]);
        }
    }
}
