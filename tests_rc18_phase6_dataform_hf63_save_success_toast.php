<?php
declare(strict_types=1);
$root=__DIR__;
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$foundation=(string)file_get_contents($root.'/products/dataform/foundation.php');
$css=(string)file_get_contents($root.'/products/dataform/assets/workspace.css');
$tests=[
    'hf63 badge'=>str_contains($records,'DataForm Workspace · HF63'),
    'success feedback variable'=>str_contains($records,'$saveSuccessDialog=\'\';'),
    'update uses dialog'=>str_contains($records,'$saveSuccessDialog=$showSaveSuccess?\'Datensatz gespeichert.\':\'\';'),
    'create uses feedback'=>str_contains($records,"'Datensatz angelegt.'"),
    'settings wording'=>str_contains($foundation,'JavaScript-Dialogfenster nach dem Speichern anzeigen'),
    'validation explanation'=>str_contains($foundation,'Validierungs- und Fehlermeldungen bleiben immer sichtbar'),
    'native dialog'=>str_contains($records,'id="df-save-success-dialog"') && str_contains($records,'<dialog'),
    'show modal'=>str_contains($records,"dialog.showModal()"),
    'dialog ok button'=>str_contains($records,'autofocus>OK</button>'),
    'dialog css'=>str_contains($css,'.df-save-dialog') && str_contains($css,'.df-save-dialog::backdrop'),
    'no record success static notice assignment'=>!str_contains($records,'$success=$showSaveSuccess?\'Der Datensatz wurde gespeichert.\':\'\';'),
];
$failed=[];
foreach($tests as $name=>$ok){
    echo ($ok?'PASS':'FAIL').' '.$name.PHP_EOL;
    if(!$ok)$failed[]=$name;
}
if($failed){fwrite(STDERR,'Failed: '.implode(', ',$failed).PHP_EOL);exit(1);} 
echo 'HF63: '.count($tests).'/'.count($tests).' PASS'.PHP_EOL;
