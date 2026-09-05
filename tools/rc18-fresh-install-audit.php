<?php
declare(strict_types=1);$r=dirname(__DIR__);$c=[];$a=function($s,$n,$ok,$d='')use(&$c){$c[]=['stage'=>$s,'check'=>$n,'status'=>$ok?'PASS':'FAIL','detail'=>$d];};
foreach(['installer/local-config.php','installer/database.php','installer/admin.php'] as $f)$a('installer',$f,is_file($r.'/'.$f));
$ad=glob($r.'/installer/schema/admin/*.php')?:[];$pr=glob($r.'/installer/schema/project/*.php')?:[];sort($ad);sort($pr);
$a('database','admin schema sequence',array_map('basename',$ad)===['001_migrations.php','002_identity.php','003_projects_audit.php','004_licensing.php'],implode(', ',array_map('basename',$ad)));
$a('database','project schema sequence',array_map('basename',$pr)===['001_migrations.php','002_dataforms.php','003_data_sources.php','004_table_workspace.php','005_dataform_table_bindings.php','006_dataform_runtime_options.php','007_dataform_search_filter_options.php'],implode(', ',array_map('basename',$pr)));
foreach(array_merge($ad,$pr) as $f){$txt=(string)file_get_contents($f);$a('database-schema',str_replace($r.'/','',$f),str_contains($txt,'<?php')&&strlen(trim($txt))>20,'PHP schema source present');}
$env=$r.'/DataForm5-Core/.env.example';$a('configuration','.env.example',is_file($env));$e=is_file($env)?(string)file_get_contents($env):'';foreach(['ADMIN_DB_HOST','ADMIN_DB_PORT','ADMIN_DB_DATABASE','ADMIN_DB_USERNAME'] as $k)$a('configuration',$k,str_contains($e,$k));
$b=(string)file_get_contents($r.'/system/app/bootstrap.php');foreach(['enterprise_pdo','enterprise_upgrade','enterprise_require_auth'] as $fn)$a('first-run',$fn,str_contains($b,'function '.$fn.'('));foreach(['installed_products','enterprise_licenses','capabilities'] as $x)$a('first-run',$x,str_contains($b,$x));
$a('product','DataForm entry',is_file($r.'/products/dataform/index.php'));$a('product','DataForm foundation',is_file($r.'/products/dataform/foundation.php'));$a('developer','dashboard',is_file($r.'/app/developer/index.php'));$a('documentation','INSTALL.md',is_file($r.'/INSTALL.md'));
$f=array_filter($c,fn($x)=>$x['status']==='FAIL');$o=['release'=>'RC1.8','phase'=>'6.3','status'=>$f===[]?'PASS':'FAIL','mode'=>'static fresh-install acceptance; external DB execution intentionally separate','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>count($f)]];
echo json_encode($o,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($f===[]?0:1);
