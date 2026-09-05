<?php
declare(strict_types=1);
require __DIR__ . '/system/app/bootstrap.php';
$seen=[];
enterprise_event_listen('phasea.test', function(\DataForm5\Events\Core\NamedEvent $event) use (&$seen): void { $seen[]='low:'.$event->payload()['value']; }, 10);
enterprise_event_listen('phasea.test', function(\DataForm5\Events\Core\NamedEvent $event) use (&$seen): void { $seen[]='high:'.$event->payload()['value']; }, 100);
$event=enterprise_event_dispatch('phasea.test',['value'=>42,'token'=>'must-not-leak']);
if ($seen !== ['high:42','low:42']) throw new RuntimeException('Listener-Reihenfolge fehlerhaft.');
if ($event->name() !== 'phasea.test') throw new RuntimeException('NamedEvent-Name fehlerhaft.');
if (!enterprise_event_catalog()->has('dataform.record.created')) throw new RuntimeException('Katalog unvollständig.');
$log=__DIR__.'/storage/logs/events.log';
if (!is_file($log)) throw new RuntimeException('Diagnoselog fehlt.');
$last=(string)array_slice(file($log,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [],-1)[0];
if (str_contains($last,'must-not-leak')) throw new RuntimeException('Secret-Redaktion fehlgeschlagen.');
echo "PASS RC1.7 Phase A Event Platform\n";
