# RC1.8-FC1-HF23 – Data Sources Workspace

Der bisherige Platzhalter „Datenquellen“ wurde durch eine echte projektbezogene Datenquellenverwaltung ersetzt.

## Unterstützte Quellen
- MySQL / MariaDB
- SQLite
- CSV-Engine
- Oracle

## Funktionen
- Datenquelle anlegen
- bearbeiten
- aktivieren/deaktivieren
- Verbindung testen
- löschen
- letzte Testergebnisse anzeigen
- interne Projekt-Datenbank als Systemquelle anzeigen

Kennwörter werden getrennt vom `config_json` mit AES-256-GCM gespeichert. Für Kennwörter wird `DATAFORM_APP_KEY` oder `APP_KEY` benötigt. Die bestehenden DataForm5-Core-Adapter werden für Verbindungstests über `DatabaseFactory` verwendet.

Für neue Projekte wird `installer/schema/project/003_data_sources.php` ausgeführt; bei bestehenden Projekten wird die Tabelle beim Öffnen des Workspaces idempotent sichergestellt.
