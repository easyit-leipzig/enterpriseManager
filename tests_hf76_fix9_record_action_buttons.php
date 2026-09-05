<?php
declare(strict_types=1);
$root=__DIR__;
$records=file_get_contents($root.'/products/dataform/records.php');
$js=file_get_contents($root.'/assets/js/easyit-button-registry.js');
$checks=[];
$checks['row show explicit anzeigen']=str_contains($records,"easyit_button_attributes('anzeigen','record')");
$checks['row show data crud show']=str_contains($records,'data-crud="show"');
$checks['row show no secondary class']=!str_contains($records,'<a class="button secondary" href="?project=<?= $projectId ?>&amp;dataform=<?= $dataformId ?>&amp;record=<?= $rowId ?>&amp;mode=detail">Anzeigen</a>');
$checks['bulk delete central']=str_contains($records,"easyit_button_attributes('loeschen','bulk')");
$checks['row delete central']=str_contains($records,"easyit_button_attributes('loeschen','record')");
$checks['anchor excludes form action']=str_contains($js,'if(!(el instanceof HTMLAnchorElement))');
$checks['href detail maps anzeigen']=str_contains($js,"if(/mode=detail|(?:[?&])view=/i.test(href))return 'anzeigen';");
$checks['form action is after href']=strpos($js,'if(!(el instanceof HTMLAnchorElement))')>strpos($js,"if(/login/i.test(href))return 'anmelden';");
$pass=0;
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if($ok)$pass++;}
echo "$pass/".count($checks)." PASS\n";
exit($pass===count($checks)?0:1);
