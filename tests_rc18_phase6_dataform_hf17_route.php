<?php
declare(strict_types=1);
$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$index=(string)file_get_contents($r.'/products/dataform/index.php');
$ht=(string)file_get_contents($r.'/products/dataform/.htaccess');
$project=(string)file_get_contents($r.'/app/projects/view.php');
$c=[];
$f=function(string $n,bool $ok)use(&$c):void{$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};

$f('unique runtime exists',is_file($r.'/products/dataform/runtime.php'));
$f('runtime marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));
$f('runtime html marker',str_contains($runtime,'data-easyit-runtime="dataform-hf36"'));
$f('runtime header',str_contains($runtime,"X-EasyIT-DataForm-Runtime: HF36"));
$f('runtime uses runtime suffix',str_contains($runtime,"/products/dataform/runtime.php"));
$f('compat index redirects',str_contains($index,"Location: ")&&str_contains($index,"runtime.php"));
$f('apache rewrite bypass',str_contains($ht,'RewriteRule ^index\\.php$ runtime.php'));
$f('project entry points direct to runtime',str_contains($project,'products/dataform/runtime.php?project='));
$f('runtime proof reports runtime file',str_contains((string)file_get_contents($r.'/products/dataform/runtime-proof.php'),'runtime.php SHA-256'));

$failed=count(array_filter($c,fn($x)=>$x['status']==='FAIL'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$failed?'FAIL':'PASS','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed?1:0);
