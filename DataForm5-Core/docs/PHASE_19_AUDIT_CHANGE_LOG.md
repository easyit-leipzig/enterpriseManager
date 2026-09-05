# Phase 19 – Audit- und Änderungsprotokoll-Layer

Der Core besitzt nun ein produktneutrales, manipulationsnachweisbares Audit-Protokoll. Jeder Eintrag enthält Ereignis, UTC-Zeit, Akteur, betroffenes Objekt, Metadaten sowie optionale HTTP-Herkunftsdaten.

## Integrität

Der Datei-Treiber speichert JSONL-Datensätze als SHA-256-HMAC-Hashkette. Jeder Datensatz bindet den Hash des vorherigen Eintrags ein. Nachträgliche Änderungen, Löschungen innerhalb der Kette oder Umordnungen werden durch `AuditManager::verify()` erkannt.

## Verwendung

```php
$audit = $kernel->container()->get(\DataForm5\Audit\Core\AuditManager::class);
$audit->record(
    'project.updated',
    ['changed' => ['name']],
    'user', '7',
    'project', '42'
);
```

Filter stehen über `AuditQuery` bereit. Der Core enthält Datei- und Null-Treiber. Das Audit-Protokoll ist append-only zu behandeln; Anwendungen dürfen es nicht über normale CRUD-Funktionen bearbeiten.
