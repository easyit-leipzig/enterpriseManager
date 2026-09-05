<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/system/ui/ButtonRegistry.php';
$registry=easyit_button_registry();
$js=(string)file_get_contents($root.'/assets/js/easyit-button-registry.js');
$layout=(string)file_get_contents($root.'/system/ui/layout.php');
$workspace=(string)file_get_contents($root.'/products/dataform/system/WorkspaceLayout.php');
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$checks=[];
$check=function(string $name,bool $ok)use(&$checks){$checks[]=$ok;echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;};
$check('all original image buttons plus publication extensions registered',count($registry)>=56);
$titles=[];
$allTitlesValid=true;
$allImagesValid=true;
foreach($registry as $key=>$def){
    $title=trim((string)($def['title']??''));
    $titles[]=$title;
    if($title==='' || strlen($title)<4){$allTitlesValid=false;}
    if(($def['image']??'')!=='assets/img/'.$key.'.png'){$allImagesValid=false;}
    foreach((array)($def['titles']??[]) as $context=>$contextTitle){
        if(trim((string)$context)==='' || strlen(trim((string)$contextTitle))<4){$allTitlesValid=false;}
    }
}
$check('every registry entry has meaningful central title',$allTitlesValid);
$check('every registry entry points to canonical image',$allImagesValid);
$check('base titles are unique',count($titles)===count(array_unique($titles)));
$check('server attributes use registry title',easyit_button_attributes('formular')==='data-button="formular" title="Formular öffnen" aria-label="Formular öffnen"');
$check('context title is centrally resolved',easyit_button_title('anzeigen','dataform_records')==='Datensätze anzeigen');
$check('context attributes carry context and central title',str_contains(easyit_button_attributes('anzeigen','dataform_records'),'title="Datensätze anzeigen"'));
$check('normaler_ds has no DataForm-list context',easyit_button_title('normaler_ds','dataform_records')==='Datensatz auswählen');
$check('unknown context cannot inject local title',easyit_button_title('neu','irgendein lokaler tooltip')==='Neu anlegen');
$check('registry JSON is generated server-side',str_contains(easyit_button_registry_data_tag(),'easyit-button-registry-data'));
$check('global layout injects registry data before JS',strpos($layout,'easyit_button_registry_data_tag()')<strpos($layout,'easyit-button-registry.js'));
$check('workspace injects registry data before JS',strpos($workspace,'easyit_button_registry_data_tag()')<strpos($workspace,'easyit-button-registry.js'));
$check('runtime injects registry data before JS',strpos($runtime,'easyit_button_registry_data_tag()')<strpos($runtime,'easyit-button-registry.js'));
$check('JS loads central registry payload',str_contains($js,"getElementById('easyit-button-registry-data')"));
$check('JS overwrites title with central title',str_contains($js,"el.setAttribute('title',centralTitle)"));
$check('JS overwrites aria-label with central title',str_contains($js,"el.setAttribute('aria-label',centralTitle)"));
$check('JS supports central context titles',str_contains($js,'contextTitles[context]'));
$check('JS does not contain duplicated PHP registry literal',!str_contains($js,'const REGISTRY={"neu"'));
$check('DataForm new uses central dataform context',str_contains($runtime,"easyit_button_attributes('neu','dataform')"));
$check('DataForm open uses central dataform context',str_contains($runtime,"easyit_button_attributes('formular','dataform')"));
$check('DataForm records uses anzeigen records context',str_contains($runtime,"easyit_button_attributes('anzeigen','dataform_records')"));
$check('DataForm delete uses central dataform context',str_contains($runtime,"easyit_button_attributes('loeschen','dataform')"));
$check('record save uses central record context',str_contains($records,"easyit_button_attributes('speichern','record')"));
$check('record delete uses central record context',str_contains($records,"easyit_button_attributes('loeschen','record')"));
$check('record edit uses central record context',str_contains($records,"easyit_button_attributes('bearbeiten','record')"));
if(in_array(false,$checks,true)){exit(1);} echo 'HF76-FIX4 central button titles: '.count($checks).'/'.count($checks).' PASS'.PHP_EOL;
