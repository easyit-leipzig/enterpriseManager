<?php
declare(strict_types=1);
$root=__DIR__;
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$checks=[];
$check=function(string $name,bool $ok)use(&$checks):void{
    $checks[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};
$check('HF18 marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));
$check('Throwable boundary',str_contains($runtime,'catch (Throwable $renderThrowable)'));
$check('partial buffer cleared',str_contains($runtime,'ob_clean()'));
$check('shell survives',str_contains($runtime,'Die Enterprise-Shell bleibt aktiv'));
$check('config normalizer',str_contains($runtime,'function dataform_field_config'));
$check('string options normalized',str_contains($runtime,'is_string($options)'));
$check('preview normalized',str_contains($runtime,"dataform_field_config(\$field['configuration_json'] ?? null)"));
$check('selected field normalized',str_contains($runtime,"dataform_field_config(\$selectedField['configuration_json'] ?? null)"));
$check('HF18 header',str_contains($runtime,'X-EasyIT-DataForm-Runtime: HF36'));
$failed=count(array_filter($checks,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
 'release'=>'RC1.8','hotfix'=>'HF34',
 'status'=>$failed===0?'PASS':'FAIL',
 'checks'=>$checks,
 'summary'=>['checks'=>count($checks),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed===0?0:1);
