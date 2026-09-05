# Phase R – Modul-Lifecycle und Hooks

Module können in `module.json` Lifecycle-Handler deklarieren. Unterstützt werden `install`, `postInstall`, `update`, `postUpdate`, `enable`, `disable`, `uninstall`, `beforeMigration`, `afterMigration`, `beforeRequest`, `afterRequest`, `beforeApi` und `afterApi`.

Jeder Hook erzeugt `module.<hook>.started`, `module.<hook>.finished` oder `module.<hook>.failed` über die bestehende Event-Plattform. Laufzeiten und Fehler werden als JSON-Lines unter `DataForm5-Core/storage/logs/modules/<hook>.log` protokolliert.

Handler sind Container-auflösbare Klassen und können entweder `__invoke()` oder `handle()` bereitstellen. Der Module-PackageInstaller führt Lifecycle-Hooks bei Install/Update/Enable/Disable/Uninstall aus; Route- und API-Dispatcher führen Request/API-Hooks aus.
