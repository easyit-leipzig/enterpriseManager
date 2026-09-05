<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/testing/Core/DeveloperQualityCenter.php',
$root.'/DataForm5-Core/system/testing/Commands/QualityCenterCommand.php',
$root.'/app/developer/tests.php',
$root.'/docs/RC1.8_PHASE5.10_TEST_CENTER.md'
] as $f)if(!is_file($f))exit(1);
$c=(string)file_get_contents($root.'/DataForm5-Core/system/console/Providers/ConsoleServiceProvider.php');
if(!str_contains($c,'QualityCenterCommand'))exit(2);
echo "RC18_PHASE510_TEST_CENTER_OK\n";
