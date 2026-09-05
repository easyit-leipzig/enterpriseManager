<?php
declare(strict_types=1);
use DataForm5\Events\Contracts\EventSubscriberInterface;
use DataForm5\Events\Core\Event;
use DataForm5\Events\Core\EventDispatcher;
$kernel = require dirname(__DIR__) . '/bootstrap/app.php';
$dispatcher = $kernel->container()->get(EventDispatcher::class);
final class BuildEvent extends Event { public array $log = []; }
$dispatcher->listen(BuildEvent::class, static function(BuildEvent $event): void { $event->log[] = 'low'; }, 0);
$dispatcher->listen(BuildEvent::class, static function(BuildEvent $event): void { $event->log[] = 'high'; }, 100);
$event = $dispatcher->dispatch(new BuildEvent());
if ($event->log !== ['high', 'low']) throw new RuntimeException('Priorisierung fehlgeschlagen.');
final class StopEvent extends Event { public array $log = []; }
$dispatcher->listen(StopEvent::class, static function(StopEvent $event): void { $event->log[]='first'; $event->stopPropagation(); }, 10);
$dispatcher->listen(StopEvent::class, static function(StopEvent $event): void { $event->log[]='second'; });
$stop = $dispatcher->dispatch(new StopEvent());
if ($stop->log !== ['first']) throw new RuntimeException('StopPropagation fehlgeschlagen.');
final class BuildSubscriber implements EventSubscriberInterface {
    public static function getSubscribedEvents(): array { return ['build.named' => ['onBuild', 5]]; }
    public function onBuild(BuildEvent $event): void { $event->log[]='subscriber'; }
}
$dispatcher->subscribe(new BuildSubscriber());
$named = $dispatcher->dispatch(new BuildEvent(), 'build.named');
if ($named->log[0] !== 'subscriber') throw new RuntimeException('Subscriber fehlgeschlagen.');
echo "PASS: Event System\n";
