<?php
declare(strict_types=1);
$root=__DIR__;
$manager=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$page=file_get_contents($root.'/products/dataform/packages.php');
$checks=[
    'HF53 marker'=>str_contains($page,'DataForm Workspace · HF53'),
    'bootstrap method'=>str_contains($manager,'private static function ensureImportMetadataSchema(PDO $pdo): void'),
    'bootstrap dataforms'=>str_contains($manager,'CREATE TABLE IF NOT EXISTS dataforms ('),
    'bootstrap fields'=>str_contains($manager,'CREATE TABLE IF NOT EXISTS dataform_fields ('),
    'bootstrap bindings'=>str_contains($manager,'CREATE TABLE IF NOT EXISTS dataform_table_bindings ('),
    'bootstrap relations'=>str_contains($manager,'CREATE TABLE IF NOT EXISTS dataform_relations ('),
    'bootstrap records'=>str_contains($manager,'CREATE TABLE IF NOT EXISTS dataform_records ('),
    'bootstrap called before import'=>str_contains($manager,'self::ensureImportMetadataSchema($pdo);'),
    'no silent nonempty metadata skip'=>str_contains($manager,'aber die erforderliche Zieltabelle konnte nicht bereitgestellt werden.'),
    'post import DataForm verification'=>str_contains($manager,'DataForm-Metadaten wurden nicht vollständig in das Zielprojekt übernommen.'),
    'catalog refreshed after import'=>str_contains($page,'$catalog=ProjectPackageManager::exportCatalog($pdo);'),
    'success reports imported forms'=>str_contains($page,'DataForms übernommen: '),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
