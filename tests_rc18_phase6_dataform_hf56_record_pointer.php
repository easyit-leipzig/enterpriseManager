<?php
declare(strict_types=1);
$base=__DIR__;
$records=(string)file_get_contents($base.'/products/dataform/records.php');
$css=(string)file_get_contents($base.'/products/dataform/assets/workspace.css');
$checks=[
    'active_record parsed'=>str_contains($records,"active_record'] ?? 0"),
    'active_record preserved'=>str_contains($records,"'per_page','active_record'"),
    'first visible record becomes active'=>str_contains($records, '$activeRecordId=(int)$records[0][\'id\'];'),
    'pointer header'=>str_contains($records,'df-record-pointer-head'),
    'pointer button'=>str_contains($records,'class="df-record-pointer <?= $isActive'),
    'active pointer uses registry image'=>str_contains($records, 'easyit_button_image_html($isActive?\'aktueller_ds\':\'normaler_ds\',\'../../\')'),
    'normal pointer uses registry type'=>str_contains($records, 'easyit_button_attributes($isActive?\'aktueller_ds\':\'normaler_ds\')'),
    'new pointer uses registry image'=>str_contains($records,"easyit_button_image_html('neuer_ds','../../')"),
    'new row'=>str_contains($records,'df-record-new-row'),
    'active row'=>str_contains($records,'df-record-active-row'),
    'pointer independent of bulk checkbox'=>str_contains($records,'type="button" class="df-record-pointer'),
    'pointer css'=>str_contains($css,'.df-record-pointer{'),
    'active css'=>str_contains($css,'.df-record-pointer.active'),
    'new css'=>str_contains($css,'.df-record-pointer.new'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
