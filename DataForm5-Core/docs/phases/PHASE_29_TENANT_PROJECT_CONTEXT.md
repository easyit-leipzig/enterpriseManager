# Phase 29 – Mandanten-, Projektkontext- und Isolation-Layer

Build 0029 führt einen produktneutralen aktiven Kontext für Mandant und Projekt ein. Alle Produkte und Module können damit Speicherpfade, Cache-Schlüssel und Datenbankverbindungen eindeutig einem Projekt zuordnen.

## Verbindliche Regeln

1. Projektbezogene Operationen müssen einen aktiven `ProjectContext` verwenden.
2. Mandanten- und Projekt-IDs werden validiert und dürfen keine Pfadbestandteile einschleusen.
3. Temporäre Kontextwechsel erfolgen ausschließlich über `ContextManager::run()`; der vorherige Kontext wird auch bei Exceptions wiederhergestellt.
4. Datenbank-, Cache-, Audit- und Storage-Zugriffe müssen künftig mit dem aktiven Kontext gekapselt werden.
5. Ein Zugriff auf einen fremden Mandanten oder ein fremdes Projekt ist über `assertMatches()` zu blockieren.

## Beispiel

```php
$context = new ProjectContext('kunde-a', 'projekt-42');
$contexts->run($context, function () use ($contexts): void {
    $path = $contexts->scopedPath('exports/project.zip');
});
```
