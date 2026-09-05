# RC1.7.10-dev – Phase J: Modul-Migrationssystem

Phase J ergänzt die Modulplattform um versionierte Datenbankmigrationen.

## Neue Kernkomponenten

- `DataForm5-Core/system/modules/Migrations/ModuleMigrationRepository.php`
- `DataForm5-Core/system/modules/Migrations/ModuleMigrationManager.php`

## Integration

Der Paketinstaller führt bei Installation und Update automatisch alle ausstehenden Migrationen eines Moduls aus. Die Enterprise-Modulverwaltung zeigt den Migrationsstatus an.

Das Module SDK erzeugt für neue Module automatisch `database/migrations/`.
