# Phase O – Modul-HTTP-Abstraktion

## Ziel

Modul-Controller arbeiten mit `DataForm5\Http\Core\Request` und `Response` statt direkt mit `$_GET`, `$_POST` oder untypisierten Arrays.

## Request

Unterstützt Query-, Body-, JSON- und Datei-Eingaben sowie Attribute für den aktuellen Benutzer und die aufgelöste Modulroute. `Request::validate()` bindet die bestehende DataForm5-Validation-Schicht ein.

```php
public function store(Request $request, Validator $validator): Response
{
    $data = $request->validate($validator, [
        'name' => 'required|string|min:2|max:120',
        'email' => 'nullable|email',
    ]);

    return Response::json(['saved' => true, 'data' => $data], 201);
}
```

## Response

- `Response::page()` – Inhalt wird in das Enterprise-Layout eingebettet.
- `Response::html()` / `text()` – direkte HTTP-Antwort.
- `Response::json()` – JSON-Antwort.
- `Response::redirect()` – Redirect.
- `Response::noContent()` – leere Antwort.

## Validierungsfehler

Für JSON-Requests antwortet der Dispatcher mit HTTP 422 und strukturierten Fehlern. Für normale Seiten wird eine Enterprise-Fehlermeldung als Page-Response erzeugt.

## Rückwärtskompatibilität

Phase-N-Controller mit `array $request` und Array-Rückgaben bleiben vorerst lauffähig. Das SDK erzeugt ab Phase O ausschließlich typisierte Controller.
