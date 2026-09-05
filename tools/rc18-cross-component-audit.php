<?php
declare(strict_types=1);
$root=dirname(__DIR__);$checks=[];$add=function($c,$n,$ok,$d='')use(&$checks){$checks[]=['component'=>$c,'check'=>$n,'status'=>$ok?'PASS':'FAIL','detail'=>$d];};
$providers=require $root.'/DataForm5-Core/config/providers.php';$text=implode("\n",array_map(fn($x)=>(string)$x,$providers));
$add('core','Provider registry',count($providers)===48,count($providers).' Provider');
foreach(['LicensingServiceProvider','InstallerServiceProvider','ModuleServiceProvider','TestingServiceProvider','ConsoleServiceProvider'] as $p)$add('core',$p,str_contains($text,$p));
foreach(['DataForm5-Core/config/licensing.php','DataForm5-Core/system/licensing/Core/LicenseManager.php','DataForm5-Core/system/licensing/Providers/LicensingServiceProvider.php','installer/schema/admin/004_licensing.php'] as $f)$add('licensing',$f,is_file($root.'/'.$f));
$product=$root.'/products/dataform';$add('dataform','foundation',is_file($product.'/foundation.php'));$add('dataform','module registry',is_file($product.'/system/ModuleRegistry.php'));$manifests=glob($product.'/modules/*/module.json')?:[];$add('dataform','module manifests',count($manifests)===9,count($manifests).' manifests');
foreach($manifests as $mf){$d=json_decode((string)file_get_contents($mf),true);$add('dataform-module',basename(dirname($mf)),is_array($d)&&isset($d['name']));}
$b=(string)file_get_contents($root.'/system/app/bootstrap.php');foreach(['enterprise_container','enterprise_quality_center','enterprise_module_routes','enterprise_module_dispatcher','enterprise_sync_module_capabilities'] as $fn)$add('bootstrap',$fn,str_contains($b,'function '.$fn.'('));
$add('bootstrap','license schema',str_contains($b,'enterprise_licenses'));
$add('installer','admin schema',count(glob($root.'/installer/schema/admin/*.php')?:[])>0);
$failed=array_filter($checks,fn($x)=>$x['status']==='FAIL');$r=['release'=>'RC1.8','phase'=>'6.2','status'=>$failed===[]?'PASS':'FAIL','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>count($failed)]];
echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($failed===[]?0:1);
