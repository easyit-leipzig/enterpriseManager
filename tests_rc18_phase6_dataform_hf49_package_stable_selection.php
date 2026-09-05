<?php
declare(strict_types=1);
$root=__DIR__;
$pkg=(string)file_get_contents($root.'/products/dataform/packages.php');
$mgr=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$checks=[
 'HF49 badge'=>str_contains($pkg,'DataForm Workspace · HF49'),
 'stable package id in row'=>str_contains($pkg,'data-package-id='),
 'load package id query'=>str_contains($pkg,"\$_GET['load_package']"),
 'URL selection persistence'=>str_contains($pkg,"searchParams.set('load_package',packageId)"),
 'autoload persisted selection'=>str_contains($pkg,'requestedLoadPackageId'),
 'download by package id'=>str_contains($pkg,'download_package_id'),
 'rename posts package id'=>str_contains($pkg,'name="package_id"'),
 'nonfatal package CRUD error'=>str_contains($pkg,'Paket konnte nicht bearbeitet werden:'),
 'nonfatal download error'=>str_contains($pkg,'Paketdatei konnte nicht geöffnet werden:'),
 'manager stable id resolver'=>str_contains($mgr,'resolveStoredPackagePathById'),
 'manager download by id'=>str_contains($mgr,'storedPackageForDownloadById'),
 'manager rename by id'=>str_contains($mgr,'renameStoredPackageById'),
 'manager delete by id'=>str_contains($mgr,'deleteStoredPackageById'),
 'filename mutation comment'=>str_contains($mgr,'Filenames are mutable CRUD presentation data'),
];
$fail=[];
foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if(!$ok)$fail[]=$name;}
exit($fail?1:0);
