<?php
$records=file_get_contents(__DIR__.'/products/dataform/records.php');
$checks=[
    'hf74 badge'=>str_contains($records,'DataForm Workspace · HF74'),
    'delete forms generated per record'=>str_contains($records,"df-inline-delete-"),
    'delete form posts delete_record'=>str_contains($records,'name="action" value="delete_record"'),
    'row delete button exists'=>str_contains($records,'df-record-delete-button'),
    'row delete button uses external form'=>str_contains($records,'form="<?= e($inlineDeleteFormId) ?>"'),
    'row delete has danger styling'=>str_contains($records,'button danger df-record-delete-button'),
    'row delete confirms concrete record'=>str_contains($records,'Datensatz #<?= $editFormRecordId ?> wirklich löschen?'),
    'new row remains create only'=>str_contains($records,'df-record-inline-create-actions') && str_contains($records,'>Datensatz anlegen</button>'),
];
$newRowStart=strpos($records,'<tr class="df-record-new-row"');
$newRowEnd=$newRowStart===false?false:strpos($records,'</tr>',$newRowStart);
$newRow=$newRowStart===false||$newRowEnd===false?'':substr($records,$newRowStart,$newRowEnd-$newRowStart);
$checks['new row has no delete button']=$newRow!=='' && !str_contains($newRow,'df-record-delete-button') && !str_contains($newRow,'>Löschen</button>');
$ok=true;
foreach($checks as $name=>$pass){
    echo ($pass?'PASS':'FAIL')." - $name\n";
    $ok=$ok&&$pass;
}
exit($ok?0:1);
