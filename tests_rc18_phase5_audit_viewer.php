<?php
declare(strict_types=1);
$r=__DIR__;$checks=[];$c=function(string $n,bool $ok)use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$a=(string)file_get_contents($r.'/app/security/audit.php');$l=(string)file_get_contents($r.'/system/ui/layout.php');$d=(string)file_get_contents($r.'/app/dashboard.php');
$c('audit viewer exists',is_file($r.'/app/security/audit.php'));
$c('guarded by permissions.manage',str_contains($a,"enterprise_require_capability(\$user,'permissions.manage')"));
$c('actor join',str_contains($a,'LEFT JOIN users u ON u.id=a.user_id'));
$c('filters',str_contains($a,'name="action"')&&str_contains($a,'name="actor"')&&str_contains($a,'name="object_type"')&&str_contains($a,'name="date_from"')&&str_contains($a,'name="date_to"'));
$c('pagination',str_contains($a,'ORDER BY a.id DESC LIMIT'));
$c('password redaction',str_contains($a,"'password_hash'")&&str_contains($a,"'[REDACTED]'"));
$c('secret/token redaction',str_contains($a,"'secret'")&&str_contains($a,"'token'"));
$c('global navigation',str_contains($l,"'audit' => ['Audit-Protokoll'"));
$c('dashboard entry',str_contains($d,'security/audit.php'));
$f=count(array_filter($checks,fn($x)=>$x['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF10','status'=>$f?'FAIL':'PASS','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>$f]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($f?1:0);
