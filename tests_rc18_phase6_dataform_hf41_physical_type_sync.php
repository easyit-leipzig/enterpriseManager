<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/DataFormManager.php');

require_once $r.'/products/dataform/system/DataFormManager.php';

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$reflect=new ReflectionMethod(DataFormManager::class,'fieldTypeForSqlType');
$map=static fn(string $sql):string => (string)$reflect->invoke(null,$sql);

$f('HF41 runtime marker',
    str_contains($runtime,'HF41 PHYSICAL TYPE SYNC ACTIVE')
    && str_contains($runtime,'DataForm · HF41')
);
$f('SQL type helper maps integer to number',$map('int(11)')==='number');
$f('SQL type helper maps varchar to text',$map('varchar(100)')==='text');
$f('SQL type helper maps text to textarea',$map('text')==='textarea');
$f('SQL type helper maps datetime',$map('datetime')==='datetime');
$f('SQL type helper maps tinyint1 to checkbox',$map('tinyint(1)')==='checkbox');
$f('synchronizer compares old and new sql type',
    str_contains($manager,'$oldSqlType')
    && str_contains($manager,'$newSqlType')
    && str_contains($manager,'$oldSqlType!==$newSqlType')
);
$f('schema derived field type is updated conservatively',
    str_contains($manager,'$syncFieldType=')
    && str_contains($manager,'$currentFieldType===$oldExpectedFieldType')
    && str_contains($manager,'SET field_type=?,configuration_json=?')
);
$f('table binding sql type is refreshed',
    str_contains($manager,"'sql_type'=>strtolower")
    && str_contains($manager,'$oldSqlType!==$newSqlType')
);
$f('sync result reports updated fields',
    str_contains($manager,"'updated'=>0")
    && str_contains($manager,"'updated'=>\$updated")
);
$f('physical column edit triggers immediate dataform sync',
    ($alter=strpos($runtime,'TableWorkspaceManager::updateManagedColumn('))!==false
    && ($sync=strpos($runtime,'DataFormManager::synchronizeBoundTableFields(', $alter))!==false
    && $sync>$alter
);
$f('opening a dataform refreshes physical schema before field query',
    ($open=strpos($runtime,'if ($selectedDataformId > 0)'))!==false
    && ($openSync=strpos($runtime,'DataFormManager::synchronizeBoundTableFields(', $open))!==false
    && ($fieldQuery=strpos($runtime,"SELECT id, name, label, field_type",$open))!==false
    && $openSync<$fieldQuery
);
$f('custom specialized field types are not blindly overwritten',
    str_contains($manager,'Benutzerdefinierte')
    && str_contains($manager,'$currentFieldType===$oldExpectedFieldType')
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF41',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
