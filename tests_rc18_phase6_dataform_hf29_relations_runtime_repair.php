<?php
declare(strict_types=1);

$r=__DIR__;
$relations=(string)file_get_contents($r.'/products/dataform/relations.php');
$modules=(string)file_get_contents($r.'/products/dataform/modules.php');
$packages=(string)file_get_contents($r.'/products/dataform/packages.php');
$layout=(string)file_get_contents($r.'/system/ui/layout.php');
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');

$checks=[];
$check=function(string $name,bool $ok)use(&$checks):void{
    $checks[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$check('relations no legacy renderer',!str_contains($relations,'render_enterprise_page('));
$check('relations uses current renderer',str_contains($relations,'render_page(['));
$check('relations gets correct application base',str_contains($relations,"'base'=>'../../'"));
$check('relations gets enterprise app navigation',str_contains($relations,"'app_nav'=>true")&&str_contains($relations,"'user'=>\$user"));
$check('relations loads workspace stylesheet',str_contains($relations,'products/dataform/assets/workspace.css'));
$check('modules legacy renderer repaired',!str_contains($modules,'render_enterprise_page(')&&str_contains($modules,'render_page(['));
$check('packages legacy renderer repaired',!str_contains($packages,'render_enterprise_page(')&&str_contains($packages,'render_page(['));
$check('layout supports scoped stylesheets',str_contains($layout,"\$page['styles']")&&str_contains($layout,'$base . $stylesheet'));
$check('HF29 runtime marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));

$allPhp='';
foreach ([$r.'/products',$r.'/app',$r.'/system',$r.'/installer'] as $productionRoot) {
    $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($productionRoot,FilesystemIterator::SKIP_DOTS));
    foreach($iterator as $file){
        if($file->isFile() && strtolower($file->getExtension())==='php'){
            $allPhp.=(string)file_get_contents($file->getPathname());
        }
    }
}
$check('no production PHP still calls legacy renderer',!str_contains($allPhp,'render_enterprise_page('));

$failed=count(array_filter($checks,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8','hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$checks,
    'summary'=>['checks'=>count($checks),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
