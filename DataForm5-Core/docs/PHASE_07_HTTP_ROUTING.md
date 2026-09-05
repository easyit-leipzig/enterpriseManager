# Phase 7 – HTTP- und Routing-Schicht

Build 0007 ergänzt den DataForm5-Core um eine produktneutrale HTTP-Schicht.

## Bestandteile

- `Request` und `Response`
- Router für GET, POST, PUT, PATCH und DELETE
- benannte Routen und URL-Erzeugung
- dynamische Parameter wie `/users/{id}`
- globale und routenspezifische Middleware
- `HttpKernel`
- JSON-Fehlerantworten für 404 und 500
- Service-Provider-Integration

Produkte registrieren ihre Routen am zentralen `Router` und lassen Anfragen durch den `HttpKernel` verarbeiten.
