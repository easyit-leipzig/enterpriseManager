<?php
declare(strict_types=1);
$config=require dirname(__DIR__).'/bootstrap.php';$pdo=\EasyIT\LicenseServer\Database\Connection::create($config['database']);(new \EasyIT\LicenseServer\Database\MigrationRunner($pdo))->migrate();echo "LICENSE_SERVER_MIGRATIONS_OK\n";
