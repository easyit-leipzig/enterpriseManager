<?php
declare(strict_types=1);

$required=[
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterNode.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterRegistry.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterElection.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterHeartbeat.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterHealth.php',
    __DIR__.'/DataForm5-Core/system/cluster/Core/ClusterManager.php',
    __DIR__.'/DataForm5-Core/system/cluster/Providers/ClusterServiceProvider.php',
    __DIR__.'/app/cluster/index.php',
    __DIR__.'/app/cluster/api.php',
    __DIR__.'/tools/cluster-heartbeat.php',
    __DIR__.'/tools/cluster-status.php',
    __DIR__.'/docs/PHASE_V_CLUSTER_FOUNDATION.md',
];
foreach($required as $file) if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}

$nodeFile=__DIR__.'/DataForm5-Core/system/cluster/Core/ClusterNode.php';
$electionFile=__DIR__.'/DataForm5-Core/system/cluster/Core/ClusterElection.php';
if(!str_contains((string)file_get_contents($nodeFile),'function isOnline')) exit(2);
if(!str_contains((string)file_get_contents($electionFile),'function leader')) exit(3);

$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
foreach(['cluster.view','cluster.manage','enterprise_cluster'] as $needle){
    if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Missing bootstrap symbol: {$needle}\n");exit(4);}
}

$layout=(string)file_get_contents(__DIR__.'/system/ui/layout.php');
if(!str_contains($layout,"'operations' => ['Betrieb'") && !str_contains($layout,"'cluster' => ['Cluster'")) exit(5);

echo "PHASE_V_CLUSTER_FOUNDATION_OK\n";
