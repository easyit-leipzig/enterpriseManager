<?php
declare(strict_types=1);

$required=[
__DIR__.'/DataForm5-Core/system/cluster/Security/NodeTrustStore.php',
__DIR__.'/DataForm5-Core/system/cluster/Security/ReplayGuard.php',
__DIR__.'/DataForm5-Core/system/cluster/Security/NodeSigner.php',
__DIR__.'/DataForm5-Core/system/cluster/Security/NodeAuthenticator.php',
__DIR__.'/app/cluster/security.php',
__DIR__.'/tools/cluster-trust-node.php',
__DIR__.'/docs/PHASE_Z_CLUSTER_SECURITY.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}

$provider=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/cluster/Providers/ClusterServiceProvider.php');
foreach(['NodeTrustStore','ReplayGuard','NodeSigner','NodeAuthenticator'] as $needle)
    if(!str_contains($provider,$needle)){fwrite(STDERR,"Provider missing {$needle}\n");exit(2);}

$event=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/replication/Core/ReplicationEvent.php');
if(!str_contains($event,'withSecurity')||!str_contains($event,'signingPayload')) exit(3);

$sync=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/replication/Core/ClusterSynchronizer.php');
if(!str_contains($sync,'Replication signature rejected')) exit(4);

$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
foreach(['cluster.security','enterprise_cluster_trust_store'] as $needle)
    if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Bootstrap missing {$needle}\n");exit(5);}

$autoload=(string)file_get_contents(__DIR__.'/DataForm5-Core/bootstrap/namespaces.php');
foreach(["'Cluster\\\\' =>","'Replication\\\\' =>","'Monitoring\\\\' =>"] as $needle)
    if(!str_contains($autoload,$needle)){fwrite(STDERR,"Autoload missing {$needle}\n");exit(6);}

echo "PHASE_Z_CLUSTER_SECURITY_OK\n";
