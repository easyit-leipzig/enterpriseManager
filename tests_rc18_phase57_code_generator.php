<?php
declare(strict_types=1);
$root=__DIR__;foreach([$root.'/DataForm5-Core/system/modules/SDK/CrudScaffolder.php',$root.'/DataForm5-Core/system/modules/SDK/Commands/MakeCrudCommand.php'] as $f)if(!is_file($f))exit(1);
$c=(string)file_get_contents($root.'/DataForm5-Core/system/console/Providers/ConsoleServiceProvider.php');if(!str_contains($c,'MakeCrudCommand'))exit(2);
$m=(string)file_get_contents($root.'/DataForm5-Core/system/modules/SDK/ModuleScaffolder.php');foreach(['InstallHook','DEVELOPMENT.md'] as $n)if(!str_contains($m,$n))exit(3);echo "RC18_PHASE57_CODE_GENERATOR_OK\n";
