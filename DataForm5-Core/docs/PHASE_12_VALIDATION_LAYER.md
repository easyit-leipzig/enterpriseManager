# Phase 12 – Validation Layer

Build 0012 ergänzt eine produktneutrale Validierungsschicht unter `system/validation/`.

## Bestandteile

- zentraler `Validator` im Service-Container
- Regeln als Zeichenkette oder Array
- verschachtelte Felder über Punktnotation
- `nullable`, `required`, Typ-, Größen-, Vergleichs- und Formatregeln
- `ErrorBag` und `ValidationResult`
- benutzerdefinierte Regeln über `RuleInterface`, `CallbackRule` oder `extend()`
- konfigurierbare deutsche Standardmeldungen

## Beispiel

```php
$validator = $kernel->container()->get(\DataForm5\Validation\Core\Validator::class);
$result = $validator->validate($input, [
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
]);
if ($result->fails()) {
    $errors = $result->errors()->all();
}
$data = $result->validated();
```
