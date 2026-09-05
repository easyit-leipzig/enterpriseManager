<?php
declare(strict_types=1);
$root=__DIR__;
$file=$root.'/products/dataform/records.php';
$src=(string)file_get_contents($file);
$checks=[
    'inline create only last page' => str_contains($src, '$showInlineCreateRow = $page === $pageCount;'),
    'toolbar new action marker' => str_contains($src, 'class="button df-new-record-action"'),
    'subnav new action marker' => str_contains($src, 'class="df-new-record-action" data-new-record-action="1"'),
    'client new action handler' => str_contains($src, "document.querySelectorAll('.df-new-record-action[data-new-record-action=\"1\"]')"),
    'new row availability guard' => str_contains($src, "var newRow=document.getElementById('record-new-row');") && str_contains($src, 'if(!newRow) return;'),
    'no reload on visible new row' => str_contains($src, 'event.preventDefault();') && str_contains($src, 'setCurrentRow(newRow,true);'),
    'focus first new control' => str_contains($src, "newRow.querySelector('.df-inline-create-control')"),
    'scroll new row without navigation' => str_contains($src, "newRow.scrollIntoView({block:'nearest',behavior:'smooth'});"),
    'toolbar uses central new image' => str_contains($src, "easyit_button_image_html('neu','../../')"),
    'new record has no delete action' => !preg_match('/id="record-new-row"[\s\S]{0,3000}data-crud="delete"/', $src),
];
$passed=0;
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if($ok)$passed++;}
echo "$passed/".count($checks)." PASS\n";
exit($passed===count($checks)?0:1);
