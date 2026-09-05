# Phase 33 – Messaging-, Event-Bus- und Integrations-Layer

Diese Phase führt eine produktneutrale Nachrichtenschicht ein. Commands und Queries besitzen jeweils genau einen Handler. Integration Events können mehrere Subscriber besitzen. Eine Middleware-Pipeline erlaubt Logging, Audit, Metriken, Transaktionen und weitere Querschnittsfunktionen, ohne Fachhandler zu verändern.

## Kernregeln

- Nachrichten werden durch einen stabilen Namen und eine serialisierbare Payload beschrieben.
- Commands und Queries werden über `MessageBus` verarbeitet.
- Integration Events werden über `EventBus` an null bis viele Subscriber verteilt.
- Nicht registrierte Command-/Query-Handler führen zu einer kontrollierten Exception.
- Produktlogik bleibt außerhalb des Core.
