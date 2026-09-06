# Phase 9 – Projekt-Assistent

Phase 9 ergänzt den Assistant-Core um `project.create`.

## Ziele

- neue easyIT-Enterprise-Projekte strukturiert anlegen
- Projekt-ID sicher aus Projektname ableiten oder explizit festlegen
- Datenhaltung MySQL, SQLite, CSV oder Oracle wählen
- lokale SQLite-/CSV-Pfade automatisch innerhalb des Projektes erzeugen
- externe MySQL-/Oracle-Verbindungen nur referenzieren; keine Klartextkennwörter speichern
- Verzeichnisstruktur unter `projects/<projekt-id>/` erzeugen
- vorhandene Projektverzeichnisse niemals überschreiben
- `config/project.json`, `config/datasource.json` und `README.md` erzeugen
- Datenquellen-Assistent mit einem Startprofil vorbelegen
- Ablauf: Projekt-Assistent → Datenquellen-Assistent → DataForm-Assistent

## Standardstruktur

```text
projects/<projekt-id>/
├── config/
│   ├── project.json
│   └── datasource.json
├── data/
├── storage/
├── logs/
├── backups/
└── README.md
```

SQLite liegt standardmäßig unter `projects/<projekt-id>/data/<projekt-id>.sqlite`.
CSV liegt standardmäßig unter `projects/<projekt-id>/data/csv/<projekt-id>/`.

## Sicherheitsregeln

- keine Pfade außerhalb von `projects/`
- kein `..` im Projektpfad
- bestehende Projektordner werden nicht überschrieben
- Projektanlage erst nach expliziter Bestätigung
- keine Klartextkennwörter im Projektentwurf oder Datenquellen-Seed
