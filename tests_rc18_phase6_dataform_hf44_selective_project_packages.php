<?php
declare(strict_types=1);

$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/packages.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');

$checks=[];
function hf44_check(string $name,bool $ok): void {
    global $checks;
    $checks[]=[$name,$ok];
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
}

hf44_check('HF44+ package marker',str_contains($page,'DataForm Workspace · HF'));
hf44_check('explicit DataForm configuration option',str_contains($page,'name="include_dataforms"')&&str_contains($page,'DataForms und Feld-/Layout-Konfiguration'));
hf44_check('explicit relations option',str_contains($page,'name="include_relations"')&&str_contains($page,'Beziehungen und Lookups'));
hf44_check('explicit table bindings option',str_contains($page,'name="include_bindings"')&&str_contains($page,'Tabellenbindungen'));
hf44_check('explicit physical schema option',str_contains($page,'name="include_table_schema"')&&str_contains($page,'Basistabellen / physische Tabellenschemata'));
hf44_check('explicit workflow option',str_contains($page,'name="include_workflow"')&&str_contains($page,'Workflows und Regeln'));
hf44_check('explicit modules option',str_contains($page,'name="include_modules"')&&str_contains($page,'Projektmodule'));
hf44_check('explicit example data option',str_contains($page,'name="include_records"')&&str_contains($page,'Datensätze als Beispieldaten'));
hf44_check('DataForms are individually selectable',str_contains($page,'name="dataform_ids[]"')&&str_contains($manager,'public static function exportCatalog('));
hf44_check('base tables are individually selectable',str_contains($page,'name="base_tables[]"')&&str_contains($manager,'isExportableApplicationTable'));
hf44_check('bound and lookup tables become dependencies',str_contains($manager,'dependencyTables(')&&str_contains($manager,"'base_table'"));
hf44_check('physical schemas are packaged',str_contains($manager,"addFromString('schema/'")&&str_contains($manager,'SHOW CREATE TABLE'));
hf44_check('physical example rows are packaged',str_contains($manager,"addFromString('records/'")&&str_contains($manager,'physicalRecords'));
hf44_check('import validates package table DDL',str_contains($manager,'validateCreateTableSql(')&&str_contains($manager,'Mehrere SQL-Anweisungen'));
hf44_check('import creates missing physical tables',str_contains($manager,'$pdo->exec($sql)')&&str_contains($manager,"'schemas'=>['created'=>0,'existing'=>0]"));
hf44_check('import preserves physical sample IDs via row importer',str_contains($manager,'Physical example rows are imported')&&str_contains($manager,'importRows($pdo,$table,$rows,$policy)'));
hf44_check('preview separates configuration schemas and records',str_contains($page,'Physische Tabellenschemata')&&str_contains($page,'Beispieldaten physischer Tabellen'));

$failed=array_filter($checks,static fn(array $row):bool=>!$row[1]);
echo PHP_EOL.count($checks).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);
