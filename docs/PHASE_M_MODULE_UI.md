# Phase M – Modul-Menüs und UI-Integration

Module können UI-Einträge deklarativ in `module.json` registrieren.

```json
"ui": {
  "navigation": [{"label":"Berichte","href":"modules/reports/index.php","capability":"reports.view","priority":100}],
  "dashboard": [{"label":"Berichte","href":"modules/reports/index.php","description":"Berichte öffnen","capability":"reports.view","priority":100}]
}
```

Nur aktivierte Module und für den angemeldeten Benutzer erlaubte Capabilities werden gerendert.
