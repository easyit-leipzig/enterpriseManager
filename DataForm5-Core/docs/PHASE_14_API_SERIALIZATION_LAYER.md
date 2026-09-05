# Phase 14 – API- und Serialisierungs-Layer

Build 0014 ergänzt den produktneutralen Core um eine einheitliche JSON-Ausgabe.

## Bestandteile

- `ApiResource` für kontrollierte Modell- und Datensatztransformationen
- `ResourceCollection` für Listen und zusätzliche Metadaten
- `Paginator` mit Seitenmetadaten und Navigationslinks
- `ApiResponse` für Erfolgs-, Fehler-, Created-, No-Content- und paginierte Antworten
- `ApiServiceProvider` zur Bereitstellung im Service-Container

Die Schicht verwendet die bestehende HTTP-Response und legt keine produktspezifischen Endpunkte fest.
