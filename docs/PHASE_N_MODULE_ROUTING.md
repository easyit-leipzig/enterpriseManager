# Phase N – Modul-Routing und Controller-Integration

## Ziel

Module stellen ihre Admin- und Anwendungsseiten nicht mehr über direkt adressierbare PHP-Dateien bereit. Stattdessen deklarieren sie Routen im `module.json`. easyIT Enterprise löst Route, HTTP-Methode, Capability und Controller zentral auf.

## Manifest

```json
{
  "routes": [
    {
      "name": "reports.index",
      "methods": ["GET"],
      "controller": "EasyIT\\Modules\\Reports\\Http\\ModuleController@index",
      "capability": "reports.view",
      "title": "Berichte"
    }
  ]
}
```

UI-Einträge verweisen anschließend auf den zentralen Einstiegspunkt:

`app/module.php?route=reports.index`

## Komponenten

- `ModuleRouteRegistry`: sammelt Routen aktivierter Module und blockiert doppelte Routennamen.
- `ModuleRouteDispatcher`: prüft HTTP-Methode und Capability und ruft den registrierten Controller über den ServiceContainer auf.
- `app/module.php`: zentraler Web-Einstiegspunkt für Modulrouten mit Enterprise-Layout und einheitlicher Fehlerdarstellung.
- `ModuleScaffolder`: erzeugt neue Module bereits mit Route und `src/Http/ModuleController.php`.

## Sicherheitsprinzip

Die URL enthält ausschließlich einen registrierten Routennamen. Es werden keine Dateipfade aus Request-Parametern geladen. Controller müssen aus einem entdeckten, aktivierten Modul stammen. Capabilities werden vor dem Controller-Aufruf geprüft.

## Rückwärtskompatibilität

Bestehende Module ohne `routes` bleiben lauffähig. Direkte PHP-Seiten werden nicht automatisch entfernt; neue SDK-Module verwenden jedoch ausschließlich das zentrale Routing. Eine spätere Konsolidierungsphase kann bestehende direkte Modulseiten migrieren.
