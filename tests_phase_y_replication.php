<?php
declare(strict_types=1);

$required=[
__DIR__.'/DataForm5-Core/system/replication/Core/ReplicationEvent.php',
__DIR__.'/DataForm5-Core/system/replication/Core/ReplicationStateStore.php',
__DIR__.'/DataForm5-Core/system/replication/Core/ClusterSynchronizer.php',
__DIR__.'/DataForm5-Core/system/replication/Core/ReplicationSnapshotService.php',
__DIR__.'/DataForm5-Core/system/replication/Contracts/ReplicationTransportInterface.php',
__DIR__.'/DataForm5-Core/system/replication/Drivers/SharedStorageReplicationTransport.php',
__DIR__.'/DataForm5-Core/system/replication/Providers/ReplicationServiceProvider.php',
__DIR__.'/DataForm5-Core/config/replication.php',
__DIR__.'/app/replication/index.php',
__DIR__.'/tools/replication-publish-modules.php',
__DIR__.'/tools/replication-consume.php',
__DIR__.'/tools/replication-status.php',
__DIR__.'/docs/PHASE_Y_REPLICATION.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}

$app=(string)file_get_contents(__DIR__.'/DataForm5-Core/config/providers.php');
if(!str_contains($app,'ReplicationServiceProvider')){fwrite(STDERR,"Provider not registered\n");exit(2);}

$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
foreach(['replication.view','replication.manage','enterprise_replication','enterprise_replication_snapshots'] as $needle)
    if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Missing {$needle}\n");exit(3);}

$layout=(string)file_get_contents(__DIR__.'/system/ui/layout.php');
if(!str_contains($layout,"'operations' => ['Betrieb'") && !str_contains($layout,"'replication' => ['Replikation'")) exit(4);

$eventClass=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/replication/Core/ReplicationEvent.php');
if(!str_contains($eventClass,'function verify')) exit(5);

echo "PHASE_Y_REPLICATION_OK\n";
