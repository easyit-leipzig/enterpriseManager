# Phase 15 – Queue- und Job-Layer

Build 0015 ergänzt DataForm5-Core um eine produktneutrale Warteschlangenschicht.

## Bestandteile

- `JobInterface` und wiederverwendbares `AbstractJob`
- `QueueInterface`
- synchroner Treiber für unmittelbare Ausführung
- dateibasierter Treiber für persistente Hintergrundaufgaben
- verzögerte Jobs
- konfigurierbare Wiederholungsversuche und Wiederholungsverzögerungen
- atomare Übernahme von wartenden in laufende Jobs
- Ablage endgültig fehlgeschlagener Jobs mit Fehlerklasse, Meldung und Stacktrace
- `QueueManager`, `QueueDispatcher` und `QueueWorker`
- Service-Container-Integration

## Verwendung

```php
use DataForm5\Queue\Core\AbstractJob;
use DataForm5\Queue\Core\QueueDispatcher;

final class ExportProject extends AbstractJob
{
    public function handle(\DataForm5\Core\Container\ServiceContainer $container): void
    {
        $projectId = (int)$this->payload()['project_id'];
        // Export ausführen
    }
}

$dispatcher = $kernel->container()->get(QueueDispatcher::class);
$dispatcher->dispatch(new ExportProject(['project_id' => 42]));
```

Für dauerhafte Warteschlangen wird `QUEUE_CONNECTION=file` gesetzt. Ein Worker verarbeitet Jobs über `QueueWorker::work()` oder gezielt über `runNext()`.
