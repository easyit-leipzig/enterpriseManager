<?php
declare(strict_types=1);
return static function (PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS enterprise_licenses (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        license_key VARCHAR(190) NOT NULL UNIQUE,
        holder VARCHAR(190) NOT NULL,
        edition VARCHAR(80) NOT NULL DEFAULT 'community',
        capabilities_json LONGTEXT NULL,
        products_json LONGTEXT NULL,
        valid_from DATE NULL,
        expires_at DATE NULL,
        grace_days INT UNSIGNED NOT NULL DEFAULT 0,
        enabled TINYINT(1) NOT NULL DEFAULT 1,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        metadata_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_license_enabled(enabled),
        INDEX idx_license_primary(is_primary),
        INDEX idx_license_expires(expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
