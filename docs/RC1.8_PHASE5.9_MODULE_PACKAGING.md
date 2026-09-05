# RC1.8 Phase 5.9 – SDK-Paketierung

`php easyit module:package <slug>` erzeugt aus einem erfolgreich validierten Modul ein installationsfähiges ZIP.

## Eigenschaften

- Vor jeder Paketierung läuft zwingend `module:validate`.
- Das ZIP beginnt mit `root/`.
- `root/PACKAGE_MANIFEST.json` enthält Dateigrößen und SHA-256-Prüfsummen.
- Für das Gesamtpaket wird zusätzlich `<paket>.zip.sha256` erzeugt.
- Ungültige Module werden abgewiesen.
- Standardziel ist `storage/module-packages/`.
- `ZipArchive` wird verwendet, wenn ext-zip verfügbar ist.
- Fehlt ext-zip, verwendet easyIT automatisch `PharData` als ZIP-Backend.

Damit funktioniert die SDK-Paketierung auch auf PHP/XAMPP-Installationen ohne ext-zip, sofern die standardmäßig verfügbare Phar-Erweiterung aktiv ist.
