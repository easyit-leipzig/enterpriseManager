<?php
$root=__DIR__;$ok=[];$test=function(string $name,bool $value)use(&$ok){$ok[]=$value;echo ($value?'PASS':'FAIL')." - $name\n";};
require_once $root.'/system/ui/ButtonRegistry.php';$registry=easyit_button_registry();
$css=(string)file_get_contents($root.'/assets/css/easyit-crud-3d-buttons.css');$records=(string)file_get_contents($root.'/products/dataform/records.php');
$test('canonical overview exists',is_file($root.'/assets/img/buttonset.png'));
foreach(['neu','bearbeiten','loeschen','speichern','aktueller_ds','neuer_ds','normaler_ds'] as $icon){$test('canonical icon '.$icon,is_file($root.'/assets/img/'.$icon.'.png'));}
$test('HF75 visual skin superseded by FIX7 image-only policy',str_contains($css,'HF76-FIX7'));
$test('new image bound',($registry['neu']['image']??'')==='assets/img/neu.png');
$test('edit image bound',($registry['bearbeiten']['image']??'')==='assets/img/bearbeiten.png');
$test('delete image bound',($registry['loeschen']['image']??'')==='assets/img/loeschen.png');
$test('save image bound',($registry['speichern']['image']??'')==='assets/img/speichern.png');
$test('record current image bound',($registry['aktueller_ds']['image']??'')==='assets/img/aktueller_ds.png');
$test('record new image bound',($registry['neuer_ds']['image']??'')==='assets/img/neuer_ds.png');
$test('record normal image bound',($registry['normaler_ds']['image']??'')==='assets/img/normaler_ds.png');
$test('record save semantic attribute',str_contains($records,'data-crud="save"'));
$test('record delete semantic attribute',str_contains($records,'data-crud="delete"'));
$test('record create semantic attribute',str_contains($records,'data-crud="create"'));
$test('HF75 badge retained',str_contains($records,'DataForm Workspace · HF75'));
exit(in_array(false,$ok,true)?1:0);
