<?php
declare(strict_types=1);
return static function (PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(190) NOT NULL UNIQUE, checksum CHAR(64) NOT NULL, executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->prepare("INSERT IGNORE INTO migrations (migration, checksum) VALUES (?, ?)")->execute([basename(__FILE__), hash_file('sha256', __FILE__)]);
};
