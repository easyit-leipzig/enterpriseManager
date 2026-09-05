# Phase 18 – Modulpaket-Installer

## Paketstruktur

```text
MeinModul.zip
└── MeinModul/
    ├── module.json
    ├── src/
    └── migrations/
        └── 001_install.sql
```

`module.json` benötigt mindestens `key`, `name` und eine semantische `version`. Der Installer akzeptiert nur kontrollierte Dateitypen, verhindert Pfadtraversierung und zeigt vor jeder Installation eine Vorschau. Updates sichern den vorhandenen Modulordner und stellen ihn bei einem Fehler wieder her.
