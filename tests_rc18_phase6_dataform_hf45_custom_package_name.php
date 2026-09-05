<?php
declare(strict_types=1);

$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/packages.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');

$checks=[];
function hf45_check(string $name,bool $ok): void {
    global $checks;
    $checks[]=[$name,$ok];
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
}

hf45_check('HF45 package marker',str_contains($page,'DataForm Workspace · HF45'));
hf45_check('standard package name is visible',str_contains($page,'Standardname – automatisch')&&str_contains($page,'YYYYMMDD_HHMMSS.dfpkg'));
hf45_check('optional custom package name field exists',str_contains($page,'name="package_name"')&&str_contains($page,'Eigener Paketname – optional'));
hf45_check('custom package name is passed to manager',str_contains($page,"'package_name'=>(string)(\$_POST['package_name']??'')"));
hf45_check('package name is normalized',str_contains($manager,'normalizePackageName('));
hf45_check('custom name controls filename base',str_contains($manager,'fileBaseSource')&&str_contains($manager,"opts['package_name']"));
hf45_check('package filename is safely normalized',str_contains($manager,'packageFileBase(')&&str_contains($manager,"preg_replace('/[^a-zA-Z0-9_-]+/'"));
hf45_check('manifest stores display package name',str_contains($manager,"'packageName'=>")&&str_contains($manager,"'packageNameMode'=>")&&str_contains($manager,"?'custom':'standard'"));
hf45_check('project metadata stores package name',substr_count($manager,"'packageName'=>")>=2);
hf45_check('README stores package name',str_contains($manager,'Package name: {$packageName}'));
hf45_check('import preview shows package name',str_contains($page,"['packageName']??")&&str_contains($page,"['project']['name']"));
hf45_check('custom name input has bounded length',str_contains($page,'maxlength="120"'));

$failed=array_filter($checks,static fn(array $row):bool=>!$row[1]);
echo PHP_EOL.count($checks).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);
