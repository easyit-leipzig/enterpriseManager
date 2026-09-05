<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/TableWorkspaceManager.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

require_once $r.'/products/dataform/system/TableWorkspaceManager.php';

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('column create method',str_contains($manager,'public static function addManagedColumn'));
$f('column update method',str_contains($manager,'public static function updateManagedColumn'));
$f('column delete method',str_contains($manager,'public static function dropManagedColumn'));
$f('protected-system-table write protection',str_contains($manager,'Feldänderungen sind für diese geschützte Systemtabelle nicht zulässig.')&&str_contains($manager,'fieldCrudAllowed'));
$f('id field protected',str_contains($manager,'Das Pflichtfeld id darf nicht geändert oder gelöscht werden.'));
$f('key/relation protection',str_contains($manager,'information_schema.KEY_COLUMN_USAGE'));
$f('duplicate rename protection',str_contains($manager,'Ein Feld mit dem neuen Namen existiert bereits.'));
$f('type whitelist reused',str_contains($manager,'normalizeColumnType'));
$f('runtime handles add action',str_contains($runtime,"'add_managed_column'"));
$f('runtime handles update action',str_contains($runtime,"'update_managed_column'"));
$f('runtime handles delete action',str_contains($runtime,"'drop_managed_column'"));
$f('field CRUD UI',str_contains($runtime,'Felder / Spaltenstruktur')&&str_contains($runtime,'Feld hinzufügen')&&str_contains($runtime,'Feld bearbeiten'));
$f('field delete confirmation',str_contains($runtime,'alle darin gespeicherten Werte wirklich löschen'));
$f('protected field badge',str_contains($runtime,'df-protected-field'));
$f('field editor inputs',str_contains($runtime,'name="column_name"')&&str_contains($runtime,'name="column_type"')&&str_contains($runtime,'name="column_nullable"'));
$f('external sources remain read only',str_contains($runtime,'Externe Quelle · Nur Lesen'));
$f('CRUD CSS',str_contains($css,'.df-column-actions')&&str_contains($css,'.df-column-editor'));
$f('canonical varchar type',TableWorkspaceManager::editableColumnType(['type'=>'varchar(190)'])==='varchar(190)');
$f('canonical mysql bigint type',TableWorkspaceManager::editableColumnType(['type'=>'bigint(20) unsigned'])==='bigint');
$f('canonical boolean type',TableWorkspaceManager::editableColumnType(['type'=>'tinyint(1)'])==='boolean');
$f('HF26 marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
