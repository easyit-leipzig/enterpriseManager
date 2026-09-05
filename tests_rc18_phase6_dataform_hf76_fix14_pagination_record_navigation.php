<?php
$records=file_get_contents(__DIR__.'/products/dataform/records.php');
$css=file_get_contents(__DIR__.'/assets/css/enterprise.css');
$checks=[
 'FIX14 marker'=>str_contains($records,'HF76-FIX14'),
 'pagination on records not pagecount'=>str_contains($records,'if(($filteredCount??0)>0)'),
 'two pages left'=>str_contains($records,'$page-2'),
 'two pages right'=>str_contains($records,'$page+2'),
 'first DS registry image'=>str_contains($records,"easyit_button_image_html('erster_ds','../../')"),
 'last DS registry image'=>str_contains($records,"easyit_button_image_html('letzter_ds','../../')"),
 'first target'=>str_contains($records,'data-record-target="<?= (int)$firstFilteredRecordId ?>"'),
 'last target'=>str_contains($records,'data-record-target="<?= (int)$lastFilteredRecordId ?>"'),
 'same page no reload'=>str_contains($records,"var row=document.getElementById('record-row-'+target);") && str_contains($records,"if(!row) return; // andere Seite: normale Navigation ist erforderlich") && str_contains($records,'event.preventDefault();'),
 'page links text navigation'=>str_contains($records,'df-pagination-page'),
 'page status'=>str_contains($records,'Seite <?= (int)$page ?> von <?= (int)$pageCount ?>'),
 'page links transparent'=>str_contains($css,'.df-pagination-compact .df-pagination-page') && str_contains($css,'background:transparent!important'),
];
$bad=0; foreach($checks as $n=>$ok){echo ($ok?'PASS':'FAIL')." - $n\n"; if(!$ok)$bad++;} exit($bad?1:0);
