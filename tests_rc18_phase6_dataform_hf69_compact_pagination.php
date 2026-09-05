<?php
$records=file_get_contents(__DIR__.'/products/dataform/records.php');
$checks=[
'HF69 marker'=>str_contains($records,'DataForm Workspace · HF69'),
'first id'=>str_contains($records,'$firstFilteredRecordId'),
'last id'=>str_contains($records,'$lastFilteredRecordId'),
'two left'=>str_contains($records,'$page-2'),
'two right'=>str_contains($records,'$page+2'),
'first DS'=>str_contains($records,"easyit_button_image_html('erster_ds','../../')"),
'last DS'=>str_contains($records,"easyit_button_image_html('letzter_ds','../../')"),
'ellipsis'=>str_contains($records,'df-pagination-ellipsis'),
'first active'=>str_contains($records,"'active_record'=>\$firstFilteredRecordId"),
'last active'=>str_contains($records,"'active_record'=>\$lastFilteredRecordId"),
];
$bad=0; foreach($checks as $n=>$ok){echo ($ok?'PASS':'FAIL')." - $n\n"; if(!$ok)$bad++;} exit($bad?1:0);
