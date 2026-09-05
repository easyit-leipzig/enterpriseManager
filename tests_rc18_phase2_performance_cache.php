<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/cache/Core/CacheNamespace.php',
$root.'/tools/cache-clear.php',$root.'/tools/cache-status.php',
$root.'/docs/RC1.8_PHASE2_PERFORMANCE_CACHE.md'] as $f)if(!is_file($f)){fwrite(STDERR,"Missing {$f}\n");exit(1);}
$m=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Core/ModuleManager.php');
if(!str_contains($m,'discovery:')||!str_contains($m,'CacheNamespace'))exit(2);
$r=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Routing/ModuleRouteRegistry.php');
if(!str_contains($r,'private ?array $cache=null'))exit(3);
echo "RC18_PHASE2_PERFORMANCE_CACHE_OK\n";
