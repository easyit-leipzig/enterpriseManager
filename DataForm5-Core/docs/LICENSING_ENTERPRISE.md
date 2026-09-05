# Enterprise-Core: Lizenzverwaltung

Die Lizenzierungs-Engine liegt bereits im gemeinsamen `DataForm5-Core/system/licensing/`.
Dieses Modul ergänzt die persistente Verwaltung für easyIT Enterprise.

## Core

- `DataForm5-Core/system/licensing/Core/PdoLicenseProvider.php`
- `DataForm5-Core/system/licensing/Core/LicenseRegistry.php`

## Enterprise-Anwendung

- `app/licensing/index.php`
- Navigationseintrag in `system/ui/layout.php`
- PDO-Provider-Verknüpfung in `system/app/bootstrap.php`

## Administrationsdatenbank

- `installer/schema/admin/004_licensing.php`
- Tabelle `enterprise_licenses`

## Overlay-Paketstandard

Modulpakete besitzen künftig **genau einen obersten Ordner `root/`**.
Unter `root/` liegen die Dateien bereits mit ihrem endgültigen relativen Pfad.
Zum Installieren wird ausschließlich der Inhalt von `root/` in das bestehende
easyIT-Enterprise-Hauptverzeichnis kopiert.
