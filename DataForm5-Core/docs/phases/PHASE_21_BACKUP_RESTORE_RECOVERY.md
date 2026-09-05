# Phase 21 – Backup-, Restore- und Recovery-Layer

Phase 21 ergänzt den DataForm5-Core um versionierte, überprüfbare Sicherungen. Jede Sicherung enthält ein JSON-Manifest, SHA-256-Prüfsummen aller Dateien, Metadaten und einen Integritätshash. Restore-Vorgänge werden in einem Staging-Verzeichnis vorbereitet und erst nach erfolgreicher Prüfung atomar aktiviert.

## Kernkomponenten

- `BackupStoreInterface`
- `FileBackupStore`
- `RecoveryManifest`
- `RecoveryManager`
- `RecoveryServiceProvider`

## Verwendung

```php
$recovery = $kernel->container()->get(\DataForm5\Recovery\Core\RecoveryManager::class);
$backup = $recovery->backup($projectPath, 'project-42', ['project_id' => 42]);
$recovery->restore($backup['id'], $restorePath);
```

Die Aufbewahrungszahl wird mit `RECOVERY_RETAIN` gesteuert.
