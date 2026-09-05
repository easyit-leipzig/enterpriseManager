# Phase Q – Modul-API und JSON-Endpunkte

Module deklarieren JSON-Endpunkte über `api_routes` in `module.json`.

```json
{
  "api_routes": [
    {
      "name": "reports.api.status",
      "methods": ["GET"],
      "controller": "EasyIT\\Modules\\Reports\\Http\\ApiController@status",
      "capability": "reports.view"
    }
  ]
}
```

Zentraler Aufruf: `app/module-api.php?route=reports.api.status`

Die API verwendet die vorhandenen DataForm5-Core-Klassen `Request`, `Response`,
`ApiResponse`, Validation und CSRF. Unterstützt werden Capability-Prüfung,
HTTP-Methodenprüfung, HTTP 422 bei Validierungsfehlern sowie einheitliche
JSON-Fehlercodes. Direkte oder URL-konstruierte PHP-Includes sind nicht nötig.
