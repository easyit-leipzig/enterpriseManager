<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/system/ui/ButtonRegistry.php';
$records=file_get_contents($root.'/products/dataform/records.php');
$checks=[
 'hf66 badge'=>str_contains($records,'DataForm Workspace · HF66'),
 'row save button always rendered'=>str_contains($records,"easyit_button_attributes('speichern','record')") && str_contains($records,'>Speichern</button>'),
 'row save title centralized'=>easyit_button_title('speichern','record')==='Datensatz speichern',
 'adhoc badge remains conditional'=>str_contains($records,"if(\$tableSaveMode==='adhoc')"),
 'adhoc keeps automatic change handler'=>str_contains($records,"addEventListener('change'"),
 'adhoc uses requestSubmit'=>str_contains($records,'form.requestSubmit()'),
 'save button text not hidden by manual branch'=>!str_contains($records,"if(\$tableSaveMode==='manual'): ?><button class=\"button\" type=\"submit\""),
 'adhoc tooltip explains explicit save remains'=>str_contains($records,'Der Speichern-Button bleibt jederzeit verfuegbar.'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;if(!$ok)$failed[]=$name;}
echo 'HF66: '.(count($checks)-count($failed)).'/'.count($checks).' PASS'.PHP_EOL;
exit($failed?1:0);
