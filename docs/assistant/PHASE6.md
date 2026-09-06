# Assistant Phase 6 – Datenquellen-Assistent

## Ziel

Phase 6 ergänzt den zentralen Assistenten um eine einheitliche Konfiguration und Prüfung der DataForm5-Datenquellen.

Unterstützt werden:

- MySQL / MariaDB
- SQLite
- CSV-Engine
- Oracle

Der Assistent `datasource.configure` führt in vier Schritten durch:

1. Profilname und Treiber
2. Verbindungsdaten + realer Verbindungstest
3. Erkennung und Auswahl von Tabelle/View/CSV-Tabelle
4. Review, JSON-Export und Übergabe an den DataForm-Assistenten

## Sicherheitsregel

Kennwörter werden nur transient aus dem aktuellen POST-Request für den Verbindungstest verwendet.

Sie werden NICHT gespeichert in:

- AssistantStateStore
- Review-Ausgabe
- JSON-Export
- DataForm-Handoff

Stattdessen kann optional `passwordRef` verwendet werden, z. B. der Name einer ENV-Variable.

## Datenquellen-Adapter

Die Assistenten-Laufzeit verwendet `DataSourceAdapterInterface` für Verbindungstest und Source-Discovery.

Implementierungen:

- `MySqlDataSourceAdapter`
- `SQLiteDataSourceAdapter`
- `CsvDataSourceAdapter`
- `OracleDataSourceAdapter`

## Einheitlicher DataForm-Datenbankvertrag

`DataFormDatabaseInterface` beschreibt die stabile DataForm-Seite der Datenbankabstraktion:

- `connect()`
- `tables()`
- `query()`
- `insert()`
- `update()`
- `delete()`

`CoreDatabaseBridge` kann eine bereits vorhandene Enterprise-/DataForm5-Core-Datenbankimplementierung kapseln, sofern sie diese Operationen bereitstellt. Dadurch überschreibt das Overlay die bestehende Core-Datenbankschicht nicht.

## CSV-Regeln

Für die CSV-Engine gelten verbindlich:

- Datenbank = Ordner
- Tabellen = `*.csv`
- Headerzeile erforderlich
- Standard-Trennzeichen `|`
- Pflichtfeld `id`
- Tabellen ohne `id` werden erkannt, aber nicht als gültige DataForm-Hauptquelle akzeptiert

## Handoff an DataForm-Assistent

Nach erfolgreichem Review erzeugt der Datenquellen-Assistent einen Handoff-Link zum DataForm-Assistenten.

Übernommen werden:

- Profilname
- Treiber
- ausgewählte Hauptquelle

Nicht kopiert werden Verbindungskennwörter. Das DataForm referenziert die Verbindung als:

`profile:<profilname>`

Beispiel:

`profile:project-main`

## JSON-Schema

Export-Schema:

`easyit.datasource.assistant.v1`

Der Export enthält zusätzlich die dokumentierte Core-Contract-Schnittstelle und die Secret-Policy.
