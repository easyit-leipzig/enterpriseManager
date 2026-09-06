# Assistant Phase 23 – Migrationshistorie, Schema-Diff und Rollback

Phase 23 erweitert Phase 22 um nachvollziehbare Migrationsläufe mit eigener Run-ID, Vorher-/Nachher-Snapshots, kategorisiertem Schema-Diff, Fehlerdiagnose und kontrolliertem In-Place-Rollback.

## Assistent

`dataform.module-migration-history`

Ablauf:
1. Projekt auswählen.
2. Migrationsläufe mit Status und Diff-Anzahl anzeigen.
3. Run-ID auswählen und vollständigen Lauf prüfen.
4. Rollback nur nach exakter Projekt-ID-Bestätigung ausführen.
5. Restore- und Verifikationsdiagnose anzeigen.

## Run-Historie

Jede Phase-22-Ausführung erzeugt nun eine Run-ID wie:

`mig-20260905-083722-62c86fbb`

Gespeichert werden:

- Status `RUNNING`, `PASS`, `FAILED` oder `ROLLED_BACK`
- Start-/Endzeitpunkt
- kompakter Upgrade-/Migrationsplan
- alle geplanten Schritte
- Recovery-Checkpoint mit SHA-256
- Snapshot vor der Migration
- Snapshot nach Erfolg oder Fehler
- Schema-Diff
- ausgeführte Migrationsergebnisse
- fehlgeschlagener Schritt
- Fehlertext
- Rollback-Ergebnis und Verifikations-Diff

Speicherorte:

- `projects/<projekt>/config/assistant/module-migration-runs.json`
- `projects/<projekt>/config/assistant/module-migration-runs/<run-id>.json`

Schemas:

- `easyit.assistant.module-migration-runs.v1`
- `easyit.assistant.module-migration-run.v1`
- `easyit.assistant.module-migration-snapshot.v1`

## Schema-Snapshot

Erfasst werden:

- persistente DataForm-Grundkonfigurationen
- Felder, Beziehungen, Events und Aktionen
- Datenquellenkonfiguration
- CSV-Tabellen und Spalten
- SQLite-Tabellen und Spalten, wenn `pdo_sqlite` verfügbar ist
- MySQL-/Oracle-Schema lesend, wenn der jeweilige PDO-Treiber und die über `passwordRef` referenzierten Zugangsdaten verfügbar sind
- installierte Module und Versionen
- Projekt- und Datenquellenkonfiguration

Externe Datenbanken ohne verfügbaren PDO-Treiber oder ohne nutzbare Zugangsdaten werden als `SKIP` protokolliert; es wird kein vollständiger Schema-Snapshot vorgetäuscht.

## Schema-Diff

Jede Änderung enthält:

- JSON-Pfad
- Typ `added`, `removed` oder `changed`
- Kategorie `dataform`, `csv`, `sqlite`, `database`, `modules` oder `config`
- Vorher-Wert
- Nachher-Wert

Damit ist z. B. nachvollziehbar, dass ein Release sowohl eine CSV-Spalte als auch ein DataForm-Feld `status` hinzugefügt hat.

## Fehlerdiagnose

Fehlgeschlagene Migrationsläufe speichern den exakten Schritt:

- Modul-ID
- Migrations-ID
- Migrationstyp
- Phase `pre` oder `post`
- Fehlertext

Der Assistent zeigt daraus eine gezielte Diagnose und verweist auf den zugehörigen Recovery-Checkpoint.

## Kontrollierter Rollback

Vor dem Rollback wird zuerst ein zusätzliches Sicherheitsbackup des aktuellen Zustands erzeugt. Danach wird der ursprüngliche Phase-22-Checkpoint in ein isoliertes Staging extrahiert und atomar als aktives Projekt eingesetzt. Der ersetzte Zustand bleibt zunächst im Recovery-Bereich erhalten.

Der Rollback verlangt die exakte Projekt-ID als Bestätigung.

Nach dem Restore wird ein neuer Snapshot aufgenommen und gegen den ursprünglichen Vorher-Snapshot verglichen. Ein exakter lokaler Rollback ergibt `verificationDiff.count = 0`.

## Externe SQL-Datenbanken

Ein automatischer In-Place-Rollback wird für Migrationsläufe mit `database.sql` gegen externe MySQL-/Oracle-Datenbanken blockiert. Der Grund ist bewusst: Das Projekt-ZIP kann einen DB-Dump enthalten, importiert diesen aber nicht automatisch in eine externe Datenbank. Ein scheinbar vollständiger automatischer Rollback wäre daher fachlich falsch.

## Backup / Restore unter neuer Projekt-ID

Migrationshistorie und Run-Dateien werden mit dem Projektbackup gesichert. Bei Restore unter neuer Projekt-ID werden Projekt-IDs in der Historie retargetet. Alte In-Place-Checkpoints werden dabei ausdrücklich als `rollbackAvailable=false` markiert, weil sie weiterhin zum ursprünglichen Projekt gehören.

## Export

Die gesamte Historie kann als JSON exportiert werden. Zusätzlich kann ein einzelner Run über `run_id` exportiert werden.
