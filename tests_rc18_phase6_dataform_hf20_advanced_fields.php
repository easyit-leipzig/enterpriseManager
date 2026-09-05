<?php
declare(strict_types=1);
$r=__DIR__;$rt=(string)file_get_contents($r.'/products/dataform/runtime.php');$rec=(string)file_get_contents($r.'/products/dataform/records.php');$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');$c=[];
$f=function(string $n,bool $ok)use(&$c):void{$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$f('advanced disclosure',str_contains($rt,'<summary>Erweitert</summary>'));
foreach(['help_text','default_value','min_length','max_length','pattern','min_value','max_value','step','autocomplete','inputmode','rows','readonly','hidden','trim','list_visible','searchable','filterable','sortable'] as $key)$f('property '.$key,str_contains($rt,'name="'.$key.'"'));
$f('persistence',str_contains($rt,'$configuration[\'help_text\']')&&str_contains($rt,'$configuration[\'list_visible\']'));
$f('records configuration',str_contains($rec,'function df_field_config')&&str_contains($rec,"['_config']"));
$f('validation',str_contains($rec,"['min_length']")&&str_contains($rec,'df_pattern_match'));
$f('defaults and visibility',str_contains($rec,"['default_value']")&&str_contains($rec,"['readonly']")&&str_contains($rec,"['hidden']"));
$f('list/search/filter/sort',str_contains($rec,'$listFields')&&str_contains($rec,'$searchableNames')&&str_contains($rec,'$filterableNames')&&str_contains($rec,'$sortableNames'));
$f('advanced css',str_contains($css,'.df-field-advanced')&&str_contains($css,'.df-advanced-grid'));
$f('HF20 marker',str_contains($rt,'HF36 DATAFORM RUNTIME ACTIVE'));
$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$failed?'FAIL':'PASS','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($failed?1:0);
