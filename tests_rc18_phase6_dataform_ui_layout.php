<?php
declare(strict_types=1);
$r=__DIR__;$checks=[];$c=function(string $n,bool $ok)use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$l=(string)file_get_contents($r.'/system/ui/layout.php');
$i=(string)file_get_contents($r.'/products/dataform/runtime.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');
$c('layout external stylesheet',str_contains($l,'assets/css/enterprise.css'));
$c('layout inline css fallback',str_contains($l,'easyit-enterprise-critical-css')&&str_contains($l,'file_get_contents($enterpriseCssFile)'));
$c('workspace runtime class',str_contains($i,'HF36 DATAFORM RUNTIME ACTIVE')&&str_contains($i,'workspace-page dataform-runtime-page'));
$c('designer classes exist',str_contains($i,'df-designer-shell')&&str_contains($i,'df-field-inspector'));
$c('designer css exists',str_contains($css,'.df-designer-shell')&&str_contains($css,'.df-field-inspector'));
$legacy=[];foreach(glob($r.'/products/dataform/*.php')?:[] as $f){$x=(string)file_get_contents($f);if(preg_match('/render_page\\(\\s*["\']/',$x))$legacy[]=basename($f);}
$c('no legacy render_page signatures',count($legacy)===0);
$f=count(array_filter($checks,fn($x)=>$x['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$f?'FAIL':'PASS','checks'=>$checks,'legacy'=>$legacy,'summary'=>['checks'=>count($checks),'failed'=>$f]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($f?1:0);
