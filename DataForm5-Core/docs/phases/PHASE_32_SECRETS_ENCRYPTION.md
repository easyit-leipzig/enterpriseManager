# Phase 32 – Secret-, Schlüssel- und Verschlüsselungs-Layer

Phase 32 führt eine produktneutrale Verwaltung vertraulicher Konfigurationswerte ein.

## Bestandteile

- `SecretManager` für Lesen, Schreiben, Löschen und Pflichtwerte
- verschlüsselter JSON-Dateispeicher und In-Memory-Speicher
- authentifizierte Verschlüsselung mit Sodium Secretbox oder AES-256-GCM
- versionierte Nutzlast mit Schlüssel-ID
- `KeyRing` mit aktivem und älteren Entschlüsselungsschlüsseln
- Schlüsselrotation durch erneute Verschlüsselung aller Einträge
- atomische Schreibvorgänge und restriktive Dateirechte

## Sicherheitsregeln

1. Produktionsschlüssel dürfen niemals im Repository gespeichert werden.
2. `SECRETS_KEY` muss in Produktion extern bereitgestellt werden.
3. Alte Schlüssel dürfen erst entfernt werden, nachdem alle Secrets rotiert wurden.
4. Geheimnisse dürfen weder geloggt noch in Audit-Metadaten oder Fehlermeldungen ausgegeben werden.
5. Die Standardentwicklungsschlüssel sind vor dem Produktivbetrieb zwingend zu ersetzen.

## Beispiel

```php
$secrets = $kernel->container()->get(\DataForm5\Secrets\Core\SecretManager::class);
$secrets->set('database.password', '...');
$password = $secrets->require('database.password');
```
