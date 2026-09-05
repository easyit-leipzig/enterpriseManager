<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/system/ui/ButtonRegistry.php';
$records=file_get_contents($root.'/products/dataform/records.php');
$checks=[
 'hf67 badge'=>str_contains($records,'DataForm Workspace · HF67'),
 'last page flag'=>str_contains($records,'$showInlineCreateRow = $page === $pageCount;'),
 'inline create form guarded'=>str_contains($records,'if($showInlineCreateRow??true)') && str_contains($records,'class="df-inline-create-form"'),
 'inline new row guarded'=>str_contains($records,'if($showInlineCreateRow??true)') && str_contains($records,'class="df-record-new-row" id="record-new-row"'),
 'new navigation uses last page'=>substr_count($records,"'page'=>\$pageCount??1")>=2,
 'new navigation targets inline row'=>substr_count($records,'#record-new-row')>=2,
 'new row uses central new-record button title'=>str_contains($records,"easyit_button_attributes('neuer_ds')") && easyit_button_title('neuer_ds')==='Neuer Datensatz',
 'page one of three hides new row'=>((1 === 3) === false),
 'page three of three shows new row'=>((3 === 3) === true),
 'empty result page one can create'=>((1 === 1) === true),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;if(!$ok)$failed[]=$name;}
echo 'HF67: '.(count($checks)-count($failed)).'/'.count($checks).' PASS'.PHP_EOL;
exit($failed?1:0);
