<?php
declare(strict_types=1);
$root=__DIR__;
$file=$root.'/products/dataform/records.php';
$src=file_get_contents($file);
$checks=[
 'inlineCreatedRecordId initialized'=>str_contains($src,'$inlineCreatedRecordId = 0;'),
 'created id captured'=>str_contains($src,'$inlineCreatedRecordId=$recordId;'),
 'created record pinned'=>str_contains($src,'$records[] = $createdRecord;'),
 'created record removed before append'=>str_contains($src,'array_splice($records, $createdIndex, 1);'),
 'last page forced after create'=>str_contains($src,'if ($inlineCreatedRecordId > 0) {') && str_contains($src,'$page = $pageCount;'),
 'active record remains created'=>str_contains($src,'$activeRecordId=$recordId;'),
 'inline values cleared'=>str_contains($src,'$inlineCreateValues=[];'),
 'inline row only last page'=>str_contains($src,'$showInlineCreateRow = $page === $pageCount;'),
 'new row id present'=>str_contains($src,'id="record-new-row"'),
 'new row no delete action'=>!preg_match('/record-new-row[\s\S]{0,2200}data-crud="delete"/', $src),
];
$pass=0;
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if($ok)$pass++;}
echo "$pass/".count($checks)." PASS\n";
exit($pass===count($checks)?0:1);
