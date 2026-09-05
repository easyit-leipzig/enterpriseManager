<?php
declare(strict_types=1);

namespace DataForm\Database;

use DataForm\Database\Contracts\DatabaseInterface;
use DataForm\Database\Csv\CsvAdapter;
use DataForm\Database\MySql\MySqlAdapter;
use DataForm\Database\Oracle\OracleAdapter;
use DataForm\Database\Sqlite\SqliteAdapter;

final class DatabaseFactory
{
    public static function create(array $config): DatabaseInterface
    {
        $driver = strtolower(trim((string)($config['driver'] ?? '')));

        $database = match ($driver) {
            'csv' => new CsvAdapter($config),
            'mysql', 'mariadb' => new MySqlAdapter($config),
            'sqlite', 'sqlite3' => new SqliteAdapter($config),
            'oracle', 'oci', 'oci8' => new OracleAdapter($config),
            default => throw new DatabaseException("Nicht unterstützter Datenbanktreiber: {$driver}"),
        };

        if (($config['auto_connect'] ?? true) === true) {
            $database->connect();
        }

        return $database;
    }
}
