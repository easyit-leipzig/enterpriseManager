<?php
declare(strict_types=1);

$root=__DIR__;
$keyClass=(string)file_get_contents($root.'/products/dataform/system/DataFormSecretKey.php');
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$installer=(string)file_get_contents($root.'/DataForm5-Core/system/installer/Core/EnterpriseInstaller.php');
$dbAssistant=(string)file_get_contents($root.'/installer/database.php');
$envExample=(string)file_get_contents($root.'/DataForm5-Core/.env.example');

$checks=[];
$check=function(string $name,bool $ok)use(&$checks):void{
    $checks[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$check('secret key helper exists',is_file($root.'/products/dataform/system/DataFormSecretKey.php'));
$check('uses 32 random bytes',str_contains($keyClass,'random_bytes(32)'));
$check('uses dfk1 prefix',str_contains($keyClass,"'dfk1_'"));
$check('uses locked env write',str_contains($keyClass,'LOCK_EX'));
$check('verifies written key',str_contains($keyClass,'hash_equals'));
$check('runtime auto provisions key',str_contains($runtime,'DataFormSecretKey::ensure'));
$check('runtime reports automatic provisioning',str_contains($runtime,'Secret-Schlüssel automatisch eingerichtet.'));
$check('fresh installer provisions DATAFORM_APP_KEY',str_contains($installer,"'DATAFORM_APP_KEY'=>'dfk1_'"));
$check('database assistant repairs missing key',str_contains($dbAssistant,'ensureDataFormSecretKey'));
$check('env template contains placeholder',str_contains($envExample,'DATAFORM_APP_KEY='));

require_once $root.'/products/dataform/system/DataFormSecretKey.php';

$tmp=sys_get_temp_dir().'/easyit-hf24-'.bin2hex(random_bytes(5)).'.env';
file_put_contents($tmp,"APP_ENV=testing\nDATAFORM_APP_KEY=\n",LOCK_EX);

$first=DataFormSecretKey::ensure($tmp);
$afterFirst=(string)file_get_contents($tmp);
$check(
    'first ensure creates a dedicated key',
    ($first['created']??false)===true
    && str_contains($afterFirst,'DATAFORM_APP_KEY=dfk1_')
);

$second=DataFormSecretKey::ensure($tmp);
$check(
    'second ensure is idempotent',
    ($second['created']??true)===false
    && hash_equals((string)$first['key'],(string)$second['key'])
);

file_put_contents($tmp,"APP_KEY=existing-app-key\nDATAFORM_APP_KEY=\n",LOCK_EX);
$fallback=DataFormSecretKey::ensure($tmp);
$check(
    'existing APP_KEY is preserved and used',
    ($fallback['source']??'')==='APP_KEY'
    && ($fallback['created']??true)===false
    && (string)$fallback['key']==='existing-app-key'
);

@unlink($tmp);

$failed=count(array_filter(
    $checks,
    static fn(array $row):bool=>$row['status']==='FAIL'
));

echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$checks,
    'summary'=>['checks'=>count($checks),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($failed===0?0:1);
