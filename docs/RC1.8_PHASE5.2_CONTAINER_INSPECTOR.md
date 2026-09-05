# RC1.8 Phase 5.2 – Service Container Inspector

Phase 5.2 erweitert den Developer Mode um eine dedizierte Containeranalyse.

## Web

`app/developer/container.php`

Anzeige:

- Service-ID
- Singleton/shared
- im aktuellen Request bereits resolved
- Tags
- Aliases
- Constructor-Parameter
- typisierte Service-Abhängigkeiten
- Filter nach Service/Typ/Alias/Tag

Die Dependency-Analyse verwendet Reflection und instanziiert dabei keine
zusätzlichen Services.

## CLI

```bash
php tools/developer-container.php
php tools/developer-container.php Queue
```

## Sicherheit

Die Seite ist nur sichtbar, wenn der Developer Mode aktiv ist und der Benutzer
`developer.view` besitzt.
