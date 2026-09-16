<?php
declare(strict_types=1);

return [
    'app' => [
        'environment' => getenv('EASYIT_ENV') ?: 'production',
        'base_path' => dirname(__DIR__),
        'api_version' => '1.0',
        'clock_skew_seconds' => 300,
        'maintenance_mode' => getenv('EASYIT_MAINTENANCE') ?: 'off', // off|read_only|full
    ],
    'database' => [
        // mysql:host=127.0.0.1;port=3306;dbname=easyit_license;charset=utf8mb4
        // pgsql:host=127.0.0.1;port=5432;dbname=easyit_license
        'dsn' => getenv('EASYIT_LICENSE_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=easyit_license;charset=utf8mb4',
        'user' => getenv('EASYIT_LICENSE_DB_USER') ?: 'root',
        'password' => getenv('EASYIT_LICENSE_DB_PASSWORD') ?: '',
    ],
    'security' => [
        'server_key_id' => getenv('EASYIT_SERVER_KEY_ID') ?: 'SERVER-2026-01',
        'server_private_key_file' => getenv('EASYIT_SERVER_PRIVATE_KEY') ?: dirname(__DIR__) . '/secure/keys/server-private.key',
        'server_public_key_file' => getenv('EASYIT_SERVER_PUBLIC_KEY') ?: dirname(__DIR__) . '/secure/keys/server-public.key',
    ],
    'runtime' => [
        'lease_seconds' => 3600,
        'grace_seconds' => 86400,
        'action_ttl_seconds' => 60,
    ],
];
