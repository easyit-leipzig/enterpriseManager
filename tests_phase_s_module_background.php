<?php
declare(strict_types=1);

$required=[
    __DIR__.'/DataForm5-Core/system/modules/Background/ModuleBackgroundRegistry.php',
    __DIR__.'/DataForm5-Core/system/modules/Background/ModuleQueuedJob.php',
    __DIR__.'/DataForm5-Core/system/modules/Background/ModuleBackgroundLogger.php',
    __DIR__.'/tools/module-job-dispatch.php',
    __DIR__.'/tools/module-queue-work.php',
    __DIR__.'/tools/module-schedule-run.php',
    __DIR__.'/tools/module-background-status.php',
    __DIR__.'/docs/PHASE_S_MODULE_BACKGROUND.md',
];
foreach($required as $file){
    if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}
}
$manifest=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/modules/Core/ModuleManifest.php');
foreach(['jobs','schedules'] as $needle){
    if(!str_contains($manifest,$needle)){fwrite(STDERR,"Manifest missing {$needle}\n");exit(2);}
}
$provider=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/modules/Providers/ModuleServiceProvider.php');
if(!str_contains($provider,'ModuleBackgroundRegistry')||!str_contains($provider,'registerSchedules')){
    fwrite(STDERR,"Background services not registered\n");exit(3);
}
$scaffolder=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/modules/SDK/ModuleScaffolder.php');
if(!str_contains($scaffolder,"ExampleJob.php")||!str_contains($scaffolder,"'schedules'")){
    fwrite(STDERR,"SDK background template missing\n");exit(4);
}
echo "PHASE_S_MODULE_BACKGROUND_OK\n";
