<?php
declare(strict_types=1);
$root=__DIR__;
$required=[
    $root.'/DataForm5-Core/system/modules/Core/ModuleManager.php',
    $root.'/DataForm5-Core/system/modules/Providers/ModuleServiceProvider.php',
    $root.'/DataForm5-Core/config/modules.php',
    $root.'/app/developer/modules.php',
    $root.'/docs/PHASE_D_MODULE_PLATFORM.md',
];
foreach($required as $file){ if(!is_file($file)){fwrite(STDERR,"PHASE_D_FAIL missing {$file}\n"); exit(1);} }
$config=require $root.'/DataForm5-Core/config/modules.php';
if(!isset($config['paths']) || count($config['paths'])<2){fwrite(STDERR,"PHASE_D_FAIL module paths\n"); exit(1);}
echo "PHASE_D_MODULE_PLATFORM_OK\n";
