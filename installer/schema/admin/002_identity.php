<?php
declare(strict_types=1);
return static function (PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL UNIQUE, label VARCHAR(120) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(120) NOT NULL UNIQUE, email VARCHAR(190) NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (user_id BIGINT UNSIGNED NOT NULL, role_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(user_id, role_id), CONSTRAINT fk_ur_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, CONSTRAINT fk_ur_role FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT IGNORE INTO roles (name,label) VALUES ('admin','Administrator'),('editor','Bearbeiter'),('viewer','Leser')");
};
