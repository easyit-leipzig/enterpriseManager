<?php
declare(strict_types=1);
$base=__DIR__;
$records=(string)file_get_contents($base.'/products/dataform/records.php');
$sortMarker='$sort = (string)($_GET[\'sort\'] ?? \'id\'); // HF59';
$dirMarker='$dir = strtolower((string)($_GET[\'dir\'] ?? \'desc\')) === \'asc\' ? \'asc\' : \'desc\';';
$checks=[
    'hf59 badge'=>str_contains($records,'DataForm Workspace · HF59'),
    'sort initialized before try'=>strpos($records,$sortMarker) !== false && strpos($records,$sortMarker) < strpos($records,'try {'),
    'direction initialized before try'=>str_contains($records,$dirMarker),
    'post runtime recovery'=>str_contains($records,'catch (RuntimeException $postError)'),
    'error remains visible'=>str_contains($records,'$error=$postError->getMessage();'),
    'inline validation stays list'=>str_contains($records,'if ($inlineCreateAttempt) {') && str_contains($records,"\$mode='list';"),
    'inline values retained'=>str_contains($records,'$inlineCreateValues=$inputValues;'),
    'normal record reload after post'=>strpos(substr($records,(int)strpos($records,'catch (RuntimeException $postError)')),'DataFormRecordStore::all(') !== false,
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
