<?php
declare(strict_types=1);
use DataForm5\Modules\Core\HookDispatcher;
use DataForm5\Modules\Core\ModuleRegistry;
$kernel=require dirname(__DIR__).'/bootstrap/app.php'; $c=$kernel->container();
$r=$c->get(ModuleRegistry::class);
if(!$r->has('demo-module')) throw new RuntimeException('Demo-Modul wurde nicht geladen.');
if($c->get('demo.module.registered')!==true) throw new RuntimeException('Modulregistrierung fehlgeschlagen.');
$out=$c->get(HookDispatcher::class)->dispatch('core.health');
if($out!==['demo-module:ok']) throw new RuntimeException('Hook-System fehlgeschlagen.');
echo "PASS: Plugin and Module System\n";
