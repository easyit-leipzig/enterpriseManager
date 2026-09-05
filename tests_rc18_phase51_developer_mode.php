<?php
declare(strict_types=1);

$root=__DIR__;
$required=[
$root.'/DataForm5-Core/system/core/Developer/DeveloperTrace.php',
$root.'/DataForm5-Core/system/core/Developer/DeveloperInspector.php',
$root.'/DataForm5-Core/system/core/Providers/DeveloperServiceProvider.php',
$root.'/DataForm5-Core/config/developer.php',
$root.'/app/developer/index.php',
$root.'/app/developer/overlay.php',
$root.'/assets/js/developer-overlay.js',
$root.'/docs/RC1.8_PHASE5.1_DEVELOPER_MODE.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}

$providers=(string)file_get_contents($root.'/DataForm5-Core/config/providers.php');
if(!str_contains($providers,'DeveloperServiceProvider'))exit(2);

$bootstrap=(string)file_get_contents($root.'/system/app/bootstrap.php');
foreach(['developer.view','enterprise_developer_enabled','enterprise_developer_trace','enterprise_developer_inspector'] as $needle)
    if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Missing {$needle}\n");exit(3);}

$layout=(string)file_get_contents($root.'/system/ui/layout.php');
foreach(['Developer','developer-overlay.js','data-developer-overlay-endpoint'] as $needle)
    if(!str_contains($layout,$needle)){fwrite(STDERR,"Layout missing {$needle}\n");exit(4);}

$trace=(string)file_get_contents($root.'/DataForm5-Core/system/core/Developer/DeveloperTrace.php');
if(!str_contains($trace,'[REDACTED]'))exit(5);

echo "RC18_PHASE51_DEVELOPER_MODE_OK\n";
