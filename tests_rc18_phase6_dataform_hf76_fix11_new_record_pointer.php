<?php
declare(strict_types=1);
$root=__DIR__;
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$checks=[];
$checks['existing_pointer_fixed']=str_contains($records,'data-button-fixed="1" data-record-pointer-id="<?= $rowId ?>" aria-pressed');
$checks['existing_pointer_server_image']=str_contains($records,'easyit_button_image_html($isActive?\'aktueller_ds\':\'normaler_ds\',\'../../\')');
$checks['new_pointer_type']=str_contains($records,"easyit_button_attributes('neuer_ds')");
$checks['new_pointer_fixed']=str_contains($records,"class=\"df-record-pointer new\"") && str_contains($records,"easyit_button_image_html('neuer_ds','../../')");
$checks['new_pointer_no_literal_star']=!str_contains($records,'<span aria-hidden="true">*</span>');
$checks['new_updated_no_neu_text']=!str_contains($records,'<span class="muted">neu</span>');
$checks['new_updated_neutral']=str_contains($records,'aria-label="Noch nicht gespeichert" title="Noch nicht gespeichert">—</span>');
$checks['create_button_fixed']=str_contains($records,"easyit_button_attributes('neu','record') ?> data-button-fixed=\"1\"");
$checks['create_button_server_image']=str_contains($records,"easyit_button_image_html('neu','../../') ?>Datensatz anlegen");
$newStart=strpos($records,'<tr class="df-record-new-row" id="record-new-row">');
$newEnd=$newStart===false?false:strpos($records,'</tr>',$newStart);
$newRow=($newStart!==false && $newEnd!==false)?substr($records,$newStart,$newEnd-$newStart):'';
$checks['new_row_no_delete']=!str_contains($newRow,"easyit_button_attributes('loeschen'") && !str_contains($newRow,'delete_record');
$pass=0;
foreach($checks as $name=>$ok){echo ($ok?'PASS ':'FAIL ').$name."\n"; if($ok)$pass++;}
echo "SUMMARY {$pass}/".count($checks)." PASS\n";
exit($pass===count($checks)?0:1);
