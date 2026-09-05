<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/modules/SDK/ModulePackager.php',
$root.'/DataForm5-Core/system/modules/SDK/Commands/ModulePackageCommand.php',
$root.'/docs/RC1.8_PHASE5.9_MODULE_PACKAGING.md'
] as $f)if(!is_file($f))exit(1);
$p=(string)file_get_contents($root.'/DataForm5-Core/system/modules/SDK/ModulePackager.php');
if(!str_contains($p,'ZipArchive')||!str_contains($p,'PharData'))exit(2);
$c=(string)file_get_contents($root.'/DataForm5-Core/system/console/Providers/ConsoleServiceProvider.php');
if(!str_contains($c,'ModulePackageCommand'))exit(3);
echo "RC18_PHASE59_MODULE_PACKAGING_OK\n";
