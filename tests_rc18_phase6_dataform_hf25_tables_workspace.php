<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/TableWorkspaceManager.php');
$controller=(string)file_get_contents($r.'/products/dataform/system/WorkspaceController.php');
$migration=(string)file_get_contents($r.'/installer/schema/project/004_table_workspace.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('tables placeholder removed',!str_contains($controller,'Die Tabellenverwaltung wird schrittweise'));
$f('tables workspace renderer',str_contains($runtime,'Tabellenkatalog')&&str_contains($runtime,'Neue Projekttabelle'));
$f('source selector',str_contains($runtime,'name="table_source"'));
$f('system source write distinction',str_contains($runtime,'Systemquelle · Schreibzugriff'));
$f('external source read only distinction',str_contains($runtime,'Externe Quelle · Nur Lesen'));
$f('catalog manager',str_contains($manager,'public static function catalog'));
$f('table inspection',str_contains($manager,'public static function inspect'));
$f('mysql catalog',str_contains($manager,'information_schema.tables'));
$f('sqlite catalog',str_contains($manager,'sqlite_master'));
$f('csv catalog',str_contains($manager,"glob(\$path.DIRECTORY_SEPARATOR.'*.csv')"));
$f('oracle catalog',str_contains($manager,'user_tables'));
$f('preview limited',str_contains($manager,'$previewLimit=max(1,min(100,$previewLimit))'));
$f('managed table registry',str_contains($manager,'dataform_managed_tables')&&str_contains($migration,'dataform_managed_tables'));
$f('managed delete protection',str_contains($manager,'Aus Sicherheitsgründen können nur Tabellen gelöscht werden'));
$f('identifier validation',str_contains($manager,"'/^[A-Za-z][A-Za-z0-9_]{0,63}$/'"));
$f('column type whitelist',str_contains($manager,'Nicht unterstützter Spaltentyp'));
$f('tables CSS',str_contains($css,'.df-table-workspace')&&str_contains($css,'.df-table-catalog'));
$f('HF25 marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));

echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($failed===0?0:1);
