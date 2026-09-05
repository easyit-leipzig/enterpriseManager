<?php
declare(strict_types=1);
$base=__DIR__;$records=(string)file_get_contents($base.'/products/dataform/records.php');$crud=(string)file_get_contents($base.'/assets/css/easyit-crud-3d-buttons.css');
require_once $base.'/system/ui/ButtonRegistry.php';$registry=easyit_button_registry();
$checks=[
 'active/normal pointer server image'=>str_contains($records, 'easyit_button_image_html($isActive?\'aktueller_ds\':\'normaler_ds\',\'../../\')'),
 'new pointer server image'=>str_contains($records,"easyit_button_image_html('neuer_ds','../../')"),
 'legacy pointer text removed'=>!str_contains($records,'▶') && !str_contains($records,'<span aria-hidden="true">*</span>'),
 'normal DS image scoped to record navigation'=>(($registry['normaler_ds']['scope']??'')==='record_navigation'),
 'current DS image scoped to record navigation'=>(($registry['aktueller_ds']['scope']??'')==='record_navigation'),
 'new DS image scoped to record navigation'=>(($registry['neuer_ds']['scope']??'')==='record_navigation'),
 'pointer CSS uses dedicated record size'=>str_contains($crud,'--easyit-record-pointer-size'),
 'legacy bulk-delete colour selectors removed'=>!str_contains($crud,'form:has(input[name="action"][value="bulk_delete"])'),
 'pointer stays ordinary button'=>str_contains($records,'type="button" class="df-record-pointer'),
];
$failed=[];foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n";if(!$ok)$failed[]=$name;}exit($failed?1:0);
