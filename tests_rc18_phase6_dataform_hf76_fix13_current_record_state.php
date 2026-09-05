<?php
declare(strict_types=1);
$root=__DIR__;
$file=$root.'/products/dataform/records.php';
$s=file_get_contents($file);
$checks=[
 'FIX13 marker'=>str_contains($s,'HF76-FIX13'),
 'no pointer href navigation'=>!str_contains($s,'onclick=\"window.location.href='),
 'replaceState present'=>str_contains($s,'window.history.replaceState'),
 'URL active_record sync'=>str_contains($s,"url.searchParams.set('active_record',recordId)"),
 'new row removes active_record'=>str_contains($s,"url.searchParams.delete('active_record')"),
 'POST form state sync'=>str_contains($s,'input[name="active_record"]'),
 'dynamic hidden active_record'=>str_contains($s,"field.name='active_record'"),
 'current table state'=>str_contains($s,"table.setAttribute('data-current-record',recordId)"),
 'new row remains client-only'=>str_contains($s,"row.id==='record-new-row'?'new':''"),
 'initial state through common routine'=>str_contains($s,'setCurrentRow(initial,false);'),
];
$fail=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$fail[]=$name;}
if($fail){exit(1);} echo count($checks).'/'.count($checks)." PASS\n";
