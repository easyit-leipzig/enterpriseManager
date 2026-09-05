# Phase 16 – Scheduler- und Task-Layer

Der Scheduler führt registrierte Core-Aufgaben anhand fünfteiliger Cron-Ausdrücke aus.

## Funktionen

- Cron-Felder für Minute, Stunde, Tag, Monat und Wochentag
- Listen, Bereiche und Schrittweiten (`*/15`, `1-5`, `1,3,5`)
- benannte Tasks und komfortable Intervalle
- Dateisperren gegen parallele Doppelstarts
- automatische Freigabe veralteter Sperren
- tägliche JSONL-Laufhistorie mit Erfolgs- und Fehlerstatus
- Integration in Kernel und Service-Container

## Beispiel

```php
$scheduler->task('backups.daily', '0 2 * * *', function ($container): void {
    // Backup durchführen
})->withoutOverlapping(7200);

$scheduler->runDue();
```

Für den Serverbetrieb wird `runDue()` üblicherweise einmal pro Minute durch den System-Cron aufgerufen.
