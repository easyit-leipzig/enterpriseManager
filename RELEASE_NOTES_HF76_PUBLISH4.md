# RC1.8-FC1-HF76 PUBLISH4

## Projekt-Anwenderexport als schlanke HTML5-DataForm-Anwendung

Der Projekt-Export erzeugt ab PUBLISH4 kein vollständiges easyIT-Enterprise-Bundle mehr. Stattdessen wird aus jedem Projekt eine eigenständige, minimale DataForm-Anwendung erzeugt.

Das exportierte Projektpaket enthält ausschließlich:

- `index.php` als Projektstartseite,
- `setup.php` für die MySQL/MariaDB-Anbindung und den Import des Projektsnapshots,
- `lib/bootstrap.php` und `lib/DataFormApp.php` als minimale Runtime,
- `media.php` für geschützte Datei-/Bildauslieferung,
- je DataForm genau eine Seite unter `dataforms/`,
- nur die für die Runtime benötigten CSS-/JavaScript-Dateien,
- nur die tatsächlich verwendeten Button-/Logo-PNGs,
- nur die Filesystem-Medien des exportierten Projekts,
- `install/database.sql`, `project.json` und `manifest.json`.

Nicht enthalten sind Enterprise-Dashboard, Projekt-/Benutzer-/Lizenzverwaltung, Developer-Werkzeuge, Tests, Hotfix-Historie, Logs, Backups oder andere Projekte.

Die erzeugten DataForm-Seiten sind HTML5-Seiten und unterstützen CRUD, Mehrfachlöschen, Suche, Paginierung, Datensatzzeiger, physische Tabellenbindungen, `dataform_records`, Lookup-/Auswahlfelder, `derived_multienum`, JSON, Link, Koordinaten, Passwortfelder sowie Datei-/Bildspeicherung.
