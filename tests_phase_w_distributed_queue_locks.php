<?php
declare(strict_types=1);

$required=[
    __DIR__.'/DataForm5-Core/system/queue/Drivers/DatabaseQueue.php',
    __DIR__.'/DataForm5-Core/system/scheduler/Contracts/MutexInterface.php',
    __DIR__.'/DataForm5-Core/system/scheduler/Core/DatabaseMutex.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterLock.php',
    __DIR__.'/tools/cluster-lock-test.php',
    __DIR__.'/docs/PHASE_W_DISTRIBUTED_QUEUE_LOCKS.md',
];
foreach($required as $file){
    if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}
}

$queueManager=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/queue/Core/QueueManager.php');
if(!str_contains($queueManager,"'database'=>new DatabaseQueue")){fwrite(STDERR,"DatabaseQueue not wired\n");exit(2);}

$scheduler=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/scheduler/Core/Scheduler.php');
if(!str_contains($scheduler,'MutexInterface')){fwrite(STDERR,"Scheduler not using MutexInterface\n");exit(3);}

$provider=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/scheduler/Providers/SchedulerServiceProvider.php');
foreach(['DatabaseMutex','mutex_driver','MutexInterface::class'] as $needle){
    if(!str_contains($provider,$needle)){fwrite(STDERR,"Scheduler provider missing {$needle}\n");exit(4);}
}

$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
if(!str_contains($bootstrap,'enterprise_cluster_lock')){fwrite(STDERR,"cluster lock helper missing\n");exit(5);}

echo "PHASE_W_DISTRIBUTED_QUEUE_LOCKS_OK\n";
