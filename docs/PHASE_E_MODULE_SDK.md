# Phase E – Einheitliches Modul-SDK

## Ziel

Neue Enterprise-Module werden reproduzierbar mit derselben Struktur erzeugt und vor der Nutzung validiert.

## Standardstruktur

```text
modules/<modul>/
├── module.json
├── bootstrap.php
├── src/
├── config/
├── resources/
├── tests/
└── README.md
```

## Modul erzeugen

```bash
php tools/create-module.php report-export EasyIT\Modules\ReportExport ReportExportModule "Exportiert Berichte"
```

## Modul prüfen

```bash
php tools/validate-module.php modules/report-export
```

## SDK

- `DataForm5\Modules\SDK\AbstractModule` – Basisklasse mit optionalen Lifecycle-Methoden.
- `ModuleScaffolder` – erzeugt die Standardstruktur.
- `ModuleValidator` – prüft Manifest und Pflichtstruktur.

Die in Phase D eingeführte gemeinsame Registry entdeckt anschließend neue Enterprise-Module automatisch aus `modules/`.
