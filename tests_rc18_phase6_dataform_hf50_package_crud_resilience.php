<?php
declare(strict_types=1);
$root=__DIR__;
$page=file_get_contents($root.'/products/dataform/packages.php');
$mgr=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$checks=[
    'HF50 marker'=>str_contains($page,'DataForm Workspace · HF50'),
    'explicit delete/rename action'=>str_contains($page,'action="packages.php?project=<?=$projectId?>"'),
    'download filename fallback'=>str_contains($page,'download_package=<?=e(rawurlencode((string)$pkg[\'file_name\']))?>'),
    'delete ID fallback'=>str_contains($page,'ProjectPackageManager::deleteStoredPackageById'),
    'delete filename fallback'=>str_contains($page,'ProjectPackageManager::deleteStoredPackage($pdo,$project,$user,$packageFile)'),
    'missing error classifier'=>str_contains($mgr,'public static function isStoredPackageMissingError'),
    'post redirect get'=>str_contains($page,"header('Location: packages.php?project='.\$projectId);"),
    'flash message'=>str_contains($page,'dataform_package_flash'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
