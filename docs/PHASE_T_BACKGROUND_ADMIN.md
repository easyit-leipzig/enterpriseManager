# Phase T – grafische Job-/Scheduler-Verwaltung

Die Enterprise-Oberfläche enthält jetzt unter **Jobs** eine zentrale Verwaltung
für die in Phase S eingeführten Modul-Hintergrundprozesse.

## Funktionen

- Queue-Zähler für wartend, in Verarbeitung und fehlgeschlagen
- registrierte Modul-Jobs und Handler
- registrierte Scheduler-Tasks und Cron-Ausdrücke
- manuelles Einreihen eines Jobs mit JSON-Payload
- manuelles Abarbeiten der File-Queue
- Ausführen aktuell fälliger Scheduler-Tasks
- Liste fehlgeschlagener File-Queue-Jobs
- Retry oder Löschen fehlgeschlagener Jobs
- letzte Scheduler-Ausführungen und Fehler

## Rechte

- `background.view`: Status und Historie ansehen
- `background.manage`: Jobs starten, Worker/Scheduler ausführen, Retry/Löschen

Administratoren erhalten neue Core-Capabilities über `enterprise_upgrade()`
automatisch.

## Sicherheit

Alle schreibenden Webaktionen sind authentifiziert, capability- und
CSRF-geschützt. In der Weboberfläche wird bei fehlgeschlagenen Jobs absichtlich
kein Stacktrace ausgegeben.

## Produktiver Betrieb

Der Web-Button ersetzt keinen dauerhaften Worker. Für den regulären Betrieb:

```bash
php tools/module-schedule-run.php
php tools/module-queue-work.php file 100
```

`module-schedule-run.php` sollte üblicherweise einmal pro Minute durch Cron oder
den Windows Task Scheduler aufgerufen werden.
