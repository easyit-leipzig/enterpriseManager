# RC1.7.4-dev – Phase D: gemeinsame Modulplattform

## Ziel

DataForm5-Core und easyIT Enterprise verwenden ab diesem Stand eine gemeinsame Modul-Discovery.

## Suchpfade

1. `DataForm5-Core/modules/` – technische Core-Erweiterungen
2. `modules/` – produktübergreifende Enterprise-Erweiterungen

Beide Pfade werden vom selben `ModuleManager` verarbeitet. Modulnamen müssen über beide Bereiche hinweg eindeutig sein.

## Neue Diagnose

`app/developer/modules.php`

Die Seite zeigt:

- entdeckte Module,
- aktivierte und geladene Module,
- Abhängigkeiten,
- fehlende Abhängigkeiten,
- tatsächliche Modulpfade.

## API-Erweiterungen

`ModuleManager` unterstützt nun:

- `discoverMany(array $paths)`
- `scanPaths()`
- `discovered()`
- `status()`

Die bisherige Methode `discover(string $path)` bleibt kompatibel.

## Modulformat

Jedes Modul besitzt einen eigenen Ordner mit mindestens `module.json`. Die Entry-Klasse implementiert `DataForm5\\Modules\\Contracts\\ModuleInterface`.
