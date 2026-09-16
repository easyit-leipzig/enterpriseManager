<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Database;

use PDO;

final class MigrationRunner
{
    public function __construct(private PDO $pdo) {}

    public function migrate(): void
    {
        foreach ($this->statements() as $sql) $this->pdo->exec($sql);
        $this->seedModules();
    }

    private function statements(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS license_schema_migrations (migration_code VARCHAR(100) PRIMARY KEY, applied_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS customers (customer_id VARCHAR(64) PRIMARY KEY, customer_number VARCHAR(64) NOT NULL UNIQUE, name VARCHAR(255) NOT NULL, company VARCHAR(255) NULL, email VARCHAR(255) NULL, status VARCHAR(20) NOT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS licenses (license_id VARCHAR(64) PRIMARY KEY, license_number VARCHAR(64) NOT NULL UNIQUE, activation_secret_hash VARCHAR(255) NOT NULL, customer_id VARCHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, valid_from BIGINT NULL, valid_until BIGINT NULL, max_installations INTEGER NOT NULL, lease_seconds INTEGER NOT NULL, grace_seconds INTEGER NOT NULL, generation BIGINT NOT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS modules (module_code VARCHAR(100) PRIMARY KEY, name VARCHAR(255) NOT NULL, requires_runtime INTEGER NOT NULL, active INTEGER NOT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS license_modules (license_id VARCHAR(64) NOT NULL, module_code VARCHAR(100) NOT NULL, enabled INTEGER NOT NULL, valid_from BIGINT NULL, valid_until BIGINT NULL, configuration TEXT NULL, PRIMARY KEY (license_id,module_code))",
            "CREATE TABLE IF NOT EXISTS installations (installation_id VARCHAR(100) PRIMARY KEY, license_id VARCHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, generation BIGINT NOT NULL, product_code VARCHAR(100) NOT NULL, product_version VARCHAR(50) NULL, environment_hash VARCHAR(128) NULL, activated_at BIGINT NOT NULL, last_seen_at BIGINT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS installation_keys (key_id VARCHAR(100) PRIMARY KEY, installation_id VARCHAR(100) NOT NULL, algorithm VARCHAR(50) NOT NULL, public_key TEXT NOT NULL, status VARCHAR(20) NOT NULL, valid_from BIGINT NOT NULL, valid_until BIGINT NULL, created_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS activation_events (activation_event_id VARCHAR(64) PRIMARY KEY, installation_id VARCHAR(100) NOT NULL, license_id VARCHAR(64) NOT NULL, event_type VARCHAR(50) NOT NULL, request_id VARCHAR(100) NULL, created_at BIGINT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS dataform_definitions (definition_id VARCHAR(64) PRIMARY KEY, project_id VARCHAR(100) NOT NULL, dataform_id VARCHAR(100) NOT NULL, revision_number BIGINT NOT NULL, status VARCHAR(20) NOT NULL, definition_json TEXT NOT NULL, definition_hash VARCHAR(128) NOT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL, UNIQUE(project_id,dataform_id,revision_number))",
            "CREATE TABLE IF NOT EXISTS runtime_sessions (runtime_id VARCHAR(100) PRIMARY KEY, license_id VARCHAR(64) NOT NULL, installation_id VARCHAR(100) NOT NULL, project_id VARCHAR(100) NOT NULL, dataform_id VARCHAR(100) NOT NULL, definition_id VARCHAR(64) NOT NULL, license_generation BIGINT NOT NULL, installation_generation BIGINT NOT NULL, manifest_version VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, issued_at BIGINT NOT NULL, lease_until BIGINT NOT NULL, grace_until BIGINT NOT NULL, last_seen_at BIGINT NULL, manifest_hash VARCHAR(128) NOT NULL)",
            "CREATE TABLE IF NOT EXISTS runtime_actions (action_id VARCHAR(100) PRIMARY KEY, runtime_id VARCHAR(100) NOT NULL, action_type VARCHAR(100) NOT NULL, dataform_id VARCHAR(100) NOT NULL, resource_hash VARCHAR(128) NOT NULL, status VARCHAR(20) NOT NULL, issued_at BIGINT NOT NULL, valid_until BIGINT NOT NULL, completed_at BIGINT NULL, decision_hash VARCHAR(128) NOT NULL, result_code VARCHAR(100) NULL)",
            "CREATE TABLE IF NOT EXISTS used_nonces (installation_id VARCHAR(100) NOT NULL, nonce VARCHAR(128) NOT NULL, request_id VARCHAR(100) NOT NULL, expires_at BIGINT NOT NULL, created_at BIGINT NOT NULL, PRIMARY KEY (installation_id,nonce))",
            "CREATE TABLE IF NOT EXISTS audit_events (audit_id VARCHAR(64) PRIMARY KEY, event_type VARCHAR(100) NOT NULL, entity_type VARCHAR(100) NOT NULL, entity_id VARCHAR(100) NULL, actor_type VARCHAR(50) NOT NULL, actor_id VARCHAR(100) NULL, old_value TEXT NULL, new_value TEXT NULL, request_id VARCHAR(100) NULL, created_at BIGINT NOT NULL)",
        ];
    }

    private function seedModules(): void
    {
        $now=time();
        $stmt=$this->pdo->prepare('SELECT module_code FROM modules WHERE module_code=?');
        $ins=$this->pdo->prepare('INSERT INTO modules (module_code,name,requires_runtime,active,created_at,updated_at) VALUES (?,?,?,?,?,?)');
        foreach ([
            ['dataform','DataForm',1],['designer','DataForm Designer',1],['export','Export',1],['api','API',1],['themes','Themes',0]
        ] as [$code,$name,$runtime]) {
            $stmt->execute([$code]); if(!$stmt->fetch()) $ins->execute([$code,$name,$runtime,1,$now,$now]);
        }
    }
}
