<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME']='/easyIT-Enterprise-RC1.8-FC1-HF17/products/dataform/runtime.php';
require __DIR__.'/system/ui/layout.php';
require __DIR__.'/products/dataform/system/WorkspaceLayout.php';
ob_start();
dataform_runtime_render([
 'title'=>'Runtime Test',
 'content'=>'<div class="df-workspace"><div class="df-designer-shell"><strong>HF17_RUNTIME_CONTENT</strong></div></div>',
 'user'=>['username'=>'tester'],
 'help'=>['location'=>'Test → DataForm'],
]);
$html=(string)ob_get_clean();
$checks=[];$check=function(string $name,bool $ok)use(&$checks):void{$checks[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];};
$check('runtime marker',str_contains($html,'data-easyit-runtime="dataform-hf36"'));
$check('doctype and head',str_contains($html,'<!doctype html>')&&str_contains($html,'<head>'));
$check('absolute enterprise css',str_contains($html,'/easyIT-Enterprise-RC1.8-FC1-HF17/assets/css/enterprise.css'));
$check('absolute workspace css',str_contains($html,'/easyIT-Enterprise-RC1.8-FC1-HF17/products/dataform/assets/workspace.css'));
$check('runtime fallback css',str_contains($html,'easyit-dataform-runtime-fallback'));
$check('topbar shell',str_contains($html,'data-runtime-shell="topbar"'));
$check('main shell',str_contains($html,'data-runtime-shell="main"'));
$check('workspace content',str_contains($html,'df-workspace')&&str_contains($html,'df-designer-shell')&&str_contains($html,'HF17_RUNTIME_CONTENT'));
$check('logout absolute url',str_contains($html,'/easyIT-Enterprise-RC1.8-FC1-HF17/logout.php'));
$check('workspace stylesheet exists',is_file(__DIR__.'/products/dataform/assets/workspace.css'));
$check('runtime.php owns live shell',str_contains((string)file_get_contents(__DIR__.'/products/dataform/runtime.php'),'HF36 DATAFORM RUNTIME ACTIVE'));
$failed=count(array_filter($checks,fn(array $c):bool=>$c['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$failed?'FAIL':'PASS','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed?1:0);
