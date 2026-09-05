<?php
declare(strict_types=1);
$root=__DIR__;
$files=[
 'nav'=>$root.'/products/dataform/system/DataFormConfigNavigation.php',
 'runtime'=>$root.'/products/dataform/runtime.php',
 'foundation'=>$root.'/products/dataform/foundation.php',
 'workflow'=>$root.'/products/dataform/workflow.php',
 'relations'=>$root.'/products/dataform/relations.php',
];
$c=[];$check=function($n,$v)use(&$c){$c[$n]=$v?'PASS':'FAIL';};
foreach($files as $k=>$f)$check($k.' exists',is_file($f));
$n=file_get_contents($files['nav']);$r=file_get_contents($files['runtime']);$f=file_get_contents($files['foundation']);$w=file_get_contents($files['workflow']);$rel=file_get_contents($files['relations']);
$check('ten central config destinations',substr_count($n,"'label'=>")>=10);
$check('runtime map',str_contains($r,'dataform_config_map'));
$check('settings related',str_contains($r,'dataform_config_related((int)$project[\'id\'],(int)$selectedDataform[\'id\'],\'settings\')'));
$check('preview backrefs',str_contains($r,"'preview'"));
$check('foundation map',str_contains($f,'dataform_config_map'));
$check('duplicate storage editor removed',!str_contains($f,'name="action" value="save_runtime_settings"'));
$check('behavior links to owner',str_contains($f,'In DataForm-Einstellungen ändern'));
$check('workflow map',str_contains($w,'dataform_config_map($projectId,$dataformId,\'workflow\')'));
$check('relations context',str_contains($rel,'$dataformId=(int)($_GET[\'dataform\']'));
$check('relations map',str_contains($rel,'dataform_config_map($projectId,$dataformId,\'relations\')'));
foreach($c as $n=>$v)echo $v.' '.$n.PHP_EOL;
$fail=count(array_filter($c,fn($v)=>$v!=='PASS'));echo count($c).'/'.count($c).' checks; failures='.$fail.PHP_EOL;exit($fail?1:0);
