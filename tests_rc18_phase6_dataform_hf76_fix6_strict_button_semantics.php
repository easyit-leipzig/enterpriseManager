<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/system/ui/ButtonRegistry.php';
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$js=(string)file_get_contents($root.'/assets/js/easyit-button-registry.js');
$css=(string)file_get_contents($root.'/assets/css/easyit-crud-3d-buttons.css');
$checks=[];
$check=function(string $name,bool $ok)use(&$checks){$checks[]=$ok;echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;};
$registry=easyit_button_registry();
$check('DataForm form button uses formular.png',($registry['formular']['image']??'')==='assets/img/formular.png' && str_contains($runtime,"easyit_button_attributes('formular','dataform')"));
$check('DataForm records button uses anzeigen.png',($registry['anzeigen']['image']??'')==='assets/img/anzeigen.png' && str_contains($runtime,"easyit_button_attributes('anzeigen','dataform_records')"));
$check('DataForm records central title is meaningful',easyit_button_title('anzeigen','dataform_records')==='Datensätze anzeigen');
$check('DataForm list no longer uses normaler_ds',!str_contains($runtime,"easyit_button_attributes('normaler_ds','dataform_records')"));
$check('normaler_ds is scoped to record navigation',($registry['normaler_ds']['scope']??'')==='record_navigation');
foreach(['erster_ds','vorheriger_ds','aktueller_ds','naechster_ds','letzter_ds','neuer_ds','normaler_ds'] as $key){
    $check($key.' is record-navigation-only',($registry[$key]['scope']??'')==='record_navigation');
}
$check('records table still uses normaler/current DS pointer',str_contains($records,"\$isActive?'aktueller_ds':'normaler_ds'"));
$check('new record row still uses neuer_ds',str_contains($records,"easyit_button_attributes('neuer_ds')"));
$check('first/last DS navigation remains explicit',str_contains($records,"easyit_button_attributes('erster_ds')") && str_contains($records,"easyit_button_attributes('letzter_ds')"));
$check('JS rejects DS image outside record navigation',str_contains($js,"RECORD_NAV_TYPES.has(explicit) && !isRecordNavigationControl(el)"));
$check('JS strips historical color-state classes',str_contains($js,"classList.remove('primary','secondary','danger','success','warning','error')"));
$check('FIX6 absolute reset exists',str_contains($css,'HF76-FIX6 – ABSOLUTE BUTTONSET RESET'));
$check('FIX6 reset forces transparent background',str_contains($css,'html body a[data-button]') && str_contains($css,'background-color:transparent!important'));
$check('FIX6 reset removes old box shadows',str_contains($css,'box-shadow:none!important'));
if(in_array(false,$checks,true)){exit(1);} echo 'HF76-FIX6 strict button semantics: '.count($checks).'/'.count($checks).' PASS'.PHP_EOL;
