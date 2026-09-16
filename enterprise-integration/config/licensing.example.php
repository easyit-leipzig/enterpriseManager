<?php
declare(strict_types=1);
return [
    'enabled' => true,
    'server_url' => 'https://license.example.invalid',
    'connect_timeout' => 5,
    'request_timeout' => 15,
    'verify_tls' => true,
    'storage_dir' => dirname(__DIR__) . '/storage/licensing',
    // Pin mindestens einen vertrauenswürdigen Server-Public-Key.
    // Den Wert liefert bin/generate-server-key.php auf dem Lizenzserver.
    'trusted_server_keys' => [
        'SERVER-2026-01' => 'BASE64_PUBLIC_KEY_HIER_EINTRAGEN',
    ],
];
