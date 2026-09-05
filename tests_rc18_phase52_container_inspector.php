<?php
declare(strict_types=1);

$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/core/Developer/ContainerInspector.php',
$root.'/app/developer/container.php',
$root.'/tools/developer-container.php',
$root.'/docs/RC1.8_PHASE5.2_CONTAINER_INSPECTOR.md'
] as $file)if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}

$provider=(string)file_get_contents($root.'/DataForm5-Core/system/core/Providers/DeveloperServiceProvider.php');
if(!str_contains($provider,'ContainerInspector::class'))exit(2);

$bootstrap=(string)file_get_contents($root.'/system/app/bootstrap.php');
if(!str_contains($bootstrap,'enterprise_container_inspector'))exit(3);

$page=(string)file_get_contents($root.'/app/developer/container.php');
foreach(['Service Container Inspector','Constructor','nur resolved','Alias-Matrix'] as $needle)
    if(!str_contains($page,$needle)){fwrite(STDERR,"Page missing {$needle}\n");exit(4);}

echo "RC18_PHASE52_CONTAINER_INSPECTOR_OK\n";
