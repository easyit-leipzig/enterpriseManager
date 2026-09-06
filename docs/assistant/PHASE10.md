# Assistant Phase 10 – Backup, Restore und Recovery

Phase 10 erweitert das kumulative easyIT-Enterprise/DataForm5-Assistant-System um `project.recovery`.

## Backup

- Projekt wird ausschließlich unter `projects/<project-id>/` gelesen.
- Projekt-ZIP wird unter `backups/projects/` erzeugt.
- Jedes ZIP enthält `easyit-backup-manifest.json`.
- Das Manifest enthält SHA-256 und Größe jeder gesicherten Datei.
- Zusätzlich wird für das vollständige ZIP eine `.sha256`-Datei erzeugt.
- Projektinterne alte Backups werden standardmäßig nicht rekursiv mit eingepackt.
- Symbolische Links werden aus Sicherheitsgründen übersprungen.

### Projektdatenbank

`Projektdatenbank mitsichern` ist standardmäßig aktiv.

- CSV: lokaler CSV-Datenbankordner wird physisch mitgesichert.
- SQLite: lokale SQLite-Datei wird physisch mitgesichert.
- MySQL/Oracle: externe Datenbank wird nur physisch mitgesichert, wenn `config/datasource.json` einen existierenden, sicheren `backup.dumpPath` innerhalb des Enterprise-Verzeichnisses enthält. Ohne Dump-Pfad wird eine Warnung ausgegeben.
- Externe SQL-Dumps werden beim Restore nicht automatisch importiert. Sie werden unter `backups/database/` des restaurierten Projekts abgelegt.

## Restore

- Restore akzeptiert ausschließlich ZIP-Dateien.
- `easyit-backup-manifest.json` ist Pflicht.
- Schema muss `easyit.project.backup.v1` sein.
- Alle Manifest-Dateien werden vor dem Schreiben per SHA-256 geprüft.
- Absolute Pfade, `../` und Einträge außerhalb von `project/` bzw. `database/` werden abgewiesen.
- Bestehende Projekte werden niemals überschrieben.
- Optional kann unter einer neuen Projekt-ID restauriert werden.
- Bei neuer Projekt-ID werden `project.json`, Projektpfad und lokale Datenquellenpfade angepasst.
- Restore läuft zuerst in einem Staging-Verzeichnis und wird erst nach erfolgreicher Prüfung nach `projects/` verschoben.

## Recovery-Diagnose

Nach erfolgreichem Restore werden mindestens geprüft:

1. Projektverzeichnis vorhanden.
2. Standardverzeichnisse vorhanden bzw. wiederhergestellt.
3. `config/project.json` lesbar und Projekt-ID korrekt.
4. `config/datasource.json` lesbar.
5. Lokale Datenbank vorhanden, wenn sie laut Manifest enthalten sein sollte.
6. Restore wurde ohne Überschreiben eines vorhandenen Projekts durchgeführt.

Status: `PASS`, `FAIL`, `WARN`, `SKIP`.

## Download

`admin/assistants/download.php` liefert nur automatisch erzeugte Backup-ZIPs und zugehörige SHA-256-Dateien aus dem zentralen `backups/projects/`-Verzeichnis aus. Pfadnavigation ist nicht möglich.

## Tests

Phase 10 wird mit `tests/assistant/phase10_smoke.php` geprüft. Zusätzlich werden die Regressionstests Phase 6 bis Phase 9 ausgeführt und der vollständige Browser-/HTTP-Weg inklusive multipart ZIP-Upload getestet.
