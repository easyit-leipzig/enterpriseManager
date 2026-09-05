<?php
declare(strict_types=1);
$base=__DIR__;
$records=(string)file_get_contents($base.'/products/dataform/records.php');
$css=(string)file_get_contents($base.'/products/dataform/assets/workspace.css');
$checks=[
    'inline create helper'=>str_contains($records,'function df_record_inline_create_control('),
    'dedicated inline form'=>str_contains($records,'class="df-inline-create-form"'),
    'inline save action'=>str_contains($records,'name="inline_create" value="1"'),
    'new row stays inside records table'=>str_contains($records,'<tr class="df-record-new-row" id="record-new-row">'),
    'new row has editable cells'=>str_contains($records,'df-record-inline-create-cell'),
    'new row renders field controls'=>str_contains($records,'df_record_inline_create_control($pdo,$field'),
    'new row submit button'=>str_contains($records,'>Datensatz anlegen</button>'),
    'old standalone new-record link row removed'=>!str_contains($records,'<a href="?project=<?= $projectId ?>&amp;dataform=<?= $dataformId ?>&amp;mode=create">Neuen Datensatz anlegen</a></td></tr>'),
    'new pointer selects inline row without reload'=>str_contains($records,'data-record-pointer-id="new"') && str_contains($records,"setCurrentRow(row,row && row.id==='record-new-row')"),
    'inline create returns to list'=>str_contains($records,'$inlineCreateAttempt') && str_contains($records,"\$mode='list';"),
    'created record becomes active'=>str_contains($records,'$activeRecordId=$recordId;'),
    'lookup options in inline row'=>str_contains($records,'$parentRecordOptionsByLookupFieldId[$fieldId]??[]'),
    'inline controls css'=>str_contains($css,'.df-record-new-row .df-inline-create-control'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
