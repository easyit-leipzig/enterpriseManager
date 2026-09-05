<?php
declare(strict_types=1);
$root=__DIR__;
$files=[
$root.'/DataForm5-Core/system/modules/Lifecycle/ModuleLifecycleEvent.php',
$root.'/DataForm5-Core/system/modules/Lifecycle/ModuleLifecycleLogger.php',
$root.'/DataForm5-Core/system/modules/Lifecycle/ModuleLifecycleManager.php',
$root.'/docs/PHASE_R_MODULE_LIFECYCLE.md'];
foreach($files as $f) if(!is_file($f)){fwrite(STDERR,"Missing {$f}\n");exit(1);}
$m=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Core/ModuleManifest.php');
$i=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Packages/ModulePackageInstaller.php');
$r=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Routing/ModuleRouteDispatcher.php');
$a=(string)file_get_contents($root.'/DataForm5-Core/system/modules/Routing/ModuleApiDispatcher.php');
$s=(string)file_get_contents($root.'/DataForm5-Core/system/modules/SDK/ModuleScaffolder.php');
foreach(['lifecycle','install','beforeMigration','afterMigration','postInstall'] as $needle) if(!str_contains($m.$i,$needle)){fwrite(STDERR,"Missing {$needle}\n");exit(2);}
foreach(['beforeRequest','afterRequest'] as $needle) if(!str_contains($r,$needle)) exit(3);
foreach(['beforeApi','afterApi'] as $needle) if(!str_contains($a,$needle)) exit(4);
if(!str_contains($s,'InstallHook')||!str_contains($s,"'lifecycle'=>")) exit(5);
echo "PHASE_R_MODULE_LIFECYCLE_OK\n";
