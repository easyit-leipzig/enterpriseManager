<?php
declare(strict_types=1);

namespace DataForm\Database\Csv;

use DataForm\Database\Contracts\RelationalDatabaseInterface;
use DataForm\Database\DatabaseException;

require_once __DIR__ . '/CsvDatabase.php';

final class CsvAdapter implements RelationalDatabaseInterface
{
    private array $config;
    private ?\CsvDatabase $engine = null;
    private array $transactionSnapshot = [];

    public function __construct(array $config) { $this->config = $config; }
    public function driver(): string { return 'csv'; }

    public function connect(): void
    {
        if ($this->engine instanceof \CsvDatabase) return;
        $basePath = (string)($this->config['base_path'] ?? dirname(__DIR__, 3) . '/storage/csv');
        $database = (string)($this->config['database'] ?? 'default');
        $this->engine = new \CsvDatabase($basePath, $database);
    }

    public function disconnect(): void { $this->engine = null; }
    public function isConnected(): bool { return $this->engine instanceof \CsvDatabase; }
    private function e(): \CsvDatabase { $this->connect(); return $this->engine; }

    public function tableExists(string $table): bool { return $this->e()->tableExists($table); }
    public function createTable(string $table, array $columns): void { $this->e()->createTable($table, $columns); }
    public function columns(string $table): array { return $this->e()->columns($table); }
    public function all(string $table): array { return $this->e()->all($table); }
    public function find(string $table, int $id): ?array { return $this->e()->find($table, $id); }
    public function where(string $table, array $criteria): array { return $this->e()->where($table, $criteria); }
    public function insert(string $table, array $data): int { return $this->e()->insert($table, $data); }
    public function update(string $table, int $id, array $data): bool { return $this->e()->update($table, $id, $data); }
    public function delete(string $table, int $id): bool { return $this->e()->delete($table, $id); }

    public function createOneToManyRelation(string $parentTable, string $childTable, string $foreignKey, string $onDelete = 'RESTRICT'): void { $this->e()->createOneToManyRelation($parentTable, $childTable, $foreignKey, $onDelete); }
    public function createManyToManyRelation(string $leftTable, string $rightTable, ?string $pivotTable = null, ?string $leftKey = null, ?string $rightKey = null, string $onDelete = 'CASCADE'): void { $this->e()->createManyToManyRelation($leftTable, $rightTable, $pivotTable, $leftKey, $rightKey, $onDelete); }
    public function attach(string $leftTable, int $leftId, string $rightTable, int $rightId, ?string $pivotTable = null): int { return $this->e()->attach($leftTable, $leftId, $rightTable, $rightId, $pivotTable); }
    public function detach(string $leftTable, int $leftId, string $rightTable, int $rightId, ?string $pivotTable = null): bool { return $this->e()->detach($leftTable, $leftId, $rightTable, $rightId, $pivotTable); }
    public function sync(string $leftTable, int $leftId, string $rightTable, array $rightIds, ?string $pivotTable = null): void { $this->e()->sync($leftTable, $leftId, $rightTable, $rightIds, $pivotTable); }
    public function related(string $sourceTable, int $sourceId, string $targetTable, ?string $pivotTable = null): array { return $this->e()->related($sourceTable, $sourceId, $targetTable, $pivotTable); }

    public function beginTransaction(): void
    {
        if ($this->transactionSnapshot !== []) throw new DatabaseException('CSV-Transaktion ist bereits aktiv.');
        $path = $this->e()->getDatabasePath();
        foreach (glob($path . '/*') ?: [] as $file) {
            if (is_file($file)) $this->transactionSnapshot[basename($file)] = file_get_contents($file);
        }
    }

    public function commit(): void { $this->transactionSnapshot = []; }

    public function rollBack(): void
    {
        if ($this->transactionSnapshot === []) return;
        $path = $this->e()->getDatabasePath();
        foreach (glob($path . '/*') ?: [] as $file) if (is_file($file)) unlink($file);
        foreach ($this->transactionSnapshot as $name => $content) file_put_contents($path . DIRECTORY_SEPARATOR . $name, $content, LOCK_EX);
        $this->transactionSnapshot = [];
    }
}
