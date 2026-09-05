<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/core/Developer/ProfilerHub.php',
$root.'/DataForm5-Core/system/core/Developer/RequestProfiler.php',
$root.'/app/developer/profiler.php',
$root.'/tools/developer-profiler.php',
$root.'/docs/RC1.8_PHASE5.5_PROFILER.md'] as $f)
    if(!is_file($f)){fwrite(STDERR,"Missing {$f}\n");exit(1);}
$provider=(string)file_get_contents($root.'/DataForm5-Core/system/core/Providers/DeveloperServiceProvider.php');
if(!str_contains($provider,'RequestProfiler::class'))exit(2);
$fs=(string)file_get_contents($root.'/DataForm5-Core/system/core/Filesystem/Filesystem.php');
if(!str_contains($fs,"ProfilerHub::start('filesystem'"))exit(3);
$q=(string)file_get_contents($root.'/DataForm5-Core/system/queue/Core/QueueWorker.php');
if(!str_contains($q,"ProfilerHub::start('queue'"))exit(4);
$page=(string)file_get_contents($root.'/app/developer/profiler.php');
foreach(['Request Profiler','Kategorien','Timeline'] as $needle)if(!str_contains($page,$needle))exit(5);
echo "RC18_PHASE55_PROFILER_OK\n";
