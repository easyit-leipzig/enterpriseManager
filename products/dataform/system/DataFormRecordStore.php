<?php
declare(strict_types=1);

final class DataFormRecordStore
{
    public static function binding(
        PDO $pdo,
        int $dataformId
    ): ?array {
        $exists=(int)$pdo->query(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema=DATABASE()
               AND table_name='dataform_table_bindings'"
        )->fetchColumn();

        if ($exists!==1) {
            return null;
        }

        $stmt=$pdo->prepare(
            "SELECT *
             FROM dataform_table_bindings
             WHERE dataform_id=?
               AND source_kind='system'
               AND source_id=0
             LIMIT 1"
        );
        $stmt->execute([$dataformId]);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $table=(string)$row['table_name'];
        self::assertIdentifier($table);

        $check=$pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema=DATABASE()
               AND table_name=?"
        );
        $check->execute([$table]);

        if ((int)$check->fetchColumn()!==1) {
            throw new RuntimeException(
                'Die an das DataForm gebundene Projekttabelle „'
                .$table.'“ existiert nicht.'
            );
        }

        return $row;
    }

    public static function isPhysical(
        PDO $pdo,
        int $dataformId
    ): bool {
        return self::binding($pdo,$dataformId)!==null;
    }

    public static function all(
        PDO $pdo,
        int $dataformId
    ): array {
        $binding=self::binding($pdo,$dataformId);
        if ($binding===null) {
            $stmt=$pdo->prepare(
                'SELECT *
                 FROM dataform_records
                 WHERE dataform_id=?
                 ORDER BY id'
            );
            $stmt->execute([$dataformId]);

            $rows=[];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $row['data']=json_decode(
                    (string)$row['data_json'],
                    true
                )?:[];
                $rows[]=$row;
            }
            return $rows;
        }

        $table=(string)$binding['table_name'];
        $rows=$pdo->query(
            'SELECT * FROM '.self::quoteIdentifier($table)
            .' ORDER BY `id`'
        )->fetchAll(PDO::FETCH_ASSOC);
        $fieldMap=self::physicalColumnMap(
            $pdo,
            $dataformId,
            $table
        );

        return array_map(
            static fn(array $row): array =>
                self::normalizePhysicalRow(
                    $row,
                    $fieldMap
                ),
            $rows
        );
    }

    public static function find(
        PDO $pdo,
        int $dataformId,
        int $recordId
    ): ?array {
        if ($recordId<1) {
            return null;
        }

        $binding=self::binding($pdo,$dataformId);
        if ($binding===null) {
            $stmt=$pdo->prepare(
                'SELECT *
                 FROM dataform_records
                 WHERE id=? AND dataform_id=?
                 LIMIT 1'
            );
            $stmt->execute([$recordId,$dataformId]);
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $row['data']=json_decode(
                (string)$row['data_json'],
                true
            )?:[];
            return $row;
        }

        $table=(string)$binding['table_name'];
        $stmt=$pdo->prepare(
            'SELECT * FROM '.self::quoteIdentifier($table)
            .' WHERE `id`=? LIMIT 1'
        );
        $stmt->execute([$recordId]);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $fieldMap=self::physicalColumnMap(
            $pdo,
            $dataformId,
            $table
        );

        return self::normalizePhysicalRow(
            $row,
            $fieldMap
        );
    }

    public static function create(
        PDO $pdo,
        int $dataformId,
        array $data
    ): int {
        $binding=self::binding($pdo,$dataformId);
        if ($binding===null) {
            $stmt=$pdo->prepare(
                'INSERT INTO dataform_records
                 (dataform_id,data_json)
                 VALUES (?,?)'
            );
            $stmt->execute([
                $dataformId,
                json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                    |JSON_UNESCAPED_SLASHES
                ),
            ]);
            return (int)$pdo->lastInsertId();
        }

        $table=(string)$binding['table_name'];
        $columns=self::physicalColumnMap(
            $pdo,
            $dataformId,
            $table
        );

        $names=[];
        $values=[];
        foreach ($columns as $fieldName=>$meta) {
            if (!array_key_exists($fieldName,$data)) {
                continue;
            }
            $names[]=$meta['column'];
            $values[]=self::normalizeWriteValue(
                $data[$fieldName],
                $meta
            );
        }

        if ($names===[]) {
            $pdo->exec(
                'INSERT INTO '.self::quoteIdentifier($table)
                .' () VALUES ()'
            );
        } else {
            $quoted=array_map(
                static fn(string $name): string =>
                    self::quoteIdentifier($name),
                $names
            );
            $placeholders=implode(
                ',',
                array_fill(0,count($names),'?')
            );
            $stmt=$pdo->prepare(
                'INSERT INTO '.self::quoteIdentifier($table)
                .' ('.implode(',',$quoted).')'
                .' VALUES ('.$placeholders.')'
            );
            $stmt->execute($values);
        }

        return (int)$pdo->lastInsertId();
    }

    public static function update(
        PDO $pdo,
        int $dataformId,
        int $recordId,
        array $data
    ): void {
        $binding=self::binding($pdo,$dataformId);
        if ($binding===null) {
            $stmt=$pdo->prepare(
                'UPDATE dataform_records
                 SET data_json=?
                 WHERE id=? AND dataform_id=?'
            );
            $stmt->execute([
                json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                    |JSON_UNESCAPED_SLASHES
                ),
                $recordId,
                $dataformId,
            ]);
            if ($stmt->rowCount()<1) {
                if (self::find($pdo,$dataformId,$recordId)===null) {
                    throw new RuntimeException(
                        'Der Datensatz wurde nicht gefunden.'
                    );
                }
            }
            return;
        }

        if (self::find($pdo,$dataformId,$recordId)===null) {
            throw new RuntimeException(
                'Der Datensatz wurde nicht gefunden.'
            );
        }

        $table=(string)$binding['table_name'];
        $columns=self::physicalColumnMap(
            $pdo,
            $dataformId,
            $table
        );

        $sets=[];
        $values=[];
        foreach ($columns as $fieldName=>$meta) {
            if (!array_key_exists($fieldName,$data)) {
                continue;
            }
            $sets[]=self::quoteIdentifier(
                (string)$meta['column']
            ).'=?';
            $values[]=self::normalizeWriteValue(
                $data[$fieldName],
                $meta
            );
        }

        if ($sets===[]) {
            return;
        }

        $values[]=$recordId;
        $stmt=$pdo->prepare(
            'UPDATE '.self::quoteIdentifier($table)
            .' SET '.implode(',',$sets)
            .' WHERE `id`=?'
        );
        $stmt->execute($values);
    }

    public static function delete(
        PDO $pdo,
        int $dataformId,
        int $recordId
    ): void {
        $binding=self::binding($pdo,$dataformId);

        if ($binding===null) {
            $stmt=$pdo->prepare(
                'DELETE FROM dataform_records
                 WHERE id=? AND dataform_id=?'
            );
            $stmt->execute([$recordId,$dataformId]);
        } else {
            $table=(string)$binding['table_name'];
            $stmt=$pdo->prepare(
                'DELETE FROM '.self::quoteIdentifier($table)
                .' WHERE `id`=?'
            );
            $stmt->execute([$recordId]);
        }

        if ($stmt->rowCount()!==1) {
            throw new RuntimeException(
                'Der Datensatz wurde nicht gefunden.'
            );
        }
    }

    public static function bulkDelete(
        PDO $pdo,
        int $dataformId,
        array $recordIds
    ): int {
        $ids=array_values(array_unique(array_filter(
            array_map('intval',$recordIds),
            static fn(int $id): bool => $id>0
        )));
        if ($ids===[]) {
            return 0;
        }

        $binding=self::binding($pdo,$dataformId);
        $ph=implode(',',array_fill(0,count($ids),'?'));

        if ($binding===null) {
            $stmt=$pdo->prepare(
                'DELETE FROM dataform_records
                 WHERE dataform_id=?
                   AND id IN ('.$ph.')'
            );
            $stmt->execute(
                array_merge([$dataformId],$ids)
            );
        } else {
            $table=(string)$binding['table_name'];
            $stmt=$pdo->prepare(
                'DELETE FROM '.self::quoteIdentifier($table)
                .' WHERE `id` IN ('.$ph.')'
            );
            $stmt->execute($ids);
        }

        return $stmt->rowCount();
    }

    public static function displayCaption(
        PDO $pdo,
        int $dataformId,
        int $recordId,
        ?string $displayFieldName=null
    ): string {
        $record=self::find(
            $pdo,
            $dataformId,
            $recordId
        );
        if ($record===null) {
            return '#'.$recordId;
        }

        if ($displayFieldName!==null) {
            $caption=trim((string)(
                $record['data'][$displayFieldName]??''
            ));
            if ($caption!=='') {
                return $caption;
            }
        }

        return '#'.$recordId;
    }

    private static function physicalColumnMap(
        PDO $pdo,
        int $dataformId,
        string $table
    ): array {
        $stmt=$pdo->prepare(
            'SELECT name,configuration_json
             FROM dataform_fields
             WHERE dataform_id=?
             ORDER BY position,id'
        );
        $stmt->execute([$dataformId]);

        $metadata=[];
        foreach (
            $pdo->query(
                'SHOW FULL COLUMNS FROM '
                .self::quoteIdentifier($table)
            )->fetchAll(PDO::FETCH_ASSOC)
            as $column
        ) {
            $metadata[
                strtolower((string)$column['Field'])
            ]=$column;
        }

        $map=[];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $field) {
            $fieldName=(string)$field['name'];
            $columnName=$fieldName;

            $raw=$field['configuration_json']??null;
            if (is_string($raw) && trim($raw)!=='') {
                $cfg=json_decode($raw,true);
                if (
                    is_array($cfg)
                    && isset($cfg['table_binding'])
                    && is_array($cfg['table_binding'])
                ) {
                    $configured=(string)(
                        $cfg['table_binding']['column']??''
                    );
                    if ($configured!=='') {
                        $columnName=$configured;
                    }
                }
            }

            self::assertIdentifier($columnName);
            $key=strtolower($columnName);
            if (!isset($metadata[$key])) {
                // A designer-only field is allowed but has no physical
                // storage in a schema-bound DataForm.
                continue;
            }

            $column=$metadata[$key];
            $map[$fieldName]=[
                'column'=>$columnName,
                'type'=>strtolower(
                    (string)$column['Type']
                ),
                'nullable'=>strtoupper(
                    (string)$column['Null']
                )==='YES',
                'default'=>$column['Default'],
                'extra'=>strtolower(
                    (string)$column['Extra']
                ),
            ];
        }

        return $map;
    }

    private static function normalizeWriteValue(
        mixed $value,
        array $meta
    ): mixed {
        if (is_bool($value)) {
            $value=$value?'1':'0';
        }
        if ($value===null) {
            return null;
        }

        $string=(string)$value;
        $type=(string)$meta['type'];

        if ($string==='' && !empty($meta['nullable'])) {
            return null;
        }

        if (
            preg_match('/^(?:datetime|timestamp)/',$type)===1
            && preg_match(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2})?$/',
                $string
            )===1
        ) {
            return str_replace('T',' ',$string);
        }

        return $string;
    }

    private static function normalizePhysicalRow(
        array $row,
        array $fieldMap
    ): array {
        $id=(int)($row['id']??0);
        $data=[];

        foreach ($fieldMap as $fieldName=>$meta) {
            $column=(string)$meta['column'];
            $value=$row[$column]??null;

            if ($value===null) {
                $data[$fieldName]='';
            } elseif (
                $value instanceof DateTimeInterface
            ) {
                $data[$fieldName]=$value->format(
                    'Y-m-d H:i:s'
                );
            } elseif (!is_scalar($value)) {
                $data[$fieldName]=(string)json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                    |JSON_UNESCAPED_SLASHES
                );
            } else {
                $data[$fieldName]=(string)$value;
            }
        }

        return [
            'id'=>$id,
            'data_json'=>json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                |JSON_UNESCAPED_SLASHES
            ),
            'data'=>$data,
            'created_at'=>isset($row['created_at'])
                ? (string)$row['created_at']
                : '',
            'updated_at'=>isset($row['updated_at'])
                ? (string)$row['updated_at']
                : '',
            '_physical'=>true,
        ];
    }

    private static function assertIdentifier(
        string $identifier
    ): void {
        if (
            preg_match(
                '/^[A-Za-z][A-Za-z0-9_]{0,63}$/',
                $identifier
            )!==1
        ) {
            throw new RuntimeException(
                'Ungültiger Tabellen- oder Feldname.'
            );
        }
    }

    private static function quoteIdentifier(
        string $identifier
    ): string {
        self::assertIdentifier($identifier);
        return '`'.$identifier.'`';
    }
}
