<?php
declare(strict_types=1);
$root=__DIR__;$checks=[];
$c=function(string $name,bool $ok)use(&$checks){$checks[$name]=$ok;echo ($ok?'PASS':'FAIL').' - '.$name.PHP_EOL;};
$logo=$root.'/assets/img/easyit-epManager-logo.png';
$layout=(string)file_get_contents($root.'/system/ui/layout.php');
$workspace=(string)file_get_contents($root.'/products/dataform/system/WorkspaceLayout.php');
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$c('manager logo exists',is_file($logo) && filesize($logo)>0);
$c('global layout uses manager logo',str_contains($layout,'assets/img/easyit-epManager-logo.png'));
$c('workspace uses manager logo',str_contains($workspace,'assets/img/easyit-epManager-logo.png'));
$c('legacy runtime uses manager logo',str_contains($runtime,"assets/img/easyit-epManager-logo.png"));
$c('global layout has no rendered eI fallback',!str_contains($layout,'<span class="brand-mark">eI</span>'));
$c('workspace has no rendered eI fallback',!str_contains($workspace,'<span class="df-runtime-brand-mark">eI</span>'));
$c('legacy runtime has no rendered eI fallback',!str_contains($runtime,'<span class="df-runtime-brand-mark">eI</span>'));
$c('legacy runtime proof banners removed',!str_contains($runtime,'<div class="hf26-proof" id="hf26-runtime-proof">')&&!str_contains($runtime,'<div class="hf26-proof" id="hf41-physical-type-sync-proof">'));
$c('legacy HF41 brand label not rendered',!str_contains($runtime,'<small style="display:block;color:#c8d5e7">DataForm · HF41</small>'));
$f=count(array_filter($checks,static fn(bool $v):bool=>!$v));
echo 'HF76 publication branding: '.(count($checks)-$f).'/'.count($checks).' PASS'.PHP_EOL;exit($f?1:0);
