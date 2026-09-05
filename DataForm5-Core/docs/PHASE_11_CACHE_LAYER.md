# Phase 11 – Cache Layer

Build **0011** ergänzt den produktneutralen Core um eine einheitliche Cache-Schnittstelle.

## Bestandteile

- `CacheInterface` als stabiler Vertrag für Produkte und Module
- `FileCache` mit atomarem Schreiben, SHA-256-Dateinamen, TTL und Namespace-Trennung
- `ArrayCache` für Tests und kurzlebige Prozesse
- `CacheManager` für konfigurierbare Stores
- `remember()` zum einmaligen Berechnen und Zwischenspeichern
- `prune()` zur Bereinigung abgelaufener oder beschädigter Dateieinträge
- Service-Container-Bindung über `CacheServiceProvider`

## Verwendung

```php
$cache = $kernel->container()->get(\DataForm5\Cache\Contracts\CacheInterface::class);
$cache->set('project.42', $project, 300);
$project = $cache->remember('project.42', 300, fn () => loadProject(42));
```

Der Datei-Cache liegt standardmäßig unter `storage/framework/cache/data`. Produktdaten und produktbezogene Regeln gehören nicht in den Core.
