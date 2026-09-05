# Phase G – grafische Modulverwaltung

## Ziel

Enterprise-Module können im Adminbereich ohne CLI installiert, aktualisiert, aktiviert, deaktiviert und entfernt werden.

## Oberfläche

`app/modules/index.php`

## Sicherheit

- Admin-Rolle erforderlich
- CSRF-Schutz
- Uploadlimit 20 MB
- ausschließlich ZIP-Dateien
- verifizierter HTTP-Upload
- bestehende Phase-F-Validierung und sichere Extraktion
- Audit- und Event-Einträge für Änderungen

## Navigation

Die Hauptnavigation enthält ab Phase G den Eintrag **Module**.
