<?php
declare(strict_types=1);
$r=__DIR__;
$rt=(string)file_get_contents($r.'/products/dataform/runtime.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$providers=(string)file_get_contents($r.'/products/dataform/assets/default-providers.js');
$c=[];$f=function(string $n,bool $ok)use(&$c):void{$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};

$f('defined mode',str_contains($rt,'Wie definiert [Wert]')&&str_contains($rt,'value="defined"'));
$f('CURRENT_TIMESTAMP mode',str_contains($rt,'CURRENT_TIMESTAMP')&&str_contains($rt,'value="current_timestamp"'));
$f('javascript mode',str_contains($rt,'JavaScript-Funktion')&&str_contains($rt,'value="javascript"'));
$f('default mode persisted',str_contains($rt,"\$configuration['default_mode']"));
$f('fixed default persisted',str_contains($rt,"\$configuration['default_value']"));
$f('provider name persisted',str_contains($rt,"\$configuration['default_js_provider']"));
$f('provider name validation',str_contains($rt,"A-Za-z0-9_$.-"));
$f('timestamp field type guard',str_contains($rt,"CURRENT_TIMESTAMP ist für diesen Feldtyp nicht zulässig"));
$f('server timestamp resolver',str_contains($records,"if(\$mode==='current_timestamp')"));
$f('date timestamp format',str_contains($records,"'date'=>date('Y-m-d')"));
$f('datetime timestamp format',str_contains($records,"'datetime'=>date('Y-m-d\\TH:i')"));
$f('generic timestamp format',str_contains($records,"date('Y-m-d H:i:s')"));
$f('JS only on create',str_contains($records,"data-record-mode")&&str_contains($records,"!=='create'"));
$f('JS registry used',str_contains($records,'easyITDefaultProviders'));
$f('no eval/new Function',!str_contains($rt,'eval(')&&!str_contains($rt,'new Function(')&&!str_contains($records,'eval(')&&!str_contains($records,'new Function('));
$f('provider registry exists',is_file($r.'/products/dataform/assets/default-providers.js'));
$f('built-in uuid',str_contains($providers,'registry.uuid'));
$f('built-in today',str_contains($providers,'registry.today'));
$f('built-in currentIso',str_contains($providers,'registry.currentIso'));
$f('HF21 marker',str_contains($rt,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$failed?'FAIL':'PASS','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed?1:0);
