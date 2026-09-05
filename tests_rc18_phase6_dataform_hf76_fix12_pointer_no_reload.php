<?php
declare(strict_types=1);
$root=__DIR__;
$file=$root.'/products/dataform/records.php';
$src=(string)file_get_contents($file);
$checks=[
    'saved pointer has no location navigation' => !str_contains($src, 'onclick="window.location.href='),
    'old pointerUrl removed' => !str_contains($src, '$pointerUrl=df_query'),
    'saved pointer id emitted' => str_contains($src, 'data-record-pointer-id="<?= $rowId ?>"'),
    'new pointer id emitted' => str_contains($src, 'data-record-pointer-id="new"'),
    'client setCurrentRow exists' => str_contains($src, 'function setCurrentRow(row,focusNewControl)'),
    'pointer click prevents navigation' => str_contains($src, 'event.preventDefault();') && str_contains($src, "event.target.closest('.df-record-pointer')"),
    'current saved pointer uses aktueller_ds' => str_contains($src, "active?'aktueller_ds':'normaler_ds'"),
    'new row keeps neuer_ds' => str_contains($src, "decoratePointer(newRow.querySelector('.df-record-pointer'),'neuer_ds',newActive)"),
    'client current record state stored' => str_contains($src, "table.setAttribute('data-current-record',recordId)") && str_contains($src, 'window.EasyITDataFormCurrentRecord=recordId'),
    'no reload call in current pointer block' => !str_contains(substr($src, strpos($src,'HF76-FIX13:')), 'window.location.href=') && !str_contains(substr($src, strpos($src,'HF76-FIX13:')), 'location.reload('),
];
$passed=0;
foreach($checks as $name=>$ok){ echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL; if($ok)$passed++; }
echo $passed.'/'.count($checks).' PASS'.PHP_EOL;
exit($passed===count($checks)?0:1);
