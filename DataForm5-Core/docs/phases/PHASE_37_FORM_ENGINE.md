# Phase 37 – Formular-Engine

Die Formular-Engine stellt produktneutrale Formulardefinitionen, Datenbindung, Validierung und HTML-Rendering bereit. DataForm selbst bleibt ein separates Produkt und verwendet diesen Layer später für Designer und Laufzeitformulare.

## Grundsätze

- Definition, Validierung und Rendering sind getrennt.
- Alle Ausgaben werden HTML-sicher maskiert.
- CSRF-Tokens können beim Rendern eingebunden werden.
- Produkte und Module können eigene Feldtypen über `FieldInterface` ergänzen.
- Persistenz gehört in Repository, ORM oder Commands, nicht in das Formular.
