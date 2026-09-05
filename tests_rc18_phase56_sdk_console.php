<?php
declare(strict_types=1);
$root=__DIR__;
foreach([$root.'/easyit',$root.'/DataForm5-Core/system/modules/SDK/SdkGenerator.php',$root.'/DataForm5-Core/system/modules/SDK/Commands/MakeModuleCommand.php',$root.'/DataForm5-Core/system/modules/SDK/Commands/MakeArtifactCommand.php',$root.'/app/developer/sdk.php'] as $f)
if(!is_file($f)){fwrite(STDERR,"Missing {$f}\n");exit(1);}
$c=(string)file_get_contents($root.'/DataForm5-Core/system/console/Providers/ConsoleServiceProvider.php');
if(!str_contains($c,'MakeModuleCommand')||!str_contains($c,'MakeArtifactCommand'))exit(2);
echo "RC18_PHASE56_SDK_CONSOLE_OK\n";
