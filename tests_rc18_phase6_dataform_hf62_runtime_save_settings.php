<?php
declare(strict_types=1);
$root=__DIR__;
$foundation=file_get_contents($root.'/products/dataform/foundation.php');
$records=file_get_contents($root.'/products/dataform/records.php');
$manager=file_get_contents($root.'/products/dataform/system/DataFormManager.php');
$package=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$schema=file_get_contents($root.'/installer/schema/project/002_dataforms.php');
$checks=[
 'foundation runtime action'=>str_contains($foundation,"save_runtime_settings"),
 'foundation manual option'=>str_contains($foundation,"value=\"manual\""),
 'foundation adhoc option'=>str_contains($foundation,"value=\"adhoc\""),
 'foundation success toggle'=>str_contains($foundation,"show_save_success"),
 'manager table_save_mode schema'=>str_contains($manager,"table_save_mode VARCHAR(20) NOT NULL DEFAULT 'manual'"),
 'manager success schema'=>str_contains($manager,"show_save_success TINYINT(1) NOT NULL DEFAULT 1"),
 'records reads mode'=>str_contains($records,"\$tableSaveMode='manual'"),
 'records auto change handler'=>str_contains($records,"addEventListener('change'"),
 'records request submit'=>str_contains($records,"form.requestSubmit()"),
 'records save button remains available'=>str_contains($records,'>Speichern</button>'),
 'records success toggle used'=>str_contains($records,"\$saveSuccessDialog=\$showSaveSuccess?'Datensatz gespeichert.':'';"),
 'package import schema mode'=>str_contains($package,"table_save_mode VARCHAR(20) NOT NULL DEFAULT 'manual'"),
 'installer schema mode'=>str_contains($schema,"table_save_mode VARCHAR(20) NOT NULL DEFAULT 'manual'"),
 'installer schema success'=>str_contains($schema,"show_save_success TINYINT(1) NOT NULL DEFAULT 1"),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;if(!$ok)$failed[]=$name;}
exit($failed?1:0);
