<?php
declare(strict_types=1);
$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/packages.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$checks=[];
function hf46_check(string $name,bool $ok): void {global $checks;$checks[]=[$name,$ok];echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;}
hf46_check('HF46 marker',str_contains($page,'DataForm Workspace · HF46'));
hf46_check('current project name is visibly rendered',str_contains($page,'Aktuelles Projekt')&&str_contains($page,"project['name']"));
hf46_check('custom package name has stable id',str_contains($page,'id="df-custom-package-name"'));
hf46_check('effective package name preview exists',str_contains($page,'Verwendeter Paketname')&&str_contains($page,'df-effective-package-name'));
hf46_check('effective filename preview exists',str_contains($page,'Voraussichtlicher Dateiname')&&str_contains($page,'df-effective-package-file'));
hf46_check('custom package name updates live',str_contains($page,"addEventListener('input',updatePackageName)"));
hf46_check('custom package name persists in browser',str_contains($page,'localStorage.setItem(storageKey')&&str_contains($page,'localStorage.getItem(storageKey)'));
hf46_check('history exposes package name',str_contains($page,'<th>Paketname</th>')&&str_contains($page,"h['package_name']"));
hf46_check('history schema contains package_name',str_contains($manager,"columnExists(\$pdo,'project_package_history','package_name')")&&str_contains($manager,'ADD COLUMN package_name'));
hf46_check('history log stores manifest packageName',str_contains($manager,"manifest['packageName']"));
$failed=array_filter($checks,static fn(array $row):bool=>!$row[1]);
echo PHP_EOL.count($checks).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);
