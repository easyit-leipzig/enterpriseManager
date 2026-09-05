<?php
declare(strict_types=1);

$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/core/Developer/HookInspector.php',
$root.'/app/developer/hooks.php',
$root.'/tools/developer-hooks.php',
$root.'/docs/RC1.8_PHASE5.4_HOOK_INSPECTOR.md'
] as $file)if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}

$provider=(string)file_get_contents($root.'/DataForm5-Core/system/core/Providers/DeveloperServiceProvider.php');
if(!str_contains($provider,'HookInspector::class'))exit(2);

$bootstrap=(string)file_get_contents($root.'/system/app/bootstrap.php');
if(!str_contains($bootstrap,'enterprise_hook_inspector'))exit(3);

$page=(string)file_get_contents($root.'/app/developer/hooks.php');
foreach(['Hook Inspector','Lifecycle-Hooks','Priorität','Runtime'] as $needle)
    if(!str_contains($page,$needle)){fwrite(STDERR,"Page missing {$needle}\n");exit(4);}

echo "RC18_PHASE54_HOOK_INSPECTOR_OK\n";
