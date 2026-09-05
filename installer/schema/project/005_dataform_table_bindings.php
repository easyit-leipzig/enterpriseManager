<?php
declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS dataform_table_bindings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            dataform_id BIGINT UNSIGNED NOT NULL,
            source_kind VARCHAR(30) NOT NULL DEFAULT 'system',
            source_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            table_name VARCHAR(64) NOT NULL,
            binding_mode VARCHAR(30) NOT NULL DEFAULT 'schema',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_dataform_table_binding_form(dataform_id),
            UNIQUE KEY uq_dataform_table_binding_source(source_kind,source_id,table_name),
            CONSTRAINT fk_dataform_table_binding_form
                FOREIGN KEY(dataform_id) REFERENCES dataforms(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci"
    );
};
