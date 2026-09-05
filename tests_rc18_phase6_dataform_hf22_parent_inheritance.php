<?php
declare(strict_types=1);
$r=__DIR__;
$rt=(string)file_get_contents($r.'/products/dataform/runtime.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$endpoint=(string)file_get_contents($r.'/products/dataform/parent-value.php');
$c=[];$f=function(string $n,bool $ok)use(&$c):void{$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};

$f('parent default mode',str_contains($rt,'value="parent_field"')&&str_contains($rt,'Wert aus Eltern-DataForm'));
$f('parent relation config',str_contains($rt,"\$configuration['parent_relation_id']"));
$f('parent field config',str_contains($rt,"\$configuration['parent_field_id']"));
$f('active 1:n validation',str_contains($rt,"relation_type='1:n'")&&str_contains($rt,'lookup_field_id'));
$f('designer parent relation select',str_contains($rt,'data-parent-relation-select'));
$f('designer parent field select',str_contains($rt,'data-parent-field-select'));
$f('lookup parent record selector',str_contains($records,'data-parent-relation-id'));
$f('parent inheritance runner',str_contains($records,'applyParentValue')&&str_contains($records,'parent-value.php'));
$f('endpoint authenticated',str_contains($endpoint,"enterprise_require_auth('../../')"));
$f('endpoint validates child relation',str_contains($endpoint,'target_dataform_id=?')&&str_contains($endpoint,"relation_type='1:n'"));
$f('endpoint validates parent field',str_contains($endpoint,'SELECT name FROM dataform_fields WHERE id=? AND dataform_id=?'));
$f('endpoint reads exact parent record',str_contains($endpoint,'DataFormRecordStore::find'));
$f('inheritance only on create',str_contains($records,"getAttribute('data-record-mode')!=='create'"));
$f('HF22 marker',str_contains($rt,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed?'FAIL':'PASS',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed?1:0);
