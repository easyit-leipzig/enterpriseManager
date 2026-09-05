<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/autoload.php';

use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Core\Contracts\ContainerInterface;
use DataForm5\Core\Exceptions\ContainerException;

interface PhaseBClock { public function value(): string; }
final class PhaseBSystemClock implements PhaseBClock { public function value(): string { return 'ok'; } }
final class PhaseBConsumer { public function __construct(public PhaseBClock $clock) {} }
final class PhaseBAction { public function run(PhaseBConsumer $consumer, string $name = 'default'): string { return $consumer->clock->value().':'.$name; } }
final class PhaseBCycleA { public function __construct(public PhaseBCycleB $b) {} }
final class PhaseBCycleB { public function __construct(public PhaseBCycleA $a) {} }

$c = new ServiceContainer();
$c->singleton(PhaseBClock::class, PhaseBSystemClock::class);
$c->alias('clock', PhaseBClock::class);
$c->tag([PhaseBClock::class], 'diagnostics');

assert($c->get(PhaseBClock::class) === $c->get('clock'));
assert($c->get(PhaseBConsumer::class)->clock instanceof PhaseBSystemClock);
assert($c->call([PhaseBAction::class, 'run'], ['name'=>'phase-b']) === 'ok:phase-b');
assert(count($c->tagged('diagnostics')) === 1);
assert($c instanceof ContainerInterface);

$cycle=false;
try { $c->get(PhaseBCycleA::class); } catch (ContainerException $e) { $cycle=str_contains($e->getMessage(),'Zirkuläre'); }
assert($cycle === true);

echo "dependency_injection: OK\n";
