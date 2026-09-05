# Phase 9 – Logging Layer

Build 0009 ergänzt den DataForm5-Core um eine produktneutrale Logging-Schicht.

## Bestandteile

- `LoggerInterface` mit den acht üblichen Log-Leveln
- JSON-Zeilenformat für maschinenlesbare Protokolle
- Kontextdaten und Platzhalter wie `{user_id}`
- Throwable-Normalisierung ohne unkontrollierte Objektserialisierung
- Mindest-Level je Kanal
- größenbasierte Rotation und Aufbewahrungsgrenze
- mehrere Kanäle, zum Beispiel `app`, `security` und `null`
- zentrale Bereitstellung über `LogManager` und den Service-Container

## Verwendung

```php
$logger = $kernel->container()->get(\DataForm5\Logging\Contracts\LoggerInterface::class);
$logger->info('Projekt {project} geöffnet.', ['project' => 12]);

$security = $kernel->container()->get(\DataForm5\Logging\Core\LogManager::class)->channel('security');
$security->warning('Fehlgeschlagene Anmeldung.', ['user' => 'admin']);
```

Logdateien liegen standardmäßig unter `storage/logs/` und dürfen keine Passwörter, Sitzungsschlüssel oder vollständigen Zugangsdaten enthalten.
