<?php
declare(strict_types=1);
use DataForm5\Health\Checks\CallbackHealthCheck;use DataForm5\Health\Core\{DiagnosticReport,HealthManager,HealthStatus};
$root=dirname(__DIR__);$kernel=require $root.'/bootstrap/app.php';$health=$kernel->container()->get(HealthManager::class);
$health->register(new CallbackHealthCheck('app.alive',fn()=>['build'=>'0020']),true,true);
$r=$health->readiness();assert($r->status===HealthStatus::UP);assert(count($r->checks)>=2);
$l=$health->liveness();assert($l->status===HealthStatus::UP);assert(count($l->checks)===1);
$health->register(new CallbackHealthCheck('failing',fn()=>false),true,false);assert($health->readiness()->status===HealthStatus::DOWN);
$diag=$kernel->container()->get(DiagnosticReport::class)->generate();assert(isset($diag['system']['php_version'],$diag['health']['status']));
echo "PASS: Health, Diagnostics and System Status Layer\n";
