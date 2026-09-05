<?php
declare(strict_types=1);
$root=__DIR__;
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$foundation=(string)file_get_contents($root.'/products/dataform/foundation.php');
$css=(string)file_get_contents($root.'/products/dataform/assets/workspace.css');
$tests=[
    'hf65 badge'=>str_contains($records,'DataForm Workspace · HF65'),
    'dialog feedback variable'=>str_contains($records,'$saveSuccessDialog=\'\';'),
    'native dialog element'=>str_contains($records,'<dialog id="df-save-success-dialog"'),
    'modal invocation'=>str_contains($records,"dialog.showModal()"),
    'ok confirmation'=>str_contains($records,'autofocus>OK</button>'),
    'no toast region'=>!str_contains($records,'df-save-toast-region'),
    'no auto close'=>!str_contains($records,'window.setTimeout(close,2800)'),
    'behavior wording'=>str_contains($foundation,'JavaScript-Dialogfenster nach dem Speichern anzeigen'),
    'behavior help'=>str_contains($foundation,'modales JavaScript-Dialogfenster mit OK-Schaltfläche'),
    'dialog backdrop css'=>str_contains($css,'.df-save-dialog::backdrop'),
    'dialog body css'=>str_contains($css,'.df-save-dialog-body'),
];
$failed=[];
foreach($tests as $name=>$ok){echo ($ok?'PASS':'FAIL').' '.$name.PHP_EOL;if(!$ok)$failed[]=$name;}
if($failed){fwrite(STDERR,'Failed: '.implode(', ',$failed).PHP_EOL);exit(1);}
echo 'HF65: '.count($tests).'/'.count($tests).' PASS'.PHP_EOL;
