# Phase S – Hintergrundprozesse und Worker-System

Phase S verbindet Module mit den bereits vorhandenen DataForm5-Core-Systemen
`Queue` und `Scheduler`.

## Modulmanifest

```json
{
  "jobs": {
    "reports.cleanup": {
      "name": "reports.cleanup",
      "handler": "EasyIT\\Modules\\Reports\\Jobs\\CleanupJob",
      "queue": "file",
      "max_attempts": 3,
      "retry_delay": 10
    }
  },
  "schedules": {
    "reports.hourly-cleanup": {
      "name": "reports.hourly-cleanup",
      "job": "reports.cleanup",
      "cron": "0 * * * *",
      "queue": "file",
      "payload": {},
      "without_overlapping": true,
      "lock_ttl": 3600
    }
  }
}
```

## Job-Handler

Ein Modul-Handler benötigt keine eigene Queue-Serialisierung:

```php
final class CleanupJob
{
    public function handle(array $payload): void
    {
        // Arbeit ausführen
    }
}
```

## CLI

```bash
php tools/module-background-status.php
php tools/module-job-dispatch.php reports.cleanup '{"project":12}' 0 file
php tools/module-queue-work.php file 100
php tools/module-schedule-run.php
```

Für produktive Zeitpläne wird `module-schedule-run.php` typischerweise einmal
pro Minute über den Betriebssystem-Scheduler/Cron gestartet.

## Eigenschaften

- nutzt den vorhandenen QueueManager/QueueWorker
- nutzt den vorhandenen Scheduler und dessen Lock-/History-System
- Retry und Delay pro Modul-Job
- Overlap-Schutz pro Zeitplan
- Ereignisse `module.job.started`, `.finished`, `.failed`
- JSONL-Protokoll unter `DataForm5-Core/storage/logs/modules/background.log`
- keine parallele Queue- oder Cron-Engine
