# Phase U – Enterprise Monitoring & Health Center

Phase U ergänzt den bestehenden Queue-/Scheduler-Betrieb um zentrale Health-Snapshots, Metriken, Heartbeats und Warnschwellen.

## Oberfläche
`app/monitoring/index.php`

## JSON
`app/monitoring/api.php`

## CLI
```bash
php tools/monitoring-heartbeat.php worker-1
php tools/monitoring-snapshot.php
```

Die Queue- und Scheduler-CLI schreiben automatisch Heartbeats. Konfigurierbare Schwellwerte liegen in `DataForm5-Core/config/monitoring.php`. Snapshots werden unter `DataForm5-Core/storage/framework/monitoring/` abgelegt; Warnungen unter `DataForm5-Core/storage/logs/monitoring/alerts.log`.
