<?php
declare(strict_types=1);

require_once __DIR__ . '/DataSourceManager.php';
require_once dirname(__DIR__, 3) . '/DataForm5-Core/system/database/autoload.php';

use DataForm\Database\DatabaseFactory;

final class TableWorkspaceManager
{
    public static function ensureRegistry(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS dataform_managed_tables (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(64) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_dataform_managed_table(table_name)
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function isManaged(PDO $pdo, string $table): bool
    {
        self::ensureRegistry($pdo);
        $stmt=$pdo->prepare(
            'SELECT COUNT(*) FROM dataform_managed_tables WHERE table_name=?'
        );
        $stmt->execute([$table]);
        return (int)$stmt->fetchColumn() === 1;
    }


    /**
     * HF40: Projekt-Anwendungstabellen dürfen auf Feldebene per CRUD gepflegt
     * werden, auch wenn sie nicht ursprünglich durch den Tabellenassistenten
     * angelegt und deshalb noch nicht in dataform_managed_tables registriert
     * wurden. Interne DataForm-/Workflow-/Migrations-Tabellen bleiben strikt
     * geschützt.
     */
    public static function isProtectedInternalTable(string $table): bool
    {
        $normalized=strtolower(trim($table));
        if ($normalized === '') {
            return true;
        }

        if (
            str_starts_with($normalized,'dataform_')
            || str_starts_with($normalized,'workflow_')
        ) {
            return true;
        }

        return in_array(
            $normalized,
            ['data_sources','migrations'],
            true
        );
    }

    public static function isApplicationTable(PDO $pdo,string $table): bool
    {
        self::assertManagedIdentifier($table,'Tabellenname');
        if (self::isProtectedInternalTable($table)) {
            return false;
        }
        if (!self::systemTableExists($pdo,$table)) {
            return false;
        }

        $stmt=$pdo->prepare(
            "SELECT table_type FROM information_schema.tables "
            ."WHERE table_schema=DATABASE() AND table_name=? LIMIT 1"
        );
        $stmt->execute([$table]);
        $type=strtoupper((string)$stmt->fetchColumn());
        return $type === 'BASE TABLE';
    }

    public static function fieldCrudAllowed(PDO $pdo,string $table): bool
    {
        return self::isManaged($pdo,$table)
            || self::isApplicationTable($pdo,$table);
    }

    public static function createManagedTable(
        PDO $pdo,
        string $table,
        string $columnSpecification
    ): void {
        self::ensureRegistry($pdo);
        self::assertManagedIdentifier($table, 'Tabellenname');

        if (self::systemTableExists($pdo, $table)) {
            throw new RuntimeException('Eine Tabelle mit diesem Namen existiert bereits.');
        }

        $columns=self::parseColumns($columnSpecification);
        if ($columns === []) {
            throw new RuntimeException(
                'Geben Sie mindestens eine zusätzliche Spalte an.'
            );
        }

        $defs=[
            '`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
        ];
        foreach ($columns as $column) {
            $defs[]='`'.str_replace('`','``',$column['name']).'` '
                .$column['sql'].' NULL';
        }

        try {
            $pdo->exec(
                'CREATE TABLE `'.str_replace('`','``',$table).'` ('
                .implode(', ', $defs)
                .') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 '
                .'COLLATE=utf8mb4_unicode_ci'
            );
            $stmt=$pdo->prepare(
                'INSERT INTO dataform_managed_tables(table_name) VALUES(?)'
            );
            $stmt->execute([$table]);
        } catch (Throwable $e) {
            if (self::systemTableExists($pdo, $table)) {
                try {
                    $pdo->exec(
                        'DROP TABLE `'.str_replace('`','``',$table).'`'
                    );
                } catch (Throwable) {
                }
            }
            throw $e;
        }
    }

    public static function dropManagedTable(PDO $pdo, string $table): void
    {
        self::assertManagedIdentifier($table, 'Tabellenname');
        if (!self::isManaged($pdo, $table)) {
            throw new RuntimeException(
                'Aus Sicherheitsgründen können nur Tabellen gelöscht werden, '
                .'die über die DataForm-Tabellenverwaltung angelegt wurden.'
            );
        }

        $bindingTableExists=(int)$pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables "
            ."WHERE table_schema=DATABASE() AND table_name='dataform_table_bindings'"
        )->fetchColumn()===1;
        if ($bindingTableExists) {
            $binding=$pdo->prepare(
                "SELECT b.dataform_id,d.name "
                ."FROM dataform_table_bindings b "
                ."JOIN dataforms d ON d.id=b.dataform_id "
                ."WHERE b.source_kind='system' "
                ."AND b.source_id=0 AND b.table_name=? LIMIT 1"
            );
            $binding->execute([$table]);
            $bound=$binding->fetch(PDO::FETCH_ASSOC);
            if ($bound) {
                throw new RuntimeException(
                    'Die Tabelle ist mit dem DataForm „'.(string)$bound['name']
                    .'“ verbunden. Löschen Sie zuerst das zugehörige DataForm.'
                );
            }
        }
        if (!self::systemTableExists($pdo, $table)) {
            $stmt=$pdo->prepare(
                'DELETE FROM dataform_managed_tables WHERE table_name=?'
            );
            $stmt->execute([$table]);
            throw new RuntimeException(
                'Die registrierte Tabelle existiert physisch nicht mehr.'
            );
        }

        $pdo->exec(
            'DROP TABLE `'.str_replace('`','``',$table).'`'
        );
        $stmt=$pdo->prepare(
            'DELETE FROM dataform_managed_tables WHERE table_name=?'
        );
        $stmt->execute([$table]);
    }

    public static function normalizeColumnRequest(
        PDO $pdo,
        string $table,
        ?string $currentColumn,
        string $type,
        bool $nullable,
        string $defaultKind,
        ?string $defaultValue,
        array $extras,
        string $indexKind
    ): array {
        self::assertFieldCrudTable($pdo,$table);

        $baseType=strtolower(trim($type));
        self::normalizeColumnType($baseType);

        $numeric=preg_match(
            '/^(?:int|integer|bigint|decimal\(\d{1,2},\d{1,2}\))$/',
            $baseType
        )===1;
        $integer=in_array($baseType,['int','integer','bigint'],true);
        $temporal=in_array($baseType,['datetime','timestamp'],true);
        $indexable=$baseType !== 'text';

        $extras=self::normalizeExtras($extras);
        $defaultKind=strtolower(trim($defaultKind));
        if (!in_array(
            $defaultKind,
            ['none','null','literal','current_timestamp'],
            true
        )) {
            $defaultKind='none';
        }
        $indexKind=self::normalizeIndexKind($indexKind);
        $messages=[];

        $removeExtra=static function(
            string $extra,
            string $message
        ) use (&$extras,&$messages): void {
            $before=count($extras);
            $extras=array_values(array_filter(
                $extras,
                static fn(string $value): bool => $value !== $extra
            ));
            if (count($extras) !== $before) {
                $messages[]=$message;
            }
        };

        if (!$numeric) {
            $removeExtra(
                'unsigned',
                'UNSIGNED wurde entfernt, weil der gewählte Datentyp nicht numerisch ist.'
            );
            $removeExtra(
                'zerofill',
                'ZEROFILL wurde entfernt, weil der gewählte Datentyp nicht numerisch ist.'
            );
        }

        if (
            $numeric
            && in_array('zerofill',$extras,true)
            && !in_array('unsigned',$extras,true)
        ) {
            $extras[]='unsigned';
            $messages[]='UNSIGNED wurde automatisch ergänzt, weil ZEROFILL diese Eigenschaft voraussetzt.';
        }

        if (!$integer) {
            $removeExtra(
                'auto_increment',
                'AUTO_INCREMENT wurde entfernt, weil es nur für INT/BIGINT möglich ist.'
            );
        }

        if ($nullable) {
            $removeExtra(
                'auto_increment',
                'AUTO_INCREMENT wurde entfernt, weil das Feld NULL-Werte zulässt.'
            );
        }

        if (in_array('auto_increment',$extras,true)) {
            $stmt=$pdo->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema=DATABASE()
                   AND table_name=?
                   AND LOWER(extra) LIKE '%auto_increment%'
                   AND (? IS NULL OR column_name<>?)
                 LIMIT 1"
            );
            $stmt->execute([$table,$currentColumn,$currentColumn]);
            $existing=$stmt->fetchColumn();
            if ($existing !== false) {
                $removeExtra(
                    'auto_increment',
                    'AUTO_INCREMENT wurde entfernt, weil die Tabelle bereits das AUTO_INCREMENT-Feld „'
                    .(string)$existing.'“ besitzt.'
                );
            }
        }

        if (!$temporal) {
            $removeExtra(
                'on_update_current_timestamp',
                'ON UPDATE CURRENT_TIMESTAMP wurde entfernt, weil es nur für DATETIME/TIMESTAMP möglich ist.'
            );
        }

        if ($defaultKind === 'current_timestamp' && !$temporal) {
            $defaultKind='none';
            $defaultValue=null;
            $messages[]='CURRENT_TIMESTAMP wurde als Vorgabewert zurückgesetzt, weil der gewählte Datentyp nicht DATETIME/TIMESTAMP ist.';
        }

        if ($defaultKind === 'null' && !$nullable) {
            $defaultKind='none';
            $defaultValue=null;
            $messages[]='DEFAULT NULL wurde zurückgesetzt, weil das Feld keine NULL-Werte zulässt.';
        }

        if (
            in_array('auto_increment',$extras,true)
            && $defaultKind !== 'none'
        ) {
            $defaultKind='none';
            $defaultValue=null;
            $messages[]='Der Vorgabewert wurde entfernt, weil AUTO_INCREMENT keinen eigenen Vorgabewert zulässt.';
        }

        if ($indexKind !== 'none' && !$indexable) {
            $indexKind='none';
            $messages[]='Der Sekundärindex wurde entfernt, weil TEXT ohne Präfix in dieser Feldverwaltung nicht indexiert wird.';
        }

        return [
            'type'=>$baseType,
            'nullable'=>$nullable,
            'default_kind'=>$defaultKind,
            'default_value'=>$defaultValue,
            'extras'=>array_values(array_unique($extras)),
            'index_kind'=>$indexKind,
            'messages'=>array_values(array_unique($messages)),
        ];
    }

    public static function addManagedColumn(
        PDO $pdo,
        string $table,
        string $column,
        string $type,
        bool $nullable,
        string $defaultKind='none',
        ?string $defaultValue=null,
        array $extras=[],
        string $indexKind='none'
    ): void {
        self::assertFieldCrudTable($pdo,$table);
        self::assertManagedIdentifier($column,'Feldname');
        if ($column === 'id') {
            throw new RuntimeException('Das Pflichtfeld id wird von DataForm verwaltet.');
        }
        if (self::columnMetadata($pdo,$table,$column) !== null) {
            throw new RuntimeException('Ein Feld mit diesem Namen existiert bereits.');
        }

        $definition=self::buildColumnDefinition(
            $pdo,
            $table,
            null,
            $type,
            $nullable,
            $defaultKind,
            $defaultValue,
            $extras
        );
        $indexKind=self::normalizeIndexKind($indexKind);

        $actions=[
            'ADD COLUMN '.self::quoteIdentifier($column,'mysql').' '.$definition,
        ];
        if ($indexKind !== 'none') {
            $indexName=self::managedIndexName(
                $table,
                $column,
                $indexKind === 'unique'
            );
            $actions[]='ADD '.($indexKind==='unique'?'UNIQUE ':'')
                .'INDEX '.self::quoteIdentifier($indexName,'mysql')
                .' ('.self::quoteIdentifier($column,'mysql').')';
        }

        $pdo->exec(
            'ALTER TABLE '.self::quoteIdentifier($table,'mysql')
            .' '.implode(', ',$actions)
        );
    }

    public static function updateManagedColumn(
        PDO $pdo,
        string $table,
        string $originalColumn,
        string $newColumn,
        string $type,
        bool $nullable,
        string $defaultKind='none',
        ?string $defaultValue=null,
        array $extras=[],
        string $indexKind='none'
    ): void {
        self::assertFieldCrudTable($pdo,$table);
        self::assertManagedIdentifier($originalColumn,'Bisheriger Feldname');
        self::assertManagedIdentifier($newColumn,'Feldname');

        self::assertMutableColumn($pdo,$table,$originalColumn);
        if (
            $newColumn !== $originalColumn
            && self::columnMetadata($pdo,$table,$newColumn) !== null
        ) {
            throw new RuntimeException('Ein Feld mit dem neuen Namen existiert bereits.');
        }

        $definition=self::buildColumnDefinition(
            $pdo,
            $table,
            $originalColumn,
            $type,
            $nullable,
            $defaultKind,
            $defaultValue,
            $extras
        );
        $indexKind=self::normalizeIndexKind($indexKind);

        if ($indexKind === 'unique') {
            self::assertUniqueValues($pdo,$table,$originalColumn);
        }

        $managedIndexes=self::managedSecondaryIndexes(
            $pdo,
            $table,
            $originalColumn
        );

        $actions=[
            'CHANGE COLUMN '.self::quoteIdentifier($originalColumn,'mysql')
            .' '.self::quoteIdentifier($newColumn,'mysql')
            .' '.$definition,
        ];

        foreach ($managedIndexes as $indexName) {
            $actions[]='DROP INDEX '.self::quoteIdentifier($indexName,'mysql');
        }

        if ($indexKind !== 'none') {
            $indexName=self::managedIndexName(
                $table,
                $newColumn,
                $indexKind === 'unique'
            );
            $actions[]='ADD '.($indexKind==='unique'?'UNIQUE ':'')
                .'INDEX '.self::quoteIdentifier($indexName,'mysql')
                .' ('.self::quoteIdentifier($newColumn,'mysql').')';
        }

        $pdo->exec(
            'ALTER TABLE '.self::quoteIdentifier($table,'mysql')
            .' '.implode(', ',$actions)
        );
    }

    public static function moveManagedColumn(
        PDO $pdo,
        string $table,
        string $column,
        string $direction
    ): array {
        self::assertFieldCrudTable($pdo,$table);
        self::assertManagedIdentifier($column,'Feldname');

        if ($column === 'id') {
            throw new RuntimeException(
                'Das DataForm-Pflichtfeld id bleibt an erster Stelle und kann nicht verschoben werden.'
            );
        }

        $direction=strtolower(trim($direction));
        if (!in_array($direction,['up','down'],true)) {
            throw new RuntimeException('Ungültige Verschieberichtung.');
        }

        $stmt=$pdo->prepare(
            "SELECT column_name,ordinal_position
             FROM information_schema.columns
             WHERE table_schema=DATABASE()
               AND table_name=?
             ORDER BY ordinal_position"
        );
        $stmt->execute([$table]);
        $columns=array_map(
            static fn(array $row): string => (string)$row['column_name'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        $index=array_search($column,$columns,true);
        if ($index === false) {
            throw new RuntimeException('Das zu verschiebende Feld wurde nicht gefunden.');
        }

        if ($direction === 'up') {
            if ($index <= 1) {
                throw new RuntimeException(
                    'Das Feld steht bereits direkt hinter dem geschützten id-Feld.'
                );
            }
            $after=$columns[$index-2];
        } else {
            if ($index >= count($columns)-1) {
                throw new RuntimeException(
                    'Das Feld steht bereits an der letzten möglichen Position.'
                );
            }
            $after=$columns[$index+1];
        }

        $definition=self::exactColumnDefinition($pdo,$table,$column);
        $pdo->exec(
            'ALTER TABLE '.self::quoteIdentifier($table,'mysql')
            .' MODIFY COLUMN '.self::quoteIdentifier($column,'mysql')
            .' '.$definition
            .' AFTER '.self::quoteIdentifier($after,'mysql')
        );

        return ['column'=>$column,'direction'=>$direction,'after'=>$after];
    }

    public static function dropManagedColumn(
        PDO $pdo,
        string $table,
        string $column
    ): void {
        self::assertFieldCrudTable($pdo,$table);
        self::assertManagedIdentifier($column,'Feldname');
        self::assertMutableColumn($pdo,$table,$column);

        $pdo->exec(
            'ALTER TABLE '.self::quoteIdentifier($table,'mysql')
            .' DROP COLUMN '.self::quoteIdentifier($column,'mysql')
        );
    }

    public static function columnMutationState(
        PDO $pdo,
        string $table,
        string $column
    ): array {
        if (!self::fieldCrudAllowed($pdo,$table)) {
            return ['allowed'=>false,'reason'=>'geschützte Systemtabelle'];
        }
        if ($column === 'id') {
            return ['allowed'=>false,'reason'=>'DataForm-Pflichtfeld'];
        }
        if (self::columnMetadata($pdo,$table,$column) === null) {
            return ['allowed'=>false,'reason'=>'Feld nicht gefunden'];
        }

        $stmt=$pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA=DATABASE()
               AND (
                    (
                        TABLE_NAME=?
                        AND COLUMN_NAME=?
                        AND (
                            CONSTRAINT_NAME='PRIMARY'
                            OR REFERENCED_TABLE_NAME IS NOT NULL
                        )
                    )
                    OR
                    (
                        REFERENCED_TABLE_NAME=?
                        AND REFERENCED_COLUMN_NAME=?
                    )
               )"
        );
        $stmt->execute([$table,$column,$table,$column]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['allowed'=>false,'reason'=>'Primär-/Fremdschlüssel'];
        }

        return ['allowed'=>true,'reason'=>''];
    }

    public static function editableColumnType(array $column): string
    {
        $type=strtolower(trim((string)($column['type']??'')));
        if (preg_match('/^varchar\\((\\d+)\\)/',$type,$m)===1) {
            return 'varchar('.(int)$m[1].')';
        }
        if (preg_match('/^decimal\\((\\d+),(\\d+)\\)/',$type,$m)===1) {
            return 'decimal('.(int)$m[1].','.(int)$m[2].')';
        }
        if (preg_match('/^tinyint\\(1\\)/',$type)===1) return 'boolean';
        if (preg_match('/^int(?:\\(\\d+\\))?/',$type)===1) return 'int';
        if (preg_match('/^bigint(?:\\(\\d+\\))?/',$type)===1) return 'bigint';
        if (str_starts_with($type,'datetime')) return 'datetime';
        if (str_starts_with($type,'timestamp')) return 'timestamp';
        if ($type==='date') return 'date';
        if (str_contains($type,'text')) return 'text';
        return $type;
    }

    public static function catalog(
        PDO $projectPdo,
        ?array $source,
        string $keyMaterial
    ): array {
        if ($source === null) {
            $stmt=$projectPdo->query(
                "SELECT table_name AS name, table_type AS kind
                 FROM information_schema.tables
                 WHERE table_schema=DATABASE()
                 ORDER BY table_name"
            );
            return array_map(
                static fn(array $row): array => [
                    'name'=>(string)$row['name'],
                    'type'=>(string)$row['kind'],
                ],
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        $driver=(string)$source['driver'];
        $config=DataSourceManager::runtimeConfig($source,$keyMaterial);

        if ($driver === 'csv') {
            $path=rtrim((string)$config['base_path'], '/\\')
                .DIRECTORY_SEPARATOR.(string)$config['database'];
            if (!is_dir($path)) {
                throw new RuntimeException(
                    'Der CSV-Datenbankordner existiert nicht: '.$path
                );
            }
            $rows=[];
            foreach (glob($path.DIRECTORY_SEPARATOR.'*.csv') ?: [] as $file) {
                $rows[]=[
                    'name'=>pathinfo($file, PATHINFO_FILENAME),
                    'type'=>'CSV',
                ];
            }
            usort(
                $rows,
                static fn(array $a,array $b): int =>
                    strcasecmp($a['name'],$b['name'])
            );
            return $rows;
        }

        $pdo=self::externalPdo($source,$keyMaterial);

        if ($driver === 'mysql') {
            $stmt=$pdo->query(
                "SELECT table_name AS name, table_type AS kind
                 FROM information_schema.tables
                 WHERE table_schema=DATABASE()
                 ORDER BY table_name"
            );
            return array_map(
                static fn(array $row): array => [
                    'name'=>(string)$row['name'],
                    'type'=>(string)$row['kind'],
                ],
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        if ($driver === 'sqlite') {
            $stmt=$pdo->query(
                "SELECT name,type
                 FROM sqlite_master
                 WHERE type IN ('table','view')
                   AND name NOT LIKE 'sqlite_%'
                 ORDER BY name"
            );
            return array_map(
                static fn(array $row): array => [
                    'name'=>(string)$row['name'],
                    'type'=>strtoupper((string)$row['type']),
                ],
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        if ($driver === 'oracle') {
            $stmt=$pdo->query(
                "SELECT table_name
                 FROM user_tables
                 ORDER BY table_name"
            );
            return array_map(
                static fn(array $row): array => [
                    'name'=>strtolower(
                        (string)($row['TABLE_NAME'] ?? $row['table_name'] ?? '')
                    ),
                    'type'=>'TABLE',
                ],
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        throw new RuntimeException('Nicht unterstützter Tabellenquellentyp.');
    }

    public static function inspect(
        PDO $projectPdo,
        ?array $source,
        string $keyMaterial,
        string $table,
        int $previewLimit=20
    ): array {
        $catalog=self::catalog($projectPdo,$source,$keyMaterial);
        $match=null;
        foreach ($catalog as $item) {
            if ((string)$item['name'] === $table) {
                $match=$item;
                break;
            }
        }
        if ($match === null) {
            throw new RuntimeException(
                'Die ausgewählte Tabelle gehört nicht zur gewählten Datenquelle.'
            );
        }

        if ($source === null) {
            $inspection=self::inspectPdo(
                $projectPdo,
                'mysql',
                $table,
                $previewLimit
            );
            $managed=self::isManaged($projectPdo,$table);
            $application=self::isApplicationTable($projectPdo,$table);
            $fieldCrud=$managed || $application;
            foreach ($inspection['columns'] as &$column) {
                $column['editable_type']=self::editableColumnType($column);
                $column['default_kind']=self::editableDefaultKind($column);
                $column['default_value']=$column['default_kind']==='literal'
                    ? (string)(
                        array_key_exists('default_raw',$column)
                            ? $column['default_raw']
                            : ($column['default']??'')
                    )
                    : '';
                $column['editable_extras']=self::editableExtras($column);
                $indexState=self::columnIndexState(
                    $projectPdo,
                    $table,
                    (string)$column['name']
                );
                $column['secondary_index']=(string)$indexState['managed_kind'];
                $column['managed_indexes']=$indexState['managed_indexes'];
                $column['external_indexes']=$indexState['external_indexes'];
                $state=$fieldCrud
                    ? self::columnMutationState($projectPdo,$table,(string)$column['name'])
                    : ['allowed'=>false,'reason'=>'geschützte Systemtabelle'];
                $column['mutable']=(bool)$state['allowed'];
                $column['protection_reason']=(string)$state['reason'];
            }
            unset($column);
            return $inspection + [
                'type'=>$match['type'],
                'managed'=>$managed,
                'application'=>$application,
                'field_crud'=>$fieldCrud,
            ];
        }

        $driver=(string)$source['driver'];

        if ($driver === 'csv') {
            $config=DataSourceManager::runtimeConfig(
                $source,
                $keyMaterial
            );
            $db=DatabaseFactory::create(
                $config+['auto_connect'=>true]
            );
            try {
                $columns=array_map(
                    static fn(string $name): array => [
                        'name'=>$name,
                        'type'=>'TEXT',
                        'nullable'=>$name==='id'?'Nein':'Ja',
                        'key'=>$name==='id'?'PRIMARY':'',
                        'default'=>'',
                        'extra'=>'CSV',
                    ],
                    $db->columns($table)
                );
                $all=$db->all($table);
                return [
                    'type'=>'CSV',
                    'columns'=>$columns,
                    'row_count'=>count($all),
                    'preview'=>array_slice($all,0,$previewLimit),
                    'managed'=>false,
                ];
            } finally {
                $db->disconnect();
            }
        }

        return self::inspectPdo(
            self::externalPdo($source,$keyMaterial),
            $driver,
            $table,
            $previewLimit
        ) + [
            'type'=>$match['type'],
            'managed'=>false,
        ];
    }

    private static function inspectPdo(
        PDO $pdo,
        string $driver,
        string $table,
        int $previewLimit
    ): array {
        $previewLimit=max(1,min(100,$previewLimit));

        if ($driver === 'mysql') {
            $quoted=self::quoteIdentifier($table,'mysql');
            $raw=$pdo->query(
                'SHOW FULL COLUMNS FROM '.$quoted
            )->fetchAll(PDO::FETCH_ASSOC);
            $columns=array_map(
                static fn(array $row): array => [
                    'name'=>(string)$row['Field'],
                    'type'=>(string)$row['Type'],
                    'nullable'=>(string)$row['Null'],
                    'key'=>(string)$row['Key'],
                    'default'=>$row['Default']===null?'NULL':(string)$row['Default'],
                    'default_raw'=>$row['Default'],
                    'extra'=>(string)$row['Extra'],
                ],
                $raw
            );
            $count=(int)$pdo->query(
                'SELECT COUNT(*) FROM '.$quoted
            )->fetchColumn();
            $preview=$pdo->query(
                'SELECT * FROM '.$quoted.' LIMIT '.$previewLimit
            )->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($driver === 'sqlite') {
            $quoted=self::quoteIdentifier($table,'sqlite');
            $raw=$pdo->query(
                'PRAGMA table_info('.$quoted.')'
            )->fetchAll(PDO::FETCH_ASSOC);
            $columns=array_map(
                static fn(array $row): array => [
                    'name'=>(string)$row['name'],
                    'type'=>(string)$row['type'],
                    'nullable'=>(int)$row['notnull']===1?'Nein':'Ja',
                    'key'=>(int)$row['pk']===1?'PRIMARY':'',
                    'default'=>$row['dflt_value']===null?'NULL':(string)$row['dflt_value'],
                    'extra'=>'',
                ],
                $raw
            );
            $count=(int)$pdo->query(
                'SELECT COUNT(*) FROM '.$quoted
            )->fetchColumn();
            $preview=$pdo->query(
                'SELECT * FROM '.$quoted.' LIMIT '.$previewLimit
            )->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($driver === 'oracle') {
            $stmt=$pdo->prepare(
                "SELECT column_name,data_type,nullable,data_default,column_id
                 FROM user_tab_columns
                 WHERE table_name=UPPER(:table)
                 ORDER BY column_id"
            );
            $stmt->execute(['table'=>$table]);
            $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns=array_map(
                static fn(array $row): array => [
                    'name'=>strtolower((string)($row['COLUMN_NAME']??'')),
                    'type'=>(string)($row['DATA_TYPE']??''),
                    'nullable'=>(string)($row['NULLABLE']??''),
                    'key'=>'',
                    'default'=>trim((string)($row['DATA_DEFAULT']??'')),
                    'extra'=>'',
                ],
                $raw
            );
            $quoted=self::quoteIdentifier(strtoupper($table),'oracle');
            $count=(int)$pdo->query(
                'SELECT COUNT(*) FROM '.$quoted
            )->fetchColumn();
            $preview=$pdo->query(
                'SELECT * FROM '.$quoted
                .' FETCH FIRST '.$previewLimit.' ROWS ONLY'
            )->fetchAll(PDO::FETCH_ASSOC);
        } else {
            throw new RuntimeException(
                'Der Tabellenadapter unterstützt diese Quelle nicht.'
            );
        }

        return [
            'columns'=>$columns,
            'row_count'=>$count,
            'preview'=>$preview,
        ];
    }

    private static function externalPdo(
        array $source,
        string $keyMaterial
    ): PDO {
        $driver=(string)$source['driver'];
        $config=DataSourceManager::runtimeConfig(
            $source,
            $keyMaterial
        );

        if ($driver === 'mysql') {
            $dsn='mysql:host='.(string)($config['host']??'127.0.0.1')
                .';port='.(int)($config['port']??3306)
                .';dbname='.(string)($config['database']??'')
                .';charset='.(string)($config['charset']??'utf8mb4');
            $username=(string)($config['username']??'');
            $password=(string)($config['password']??'');
        } elseif ($driver === 'sqlite') {
            $dsn='sqlite:'.(string)($config['path']??'');
            $username='';
            $password='';
        } elseif ($driver === 'oracle') {
            $dsn=trim((string)($config['dsn']??''));
            if ($dsn === '') {
                $dsn='oci:dbname=//'
                    .(string)($config['host']??'127.0.0.1')
                    .':'.(int)($config['port']??1521)
                    .'/'.(string)($config['service_name']??'FREEPDB1')
                    .';charset='.(string)($config['charset']??'AL32UTF8');
            }
            $username=(string)($config['username']??'');
            $password=(string)($config['password']??'');
        } else {
            throw new RuntimeException(
                'Für diese Datenquelle ist keine PDO-Tabelleninspektion verfügbar.'
            );
        }

        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES=>false,
            ]
        );
    }

    private static function assertFieldCrudTable(PDO $pdo,string $table): void
    {
        self::assertManagedIdentifier($table,'Tabellenname');
        if (!self::fieldCrudAllowed($pdo,$table)) {
            throw new RuntimeException(
                'Feldänderungen sind für diese geschützte Systemtabelle nicht zulässig.'
            );
        }
        if (!self::systemTableExists($pdo,$table)) {
            throw new RuntimeException('Die Projekttabelle existiert physisch nicht.');
        }
    }

    private static function assertMutableColumn(
        PDO $pdo,
        string $table,
        string $column
    ): array {
        if ($column === 'id') {
            throw new RuntimeException('Das Pflichtfeld id darf nicht geändert oder gelöscht werden.');
        }
        $meta=self::columnMetadata($pdo,$table,$column);
        if ($meta === null) {
            throw new RuntimeException('Das Feld wurde nicht gefunden.');
        }
        $state=self::columnMutationState($pdo,$table,$column);
        if (!$state['allowed']) {
            throw new RuntimeException(
                'Das Feld ist geschützt: '.(string)$state['reason'].'.'
            );
        }
        return $meta;
    }

    private static function columnMetadata(
        PDO $pdo,
        string $table,
        string $column
    ): ?array {
        $stmt=$pdo->prepare(
            "SELECT column_name,column_type,is_nullable,column_default,extra
             FROM information_schema.columns
             WHERE table_schema=DATABASE()
               AND table_name=?
               AND column_name=?
             LIMIT 1"
        );
        $stmt->execute([$table,$column]);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        if ($row===false) return null;
        return [
            'name'=>(string)$row['column_name'],
            'type'=>(string)$row['column_type'],
            'nullable'=>(string)$row['is_nullable'],
            'default'=>$row['column_default'],
            'extra'=>(string)$row['extra'],
        ];
    }

    private static function editableDefaultKind(array $column): string
    {
        $default=array_key_exists('default_raw',$column)
            ? $column['default_raw']
            : ($column['default']??null);
        if ($default === null) {
            return strtoupper((string)($column['nullable']??'NO')) === 'YES'
                ? 'null'
                : 'none';
        }

        $defaultString=trim((string)$default);
        if (
            preg_match(
                '/^current_timestamp(?:\(\))?$/i',
                $defaultString
            ) === 1
        ) {
            return 'current_timestamp';
        }

        return 'literal';
    }

    private static function editableExtras(array $column): array
    {
        $type=strtolower((string)($column['type']??''));
        $extra=strtolower((string)($column['extra']??''));
        $values=[];

        if (preg_match('/\bunsigned\b/',$type)===1) {
            $values[]='unsigned';
        }
        if (preg_match('/\bzerofill\b/',$type)===1) {
            $values[]='zerofill';
        }
        if (str_contains($extra,'auto_increment')) {
            $values[]='auto_increment';
        }
        if (str_contains($extra,'on update current_timestamp')) {
            $values[]='on_update_current_timestamp';
        }

        return $values;
    }

    public static function columnIndexState(
        PDO $pdo,
        string $table,
        string $column
    ): array {
        self::assertManagedIdentifier($table,'Tabellenname');
        self::assertManagedIdentifier($column,'Feldname');

        $stmt=$pdo->prepare(
            "SELECT index_name,non_unique,seq_in_index
             FROM information_schema.statistics
             WHERE table_schema=DATABASE()
               AND table_name=?
               AND column_name=?
               AND index_name<>'PRIMARY'
             ORDER BY index_name,seq_in_index"
        );
        $stmt->execute([$table,$column]);

        $managed=[];
        $external=[];
        $managedKind='none';

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $name=(string)$row['index_name'];
            $isManaged=str_starts_with($name,'idx_df_')
                || str_starts_with($name,'uq_df_');

            if ($isManaged && (int)$row['seq_in_index']===1) {
                $managed[]=$name;
                if (str_starts_with($name,'uq_df_')) {
                    $managedKind='unique';
                } elseif ($managedKind !== 'unique') {
                    $managedKind='index';
                }
            } else {
                $external[]=$name;
            }
        }

        return [
            'managed_kind'=>$managedKind,
            'managed_indexes'=>array_values(array_unique($managed)),
            'external_indexes'=>array_values(array_unique($external)),
        ];
    }

    private static function managedSecondaryIndexes(
        PDO $pdo,
        string $table,
        string $column
    ): array {
        $state=self::columnIndexState($pdo,$table,$column);
        return (array)$state['managed_indexes'];
    }

    private static function managedIndexName(
        string $table,
        string $column,
        bool $unique
    ): string {
        $prefix=$unique?'uq_df_':'idx_df_';
        $hash=substr(hash('sha256',$table."\0".$column),0,10);
        $stem=preg_replace('/[^A-Za-z0-9_]/','_',$table.'_'.$column) ?: 'field';
        $maxStem=64-strlen($prefix)-1-strlen($hash);
        return $prefix.substr($stem,0,max(1,$maxStem)).'_'.$hash;
    }

    private static function normalizeIndexKind(string $kind): string
    {
        $kind=strtolower(trim($kind));
        if (!in_array($kind,['none','index','unique'],true)) {
            throw new RuntimeException('Ungültiger Sekundärindextyp.');
        }
        return $kind;
    }

    private static function normalizeExtras(array $extras): array
    {
        $allowed=[
            'unsigned',
            'zerofill',
            'auto_increment',
            'on_update_current_timestamp',
        ];
        $values=[];
        foreach ($extras as $extra) {
            $extra=strtolower(trim((string)$extra));
            if ($extra === '') continue;
            if (!in_array($extra,$allowed,true)) {
                throw new RuntimeException(
                    'Nicht unterstützte Extra-Eigenschaft „'.$extra.'“.'
                );
            }
            $values[$extra]=true;
        }
        return array_keys($values);
    }

    private static function buildColumnDefinition(
        PDO $pdo,
        string $table,
        ?string $currentColumn,
        string $type,
        bool $nullable,
        string $defaultKind,
        ?string $defaultValue,
        array $extras
    ): string {
        $baseType=strtolower(trim($type));
        $sqlType=self::normalizeColumnType($baseType);
        $extras=self::normalizeExtras($extras);

        $numeric=preg_match(
            '/^(?:int|integer|bigint|decimal\(\d{1,2},\d{1,2}\))$/',
            $baseType
        )===1;
        $boolean=in_array($baseType,['bool','boolean'],true);
        $integer=in_array($baseType,['int','integer','bigint'],true);
        $temporal=in_array($baseType,['datetime','timestamp'],true);

        if (
            (in_array('unsigned',$extras,true)
                || in_array('zerofill',$extras,true))
            && !$numeric
        ) {
            throw new RuntimeException(
                'UNSIGNED und ZEROFILL sind nur für numerische Felder zulässig.'
            );
        }

        if (in_array('zerofill',$extras,true)) {
            if (!in_array('unsigned',$extras,true)) {
                $extras[]='unsigned';
            }
        }

        if (in_array('auto_increment',$extras,true)) {
            if (!$integer) {
                throw new RuntimeException(
                    'AUTO_INCREMENT ist nur für INT/BIGINT-Felder zulässig.'
                );
            }
            if ($nullable) {
                throw new RuntimeException(
                    'AUTO_INCREMENT-Felder dürfen keine NULL-Werte zulassen.'
                );
            }

            $stmt=$pdo->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema=DATABASE()
                   AND table_name=?
                   AND LOWER(extra) LIKE '%auto_increment%'
                   AND (? IS NULL OR column_name<>?)
                 LIMIT 1"
            );
            $stmt->execute([$table,$currentColumn,$currentColumn]);
            $existing=$stmt->fetchColumn();
            if ($existing !== false) {
                throw new RuntimeException(
                    'Die Tabelle besitzt bereits das AUTO_INCREMENT-Feld „'
                    .(string)$existing.'“.'
                );
            }
        }

        if (
            in_array('on_update_current_timestamp',$extras,true)
            && !$temporal
        ) {
            throw new RuntimeException(
                'ON UPDATE CURRENT_TIMESTAMP ist nur für DATETIME/TIMESTAMP zulässig.'
            );
        }

        $definition=$sqlType;
        if (in_array('unsigned',$extras,true)) {
            $definition.=' UNSIGNED';
        }
        if (in_array('zerofill',$extras,true)) {
            $definition.=' ZEROFILL';
        }
        $definition.=$nullable?' NULL':' NOT NULL';

        $defaultKind=strtolower(trim($defaultKind));
        if (!in_array(
            $defaultKind,
            ['none','null','literal','current_timestamp'],
            true
        )) {
            throw new RuntimeException('Ungültige Vorgabewert-Art.');
        }

        if ($defaultKind === 'null') {
            if (!$nullable) {
                throw new RuntimeException(
                    'DEFAULT NULL ist nur bei Feldern zulässig, die NULL-Werte erlauben.'
                );
            }
            $definition.=' DEFAULT NULL';
        } elseif ($defaultKind === 'literal') {
            $literal=(string)($defaultValue??'');
            if ($numeric) {
                if ($literal === '' || !is_numeric($literal)) {
                    throw new RuntimeException(
                        'Der Vorgabewert muss für diesen numerischen Datentyp eine Zahl sein.'
                    );
                }
                $definition.=' DEFAULT '.$literal;
            } elseif ($boolean) {
                $normalizedBoolean=strtolower(trim($literal));
                if (!in_array($normalizedBoolean,['0','1','false','true'],true)) {
                    throw new RuntimeException(
                        'BOOLEAN-Vorgabewerte müssen 0, 1, false oder true sein.'
                    );
                }
                $definition.=' DEFAULT '.(
                    in_array($normalizedBoolean,['1','true'],true)?'1':'0'
                );
            } else {
                $definition.=' DEFAULT '.$pdo->quote($literal);
            }
        } elseif ($defaultKind === 'current_timestamp') {
            if (!$temporal) {
                throw new RuntimeException(
                    'CURRENT_TIMESTAMP ist als Vorgabewert nur für DATETIME/TIMESTAMP zulässig.'
                );
            }
            $definition.=' DEFAULT CURRENT_TIMESTAMP';
        }

        if (in_array('auto_increment',$extras,true)) {
            if ($defaultKind !== 'none') {
                throw new RuntimeException(
                    'AUTO_INCREMENT kann nicht mit einem eigenen Vorgabewert kombiniert werden.'
                );
            }
            $definition.=' AUTO_INCREMENT';
        }
        if (in_array('on_update_current_timestamp',$extras,true)) {
            $definition.=' ON UPDATE CURRENT_TIMESTAMP';
        }

        return $definition;
    }

    private static function assertUniqueValues(
        PDO $pdo,
        string $table,
        string $column
    ): void {
        $quotedTable=self::quoteIdentifier($table,'mysql');
        $quotedColumn=self::quoteIdentifier($column,'mysql');

        $sql='SELECT '.$quotedColumn.',COUNT(*) AS c'
            .' FROM '.$quotedTable
            .' WHERE '.$quotedColumn.' IS NOT NULL'
            .' GROUP BY '.$quotedColumn
            .' HAVING COUNT(*)>1'
            .' LIMIT 1';

        $row=$pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            throw new RuntimeException(
                'UNIQUE kann nicht angelegt werden, weil im Feld bereits doppelte Werte vorhanden sind.'
            );
        }
    }

    private static function exactColumnDefinition(
        PDO $pdo,
        string $table,
        string $column
    ): string {
        self::assertManagedIdentifier($table,'Tabellenname');
        self::assertManagedIdentifier($column,'Feldname');

        $row=$pdo->query(
            'SHOW CREATE TABLE '.self::quoteIdentifier($table,'mysql')
        )->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException('Die vollständige Tabellendefinition konnte nicht gelesen werden.');
        }

        $create='';
        foreach ($row as $key=>$value) {
            if (stripos((string)$key,'create table') !== false) {
                $create=(string)$value;
                break;
            }
        }
        if ($create === '') {
            $values=array_values($row);
            $create=(string)($values[1]??'');
        }
        if ($create === '') {
            throw new RuntimeException('Die vollständige CREATE-TABLE-Definition ist leer.');
        }

        $pattern='/^\\s*`'.preg_quote($column,'/').'`\\s+(.+?)(?:,)?\\s*$/mi';
        if (preg_match($pattern,$create,$match) !== 1) {
            throw new RuntimeException(
                'Die exakte Definition des Feldes „'.$column.'“ konnte nicht rekonstruiert werden.'
            );
        }
        $definition=trim((string)$match[1]);
        if (str_ends_with($definition,',')) {
            $definition=rtrim(substr($definition,0,-1));
        }
        if ($definition === '') {
            throw new RuntimeException('Die exakte Felddefinition ist leer.');
        }
        return $definition;
    }

    private static function parseColumns(string $specification): array
    {
        $parts=preg_split(
            '/\R+/',
            $specification,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $result=[];
        $seen=[];

        foreach ($parts as $part) {
            $part=trim($part);
            if ($part === '') continue;

            [$name,$type]=array_pad(explode(':',$part,2),2,'varchar(255)');
            $name=trim($name);
            $type=strtolower(trim($type));

            self::assertManagedIdentifier($name,'Spaltenname');
            if ($name === 'id') {
                throw new RuntimeException(
                    'Die Spalte id wird automatisch erzeugt.'
                );
            }
            if (isset($seen[$name])) {
                throw new RuntimeException(
                    'Die Spalte „'.$name.'“ wurde mehrfach angegeben.'
                );
            }
            $seen[$name]=true;

            $sql=self::normalizeColumnType($type);
            $result[]=['name'=>$name,'sql'=>$sql];
        }

        return $result;
    }

    private static function normalizeColumnType(string $type): string
    {
        return match (true) {
            $type === 'text' => 'TEXT',
            $type === 'int', $type === 'integer' => 'INT',
            $type === 'bigint' => 'BIGINT',
            $type === 'date' => 'DATE',
            $type === 'datetime' => 'DATETIME',
            $type === 'timestamp' => 'TIMESTAMP',
            $type === 'bool', $type === 'boolean' => 'TINYINT(1)',
            preg_match('/^varchar\((\d{1,4})\)$/',$type,$m) === 1
                && (int)$m[1] >= 1
                && (int)$m[1] <= 2000
                => 'VARCHAR('.(int)$m[1].')',
            preg_match('/^decimal\((\d{1,2}),(\d{1,2})\)$/',$type,$m) === 1
                && (int)$m[1] >= 1
                && (int)$m[1] <= 65
                && (int)$m[2] >= 0
                && (int)$m[2] <= 30
                && (int)$m[2] <= (int)$m[1]
                => 'DECIMAL('.(int)$m[1].','.(int)$m[2].')',
            default => throw new RuntimeException(
                'Nicht unterstützter Spaltentyp „'.$type.'“. '
                .'Zulässig sind varchar(n), text, int, bigint, decimal(p,s), '
                .'date, datetime, timestamp und boolean.'
            ),
        };
    }

    private static function systemTableExists(PDO $pdo, string $table): bool
    {
        $stmt=$pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables '
            .'WHERE table_schema=DATABASE() AND table_name=?'
        );
        $stmt->execute([$table]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private static function assertManagedIdentifier(
        string $identifier,
        string $label
    ): void {
        if (
            !preg_match(
                '/^[A-Za-z][A-Za-z0-9_]{0,63}$/',
                $identifier
            )
        ) {
            throw new RuntimeException(
                $label.' darf nur mit einem Buchstaben beginnen und '
                .'Buchstaben, Ziffern oder _ enthalten (max. 64 Zeichen).'
            );
        }
    }

    private static function quoteIdentifier(
        string $identifier,
        string $driver
    ): string {
        if ($driver === 'mysql') {
            return '`'.str_replace('`','``',$identifier).'`';
        }
        return '"'.str_replace('"','""',$identifier).'"';
    }
}
