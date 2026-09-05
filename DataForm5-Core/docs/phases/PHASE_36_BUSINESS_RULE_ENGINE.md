# Phase 36 – Regel- und Business-Rule-Engine

Die Business-Rule-Engine trennt fachliche Entscheidungen von Controllern, Workflows und Datenbankcode.

## Bestandteile

- `FactContext` für verschachtelte Fakten
- priorisierte Regeln über `RuleInterface`
- Callback-Regeln
- Regelgruppen
- Strategien `first` und `all`
- nachvollziehbare `Decision`-Objekte mit Begründungen und Metadaten

## Verbindliche Regel

Regeln dürfen Entscheidungen liefern, aber keine verdeckten Zustandsänderungen ausführen. Zustandsänderungen gehören in Workflow-Actions, Commands oder explizite Services.
