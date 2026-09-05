<?php
declare(strict_types=1);

final class CsvDatabase
{
    private const DELIMITER = '|';
    private const ENCLOSURE = '"';
    private const ESCAPE = '\\';
    private const RELATION_FILE = '_relations.json';

    private string $databasePath;

    public function __construct(string $basePath, string $databaseName)
    {
        $basePath = rtrim(trim($basePath), DIRECTORY_SEPARATOR);
        if ($basePath === '') throw new InvalidArgumentException('Der Basispfad darf nicht leer sein.');
        $this->assertValidIdentifier($databaseName, 'Datenbankname');
        $this->databasePath = $basePath . DIRECTORY_SEPARATOR . $databaseName;
        if (!is_dir($this->databasePath) && !mkdir($this->databasePath, 0775, true) && !is_dir($this->databasePath)) {
            throw new RuntimeException('Datenbankordner konnte nicht erstellt werden.');
        }
        if (!is_file($this->relationPath())) $this->writeRelations([]);
    }

    public function createTable(string $table, array $columns): void
    {
        $path = $this->tablePath($table);
        if (is_file($path)) throw new RuntimeException("Tabelle \"$table\" existiert bereits.");
        $columns = $this->normalizeColumns($columns);
        $h = $this->openFile($path, 'x+b');
        try { $this->lock($h, LOCK_EX); $this->writeCsvRow($h, $columns); fflush($h); }
        finally { $this->unlockAndClose($h); }
    }

    public function tableExists(string $table): bool { return is_file($this->tablePath($table)); }
    public function columns(string $table): array { return $this->readTable($table)['columns']; }
    public function all(string $table): array { return $this->readTable($table)['records']; }

    public function find(string $table, int $id): ?array
    {
        $this->assertPositiveId($id);
        foreach ($this->all($table) as $row) if ((int)$row['id'] === $id) return $row;
        return null;
    }

    public function where(string $table, array $criteria): array
    {
        return array_values(array_filter($this->all($table), function(array $row) use ($criteria): bool {
            foreach ($criteria as $column => $value) {
                if (!array_key_exists($column, $row) || $row[$column] !== $this->scalarToString($value)) return false;
            }
            return true;
        }));
    }

    public function insert(string $table, array $data): int
    {
        $path = $this->requireTable($table); $h = $this->openFile($path, 'c+b');
        try {
            $this->lock($h, LOCK_EX); $state = $this->readAllFromHandle($h);
            unset($data['id']); $this->assertKnownColumns($state['columns'], $data); $this->validateForeignKeys($table, $data);
            $id = $this->nextId($state['records']); $row = ['id' => (string)$id];
            foreach ($state['columns'] as $column) if ($column !== 'id') $row[$column] = $this->scalarToString($data[$column] ?? null);
            $state['records'][] = $row; $this->rewriteHandle($h, $state['columns'], $state['records']); return $id;
        } finally { $this->unlockAndClose($h); }
    }

    public function update(string $table, int $id, array $data): bool
    {
        $this->assertPositiveId($id); unset($data['id']); if ($data === []) return false;
        $path = $this->requireTable($table); $h = $this->openFile($path, 'c+b');
        try {
            $this->lock($h, LOCK_EX); $state = $this->readAllFromHandle($h);
            $this->assertKnownColumns($state['columns'], $data); $this->validateForeignKeys($table, $data); $updated = false;
            foreach ($state['records'] as &$row) if ((int)$row['id'] === $id) { foreach ($data as $k=>$v) $row[$k]=$this->scalarToString($v); $updated=true; break; }
            unset($row); if ($updated) $this->rewriteHandle($h, $state['columns'], $state['records']); return $updated;
        } finally { $this->unlockAndClose($h); }
    }

    public function delete(string $table, int $id): bool
    {
        $this->assertPositiveId($id); if ($this->find($table, $id) === null) return false;
        $this->applyDeleteRules($table, $id); return $this->deleteDirect($table, $id);
    }

    public function createOneToManyRelation(string $parentTable, string $childTable, string $foreignKey, string $onDelete='RESTRICT'): void
    {
        $this->requireTable($parentTable); $this->requireTable($childTable);
        if (!in_array($foreignKey, $this->columns($childTable), true)) throw new InvalidArgumentException('Fremdschlüsselspalte fehlt.');
        $onDelete = $this->normalizeDeleteRule($onDelete); $relations=$this->readRelations();
        foreach ($relations as $r) if (($r['type']??'')==='one_to_many' && $r['parent_table']===$parentTable && $r['child_table']===$childTable && $r['foreign_key']===$foreignKey) throw new RuntimeException('Beziehung existiert bereits.');
        $relations[]=['type'=>'one_to_many','parent_table'=>$parentTable,'parent_key'=>'id','child_table'=>$childTable,'foreign_key'=>$foreignKey,'on_delete'=>$onDelete];
        $this->writeRelations($relations);
    }

    public function createManyToManyRelation(string $leftTable, string $rightTable, ?string $pivotTable=null, ?string $leftKey=null, ?string $rightKey=null, string $onDelete='CASCADE'): void
    {
        $this->requireTable($leftTable); $this->requireTable($rightTable);
        $pivotTable ??= $leftTable . '_' . $rightTable;
        $leftKey ??= $leftTable . '_id'; $rightKey ??= $rightTable . '_id';
        foreach ([$pivotTable=>$pivotTable,$leftKey=>$leftKey,$rightKey=>$rightKey] as $id) $this->assertValidIdentifier($id, 'Bezeichner');
        if ($leftKey === $rightKey) throw new InvalidArgumentException('Pivot-Fremdschlüssel müssen verschieden sein.');
        $onDelete = $this->normalizeDeleteRule($onDelete);
        if (!$this->tableExists($pivotTable)) $this->createTable($pivotTable, [$leftKey, $rightKey]);
        else {
            $cols=$this->columns($pivotTable);
            if (!in_array($leftKey,$cols,true)||!in_array($rightKey,$cols,true)) throw new RuntimeException('Vorhandene Pivot-Tabelle besitzt nicht die erwarteten Spalten.');
        }
        $relations=$this->readRelations();
        foreach ($relations as $r) if (($r['type']??'')==='many_to_many' && $r['left_table']===$leftTable && $r['right_table']===$rightTable && $r['pivot_table']===$pivotTable) throw new RuntimeException('n:m-Beziehung existiert bereits.');
        $relations[]=['type'=>'many_to_many','left_table'=>$leftTable,'left_key'=>$leftKey,'right_table'=>$rightTable,'right_key'=>$rightKey,'pivot_table'=>$pivotTable,'on_delete'=>$onDelete];
        $this->writeRelations($relations);
    }

    public function attach(string $leftTable, int $leftId, string $rightTable, int $rightId, ?string $pivotTable=null): int
    {
        $r=$this->manyToManyRelation($leftTable,$rightTable,$pivotTable); $this->assertPositiveId($leftId); $this->assertPositiveId($rightId);
        if ($this->find($r['left_table'],$leftId)===null || $this->find($r['right_table'],$rightId)===null) throw new RuntimeException('Zu verknüpfender Datensatz existiert nicht.');
        $existing=$this->where($r['pivot_table'],[$r['left_key']=>$leftId,$r['right_key']=>$rightId]);
        if ($existing!==[]) return (int)$existing[0]['id'];
        return $this->insert($r['pivot_table'],[$r['left_key']=>$leftId,$r['right_key']=>$rightId]);
    }

    public function detach(string $leftTable, int $leftId, string $rightTable, int $rightId, ?string $pivotTable=null): bool
    {
        $r=$this->manyToManyRelation($leftTable,$rightTable,$pivotTable); $rows=$this->where($r['pivot_table'],[$r['left_key']=>$leftId,$r['right_key']=>$rightId]); $changed=false;
        foreach ($rows as $row) $changed=$this->deleteDirect($r['pivot_table'],(int)$row['id'])||$changed;
        return $changed;
    }

    public function sync(string $leftTable, int $leftId, string $rightTable, array $rightIds, ?string $pivotTable=null): void
    {
        $r=$this->manyToManyRelation($leftTable,$rightTable,$pivotTable); $this->assertPositiveId($leftId);
        $wanted=array_values(array_unique(array_map('intval',$rightIds))); foreach ($wanted as $id) $this->assertPositiveId($id);
        $current=$this->where($r['pivot_table'],[$r['left_key']=>$leftId]); $currentIds=array_map(fn($x)=>(int)$x[$r['right_key']],$current);
        foreach (array_diff($currentIds,$wanted) as $id) $this->detach($leftTable,$leftId,$rightTable,$id,$r['pivot_table']);
        foreach (array_diff($wanted,$currentIds) as $id) $this->attach($leftTable,$leftId,$rightTable,$id,$r['pivot_table']);
    }

    public function related(string $sourceTable, int $sourceId, string $targetTable, ?string $pivotTable=null): array
    {
        $r=$this->manyToManyRelation($sourceTable,$targetTable,$pivotTable); $reverse=$r['left_table']!==$sourceTable;
        $sourceKey=$reverse?$r['right_key']:$r['left_key']; $targetKey=$reverse?$r['left_key']:$r['right_key'];
        $rows=$this->where($r['pivot_table'],[$sourceKey=>$sourceId]); $result=[];
        foreach ($rows as $pivot) { $target=$this->find($targetTable,(int)$pivot[$targetKey]); if ($target!==null) $result[]=$target; }
        return $result;
    }

    public function withRelated(string $sourceTable, string $targetTable, string $resultKey='related', ?string $pivotTable=null): array
    {
        $out=[]; foreach ($this->all($sourceTable) as $row) { $row[$resultKey]=$this->related($sourceTable,(int)$row['id'],$targetTable,$pivotTable); $out[]=$row; } return $out;
    }

    public function relations(): array { return $this->readRelations(); }
    public function getDatabasePath(): string { return $this->databasePath; }

    private function manyToManyRelation(string $a,string $b,?string $pivot): array
    {
        foreach ($this->readRelations() as $r) {
            if (($r['type']??'')!=='many_to_many') continue;
            $tables=($r['left_table']===$a&&$r['right_table']===$b)||($r['left_table']===$b&&$r['right_table']===$a);
            if ($tables && ($pivot===null||$r['pivot_table']===$pivot)) return $r;
        }
        throw new RuntimeException('Die angeforderte n:m-Beziehung ist nicht definiert.');
    }

    private function validateForeignKeys(string $table,array $data): void
    {
        foreach ($this->readRelations() as $r) {
            if (($r['type']??'')==='one_to_many' && $r['child_table']===$table) $this->validateFkValue($data,$r['foreign_key'],$r['parent_table']);
            if (($r['type']??'')==='many_to_many' && $r['pivot_table']===$table) {
                $this->validateFkValue($data,$r['left_key'],$r['left_table']); $this->validateFkValue($data,$r['right_key'],$r['right_table']);
            }
        }
    }

    private function validateFkValue(array $data,string $key,string $parentTable): void
    {
        if (!array_key_exists($key,$data)) return; $value=$this->scalarToString($data[$key]); if ($value==='') return;
        if (!ctype_digit($value)||(int)$value<1) throw new InvalidArgumentException("Fremdschlüssel $key muss positive ID sein.");
        if ($this->find($parentTable,(int)$value)===null) throw new RuntimeException("Fremdschlüsselverletzung für $key.");
    }

    private function applyDeleteRules(string $table,int $id): void
    {
        foreach ($this->readRelations() as $r) {
            if (($r['type']??'')==='one_to_many' && $r['parent_table']===$table) {
                $children=$this->where($r['child_table'],[$r['foreign_key']=>$id]); if ($children===[]) continue;
                if ($r['on_delete']==='RESTRICT') throw new RuntimeException('Löschen wegen abhängiger Datensätze nicht möglich.');
                foreach ($children as $child) $r['on_delete']==='CASCADE' ? $this->delete($r['child_table'],(int)$child['id']) : $this->update($r['child_table'],(int)$child['id'],[$r['foreign_key']=>null]);
            }
            if (($r['type']??'')==='many_to_many') {
                $key=null; if ($r['left_table']===$table) $key=$r['left_key']; elseif ($r['right_table']===$table) $key=$r['right_key']; else continue;
                $links=$this->where($r['pivot_table'],[$key=>$id]); if ($links===[]) continue;
                if ($r['on_delete']==='RESTRICT') throw new RuntimeException('Löschen wegen bestehender n:m-Zuordnungen nicht möglich.');
                foreach ($links as $link) $this->deleteDirect($r['pivot_table'],(int)$link['id']);
            }
        }
    }

    private function deleteDirect(string $table,int $id): bool
    {
        $path=$this->requireTable($table); $h=$this->openFile($path,'c+b');
        try { $this->lock($h,LOCK_EX); $s=$this->readAllFromHandle($h); $n=count($s['records']); $s['records']=array_values(array_filter($s['records'],fn($r)=>(int)$r['id']!==$id)); if(count($s['records'])===$n)return false; $this->rewriteHandle($h,$s['columns'],$s['records']); return true; }
        finally { $this->unlockAndClose($h); }
    }

    private function readTable(string $table): array { $h=$this->openFile($this->requireTable($table),'rb'); try{$this->lock($h,LOCK_SH);return $this->readAllFromHandle($h);}finally{$this->unlockAndClose($h);} }
    private function readAllFromHandle($h): array { rewind($h); $header=fgetcsv($h,0,self::DELIMITER,self::ENCLOSURE,self::ESCAPE); if(!$header||$header[0]!=='id')throw new RuntimeException('Ungültige CSV-Kopfzeile.'); $records=[]; while(($row=fgetcsv($h,0,self::DELIMITER,self::ENCLOSURE,self::ESCAPE))!==false){if($row===[null]||$row===[])continue;$row=array_slice(array_pad($row,count($header),''),0,count($header));$rec=array_combine($header,array_map('strval',$row));if(!is_array($rec)||!ctype_digit($rec['id']))throw new RuntimeException('Ungültige ID.');$records[]=$rec;} return ['columns'=>$header,'records'=>$records]; }
    private function rewriteHandle($h,array $columns,array $records): void { rewind($h); ftruncate($h,0); $this->writeCsvRow($h,$columns); foreach($records as $r){$row=[];foreach($columns as $c)$row[]=$r[$c]??'';$this->writeCsvRow($h,$row);}fflush($h); }
    private function writeCsvRow($h,array $row): bool { return fputcsv($h,$row,self::DELIMITER,self::ENCLOSURE,self::ESCAPE,"\n")!==false; }
    private function nextId(array $rows): int { $max=0;foreach($rows as $r)$max=max($max,(int)$r['id']);return $max+1; }
    private function normalizeColumns(array $columns): array { $out=['id'];foreach($columns as $c){if(!is_string($c))throw new InvalidArgumentException('Spaltenname muss Text sein.');$c=trim($c);$this->assertValidIdentifier($c,'Spaltenname');if($c!=='id'&&!in_array($c,$out,true))$out[]=$c;}return $out; }
    private function assertKnownColumns(array $columns,array $data): void { foreach(array_keys($data) as $c)if(!in_array($c,$columns,true))throw new InvalidArgumentException("Unbekannte Spalte $c."); }
    private function scalarToString(mixed $v): string { if($v===null)return '';if(is_bool($v))return $v?'1':'0';if(!is_scalar($v))throw new InvalidArgumentException('Werte müssen skalar oder null sein.');return (string)$v; }
    private function normalizeDeleteRule(string $rule): string { $rule=strtoupper(trim($rule));if(!in_array($rule,['RESTRICT','CASCADE','SET_NULL'],true))throw new InvalidArgumentException('Ungültige Löschregel.');return $rule; }
    private function assertPositiveId(int $id): void { if($id<1)throw new InvalidArgumentException('ID muss größer als 0 sein.'); }
    private function assertValidIdentifier(string $id,string $label): void { if($id===''||!preg_match('/^[A-Za-z_][A-Za-z0-9_-]*$/',$id))throw new InvalidArgumentException("$label ist ungültig: $id"); }
    private function tablePath(string $table): string { $this->assertValidIdentifier($table,'Tabellenname');return $this->databasePath.DIRECTORY_SEPARATOR.$table.'.csv'; }
    private function requireTable(string $table): string { $p=$this->tablePath($table);if(!is_file($p))throw new RuntimeException("Tabelle $table existiert nicht.");return $p; }
    private function relationPath(): string { return $this->databasePath.DIRECTORY_SEPARATOR.self::RELATION_FILE; }
    private function readRelations(): array { $json=file_get_contents($this->relationPath());if($json===false)return []; $v=json_decode($json,true,512,JSON_THROW_ON_ERROR);return is_array($v)?$v:[]; }
    private function writeRelations(array $r): void { $json=json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);if(file_put_contents($this->relationPath(),$json.PHP_EOL,LOCK_EX)===false)throw new RuntimeException('Relationen konnten nicht gespeichert werden.'); }
    private function openFile(string $path,string $mode){$h=fopen($path,$mode);if($h===false)throw new RuntimeException("Datei konnte nicht geöffnet werden: $path");return $h;}
    private function lock($h,int $op): void { if(!flock($h,$op))throw new RuntimeException('Dateisperre fehlgeschlagen.'); }
    private function unlockAndClose($h): void { flock($h,LOCK_UN);fclose($h); }
}
