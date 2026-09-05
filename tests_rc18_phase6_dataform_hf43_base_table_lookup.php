<?php
declare(strict_types=1);

$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/relations.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/RelationManager.php');
$records=(string)file_get_contents($root.'/products/dataform/records.php');

$checks=[];
function hf43_check(string $name,bool $ok): void {
    global $checks;
    $checks[]=[$name,$ok];
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
}

hf43_check('HF43 marker', str_contains($page,'DataForm Workspace · HF43'));
hf43_check('lookup source selector exists',
    str_contains($page,'name="lookup_source_kind"')
    && str_contains($page,'value="base_table"')
    && str_contains($page,'Basistabelle – Referenzdaten direkt aus einer Projekttabelle lesen')
);
hf43_check('base table selector exists',
    str_contains($page,'name="lookup_table"')
    && str_contains($page,'Basistabelle – enthält die auswählbaren Referenzdatensätze')
);
hf43_check('base table key and display selectors exist',
    str_contains($page,'name="lookup_key_column"')
    && str_contains($page,'name="lookup_display_column"')
);
hf43_check('base lookup does not require reference DataForm',
    str_contains($page,'Ein DataForm für diese Tabelle ist nicht erforderlich.')
    && str_contains($page,'targetDataformWrap.hidden=baseMode')
);
hf43_check('create base-table n:1 is persisted',
    str_contains($page,'RelationManager::createManyToOneBaseTable(')
    && str_contains($manager,'public static function createManyToOneBaseTable(')
);
hf43_check('update base-table n:1 is persisted',
    str_contains($page,'RelationManager::updateManyToOneBaseTable(')
    && str_contains($manager,'public static function updateManyToOneBaseTable(')
);
hf43_check('lookup config stores physical table metadata',
    str_contains($manager,"'lookup_source_kind'=>'base_table'")
    && str_contains($manager,"'key_column'=>")
    && str_contains($manager,"'display_column'=>")
);
hf43_check('base tables are discovered from project database',
    str_contains($manager,'public static function selectableBaseTables(')
    && str_contains($manager,'information_schema.TABLES')
);
hf43_check('base table lookup health validates schema',
    str_contains($manager,'validateBaseTableLookupTarget(')
    && str_contains($manager,'Die Lookup-Basistabelle')
);
hf43_check('runtime loads base table options',
    str_contains($records,'RelationManager::baseTableLookupOptions(')
    && str_contains($manager,'public static function baseTableLookupOptions(')
);
hf43_check('runtime validates base table selection',
    str_contains($records,'RelationManager::baseTableLookupValueExists(')
    && str_contains($manager,'public static function baseTableLookupValueExists(')
);
hf43_check('runtime resolves base table captions',
    str_contains($records,'RelationManager::baseTableLookupCaption(')
    && str_contains($manager,'public static function baseTableLookupCaption(')
);
hf43_check('relation list identifies base table target',
    str_contains($page,"'Basistabelle → '")
    && str_contains($page,'Lookup-Quelle: Basistabelle')
);
hf43_check('legacy DataForm lookup remains available',
    str_contains($page,'DataForm – Referenzdaten werden über ein vorhandenes DataForm gelesen')
    && str_contains($page,'RelationManager::createManyToOne(')
);

$failed=array_filter($checks,static fn(array $row):bool=>!$row[1]);
echo PHP_EOL.count($checks).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);
