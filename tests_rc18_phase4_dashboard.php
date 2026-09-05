<?php
declare(strict_types=1);

$root=__DIR__;
foreach([
$root.'/DataForm5-Core/system/core/Dashboard/EnterpriseDashboard.php',
$root.'/app/operations/index.php',
$root.'/docs/RC1.8_PHASE4_DASHBOARD_CONSOLIDATION.md'
] as $file)if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}

$layout=(string)file_get_contents($root.'/system/ui/layout.php');
if(!str_contains($layout,"'operations' => ['Betrieb'"))exit(2);
if(!str_contains($layout,'$nav[\'logout\'] = [\'Abmelden\', $base . \'logout.php\'];')){
    fwrite(STDERR,"Global logout navigation missing from authenticated app layout\n");
    exit(7);
}
foreach(["'background' => ['Jobs'","'monitoring' => ['Monitoring'","'cluster' => ['Cluster'","'storage' => ['Storage'","'replication' => ['Replikation'"] as $legacy)
    if(str_contains($layout,$legacy)){fwrite(STDERR,"Legacy top-level navigation remains: {$legacy}\n");exit(3);}

$dashboard=(string)file_get_contents($root.'/app/dashboard.php');
foreach(['Enterprise Control Center','Betriebsstatus','operations/index.php'] as $needle)
    if(!str_contains($dashboard,$needle)){fwrite(STDERR,"Dashboard missing {$needle}\n");exit(4);}

$bootstrap=(string)file_get_contents($root.'/system/app/bootstrap.php');
if(!str_contains($bootstrap,'enterprise_dashboard_snapshot'))exit(5);

$schedulerProvider=(string)file_get_contents($root.'/DataForm5-Core/system/scheduler/Providers/SchedulerServiceProvider.php');
if(!str_contains($schedulerProvider,'singleton(ScheduleHistory::class')){fwrite(STDERR,"ScheduleHistory is not registered\n");exit(6);}

echo "RC18_PHASE4_DASHBOARD_OK\n";
