# RC1.8 Phase 5.1 – Developer Mode

Phase 5.1 führt den Entwicklermodus ein, ohne bereits Generatoren oder das
vollständige SDK vorwegzunehmen.

## Aktivierung

```text
DEVELOPER_MODE=true
DEVELOPER_OVERLAY=true
DEVELOPER_ALLOWED_ROLES=admin
DEVELOPER_MAX_EVENTS=100
```

Standardmäßig ist der Developer Mode deaktiviert.

## Developer Dashboard

`app/developer/index.php`

Zeigt:

- Request-Laufzeit
- aktuellen und maximalen Speicherverbrauch
- PHP-Version und SAPI
- geladene/aktive Module
- Service-Container-Status
- Event-Trace des aktuellen Requests
- Queue-Status
- Scheduler-Tasks

## Overlay

Mit `Ctrl + Shift + D` wird auf authentifizierten Enterprise-Seiten ein kleines
Debug-Overlay geöffnet. Es bezieht seine Daten aus
`app/developer/overlay.php`.

## Sicherheit

- nur bei `DEVELOPER_MODE=true`
- nur für Rollen aus `DEVELOPER_ALLOWED_ROLES`
- zusätzlich Capability `developer.view`
- sensible Kontextschlüssel wie Passwort, Token, Secret, Session und API-Key
  werden im Trace redigiert
- Developer Mode sollte in Produktion deaktiviert bleiben
