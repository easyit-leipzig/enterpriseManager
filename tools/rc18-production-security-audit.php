<?php
declare(strict_types=1);
$r=dirname(__DIR__);$c=[];$a=function($g,$n,$ok,$d='')use(&$c){$c[]=['group'=>$g,'check'=>$n,'status'=>$ok?'PASS':'FAIL','detail'=>$d];};
$p=$r.'/DataForm5-Core/.env.production.example';$a('config','production template',is_file($p));$e=is_file($p)?(string)file_get_contents($p):'';
foreach(['APP_ENV=production','APP_DEBUG=false','LOG_LEVEL=warning','HEALTH_ENABLED=false','ADMIN_DB_PASSWORD=CHANGE_ME','PROJECT_DB_PASSWORD=CHANGE_ME'] as $x)$a('config',$x,str_contains($e,$x));
$a('secrets','development secret removed',!str_contains($e,'change-this-development-key-before-production-0001'));
$a('secrets','real .env not shipped',!is_file($r.'/DataForm5-Core/.env'));
$b=(string)file_get_contents($r.'/system/app/bootstrap.php');
foreach(['session.use_strict_mode','session.use_only_cookies',"'httponly'=>true","'samesite'=>'Lax'","'secure'=>\$secure"] as $x)$a('session',$x,str_contains($b,$x));
$i=(string)file_get_contents($r.'/installer/local-config.php');
$a('installer','CSRF',str_contains($i,'easyit_csrf')&&str_contains($i,'hash_equals'));
$a('installer','no env overwrite',str_contains($i,'nicht überschrieben'));
$a('installer','0600 env permissions',str_contains($i,'chmod($envPath, 0600)'));
$f=array_filter($c,fn($x)=>$x['status']==='FAIL');
$o=['release'=>'RC1.8','phase'=>'6.6','status'=>$f===[]?'PASS':'FAIL','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>count($f)]];
echo json_encode($o,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($f===[]?0:1);
