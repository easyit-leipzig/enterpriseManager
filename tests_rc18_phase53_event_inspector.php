<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/core/Developer/EventInspector.php',
$root.'/app/developer/events.php',$root.'/tools/developer-events.php',
$root.'/docs/RC1.8_PHASE5.3_EVENT_INSPECTOR.md'] as $file)
    if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}
$dispatcher=(string)file_get_contents($root.'/DataForm5-Core/system/events/Core/EventDispatcher.php');
if(!str_contains($dispatcher,'describeListeners'))exit(2);
$provider=(string)file_get_contents($root.'/DataForm5-Core/system/core/Providers/DeveloperServiceProvider.php');
if(!str_contains($provider,'EventInspector::class')||!str_contains($provider,'enterprise_events()'))exit(3);
$bootstrap=(string)file_get_contents($root.'/system/app/bootstrap.php');
if(!str_contains($bootstrap,'enterprise_event_inspector'))exit(4);
$page=(string)file_get_contents($root.'/app/developer/events.php');
foreach(['Event Inspector','Prioritäten','Dispatch-Trace','nur dispatched'] as $needle)
    if(!str_contains($page,$needle)){fwrite(STDERR,"Page missing {$needle}\n");exit(5);}
echo "RC18_PHASE53_EVENT_INSPECTOR_OK\n";
