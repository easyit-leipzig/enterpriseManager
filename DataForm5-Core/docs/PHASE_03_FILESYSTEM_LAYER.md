# Phase 3 – Filesystem Layer

Build: `DataForm5-Core build0003`

## Ziel

Eine zentrale, sichere Dateisystemschicht für alle easyIT-Produkte. Produktcode greift nicht mehr direkt auf `file_get_contents`, `file_put_contents`, `copy`, `rename` oder rekursive Löschroutinen zu.

## Komponenten

- `Filesystem`: Lesen, atomisches Schreiben, Anhängen, Kopieren, Verschieben, Löschen, Listen und SHA-Prüfsummen.
- `Storage`: auf einen erlaubten Wurzelordner begrenzter Datenträger.
- `PathGuard`: verhindert Pfadflucht über absolute Pfade und `..`.
- `ZipManager`: ZIP-Erzeugung und sichere Extraktion; benötigt `ext-zip`.
- `BackupManager`: Verzeichnis- oder ZIP-Backups mit Zeitstempel.
- `FilesystemServiceProvider`: Einbindung in Kernel und Service-Container.

## Nutzung

```php
$kernel = require __DIR__ . '/bootstrap/app.php';
$storage = $kernel->container()->get(\DataForm5\Core\Filesystem\Storage::class);
$storage->write('projects/demo/config.json', '{}');
```

## Sicherheitsregeln

- Storage akzeptiert nur relative Pfade innerhalb seines Roots.
- Schreibvorgänge sind standardmäßig atomisch.
- ZIP-Extraktion blockiert absolute Pfade und Traversal-Einträge.
- Backups liegen ausschließlich unter `storage/backups`.
