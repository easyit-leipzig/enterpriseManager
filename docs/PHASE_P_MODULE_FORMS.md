# Phase P – Modul-Formulare und CSRF/Input-Handling

- deklarative Formulare über `forms/*.php`
- `FormFactory::fromDefinition()` / `fromFile()`
- automatische CSRF-Felder im `FormRenderer`
- automatische CSRF-Prüfung für POST/PUT/PATCH/DELETE im Modul-Dispatcher
- Wiederbefüllung über `Form::bind()`
- Feldfehler über `FormResult` und `ErrorBag`
- SDK erzeugt ein funktionsfähiges Beispiel-Formular

Ungültige CSRF-Tokens werden mit HTTP 419 abgewiesen.
