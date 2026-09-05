<?php
declare(strict_types=1);
$r=__DIR__;$s=(string)file_get_contents($r.'/setup.php');$a=(string)file_get_contents($r.'/installer/admin.php');$c=[];
$f=function($n,$ok)use(&$c){$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$f('sticky form model',str_contains($s,"'db_host'=>")&&str_contains($s,"\$form['admin_email']"));
$f('sticky products',str_contains($s,"in_array('dataform',\$form['products'],true)"));
$f('sticky environment',str_contains($s,"\$form['environment']==='production'"));
$f('setup password eyes',str_contains($s,'data-password-field')&&str_contains($s,'password-toggle'));
$f('admin password eyes',str_contains($a,'data-password-field')&&str_contains($a,'password-toggle'));
$f('no password session persistence',!str_contains($s,"\$_SESSION['admin_password']"));
$failed=count(array_filter($c,fn($x)=>$x['status']==='FAIL'));
echo json_encode(['hotfix'=>'HF15','status'=>$failed?'FAIL':'PASS','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed?1:0);
