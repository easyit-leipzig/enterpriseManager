# Phase 18 – Internationalisierung und Lokalisierung

Diese Phase ergänzt den DataForm5-Core um einen produktneutralen Übersetzungs- und Formatierungsdienst.

## Bestandteile

- PHP-Übersetzungsdateien unter `resources/lang/<locale>/`
- Punktnotation (`messages.project.created`)
- Platzhalter `:name` und `{name}`
- Fallback-Locale
- Laufzeitwechsel der Sprache
- localeabhängige Zahlen-, Währungs- und Datumsformatierung
- Nutzung von `ext-intl`, sofern vorhanden, mit PHP-Fallback

## Verwendung

```php
$translator->get('messages.welcome', ['name' => 'Olaf']);
$translator->setLocale('en_US');
$formatter->currency(1234.50, 'EUR');
```
