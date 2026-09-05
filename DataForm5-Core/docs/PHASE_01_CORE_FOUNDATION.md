# Phase 1 – Core Foundation

Diese Phase schreibt das technische Fundament von `DataForm5-Core` verbindlich fest.

## Enthalten

- PSR-4-ähnlicher Core-Autoloader ohne externe Abhängigkeiten
- zentraler `Kernel`
- Dependency-Injection- und Service-Container
- Service-Provider-Vertrag
- zentrale Pfadauflösung
- PHP-basierter Konfigurationsloader
- Bootstrap-Einstiegspunkt
- automatischer Selbsttest

## Verbindliche Regel

Produkte und Module greifen künftig über den Kernel und den Service-Container auf Core-Dienste zu. Direkte globale Initialisierung und unkontrollierte Einzel-Includes sind zu vermeiden.

## Einstieg

```php
$kernel = require '/pfad/easyIT-Enterprise/DataForm5-Core/bootstrap/app.php';
$container = $kernel->container();
```
