<?php
declare(strict_types=1);
$required=[
__DIR__.'/DataForm5-Core/system/modules/Background/ModuleBackgroundAdmin.php',
__DIR__.'/app/background/index.php',
__DIR__.'/docs/PHASE_T_BACKGROUND_ADMIN.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}
$fileQueue=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/queue/Drivers/FileQueue.php');
foreach(['statistics','failedJobs','retryFailed','deleteFailed'] as $method)if(!str_contains($fileQueue,'function '.$method)){fwrite(STDERR,"FileQueue missing {$method}\n");exit(2);}
$history=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/scheduler/Core/ScheduleHistory.php');
if(!str_contains($history,'function recent')){fwrite(STDERR,"ScheduleHistory reader missing\n");exit(3);}
$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
foreach(['background.view','background.manage','enterprise_module_background_admin'] as $needle)if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Bootstrap missing {$needle}\n");exit(4);}
$layout=(string)file_get_contents(__DIR__.'/system/ui/layout.php');
if(!str_contains($layout,"'operations' => ['Betrieb'") && !str_contains($layout,"'background' => ['Jobs'")){
    fwrite(STDERR,"Navigation missing Jobs/Betrieb entry\n");exit(5);
}
echo "PHASE_T_BACKGROUND_ADMIN_OK\n";
