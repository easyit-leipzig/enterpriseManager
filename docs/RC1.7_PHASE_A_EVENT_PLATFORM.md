# RC1.7 – Phase A: Enterprise Event Platform

## Ziel

Phase A vereinheitlicht das bereits im DataForm5-Core vorhandene Dispatcher-System und macht es als stabile Enterprise-Schnittstelle für Produkte und Module verfügbar. Module sollen nicht mehr direkt voneinander abhängen, sondern auf benannte Ereignisse reagieren.

## Öffentliche API

```php
enterprise_event_listen('dataform.record.created', function (NamedEvent $event): void {
    $recordId = $event->payload()['record_id'] ?? null;
});

enterprise_event_dispatch('dataform.record.created', [
    'record_id' => 42,
    'dataform_id' => 7,
]);
```

Listener können mit Prioritäten registriert werden. Höhere Werte werden zuerst ausgeführt.

## Ereignisnamen

Ereignisse verwenden die Form `bereich.objekt.aktion`, zum Beispiel:

- `auth.user.logged_in`
- `auth.user.logged_out`
- `project.registered`
- `dataform.record.created`
- `dataform.record.updated`
- `dataform.record.deleted`

## Sicherheitsregel

Event-Payloads dürfen niemals Kennwörter, API-Schlüssel, Session-IDs oder andere Geheimnisse enthalten. Das Diagnoseprotokoll filtert typische Secret-Schlüssel zusätzlich.

## Diagnose

Die Enterprise-Schicht kann Ereignisse ohne Payload-Geheimnisse in `storage/logs/events.log` protokollieren. Der Entwicklerbereich `app/developer/events.php` zeigt den zentralen Ereigniskatalog und die letzten Diagnoseeinträge.

## Rückwärtskompatibilität

Die vorhandenen Klassen `EventDispatcher`, `EventSubscriberInterface` und `EventServiceProvider` bleiben unverändert nutzbar. Phase A ergänzt eine Enterprise-Fassade, einen zentralen Katalog und erste verbindliche Core-Ereignisse.
