<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Database;

use PDO;

final class Connection
{
    public static function create(array $cfg): PDO
    {
        $pdo = new PDO((string)$cfg['dsn'], (string)($cfg['user']??''), (string)($cfg['password']??''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $driver=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if($driver==='mysql') {
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("SET collation_connection='utf8mb4_unicode_ci'");
        }
        return $pdo;
    }
}
