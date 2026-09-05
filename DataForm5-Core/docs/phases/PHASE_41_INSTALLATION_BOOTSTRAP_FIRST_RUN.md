# Phase 41 – Installations-, Bootstrap- und Ersteinrichtungs-Layer

Der Layer prüft die technische Laufzeitumgebung, bereitet benötigte Storage-Verzeichnisse vor und schreibt nach erfolgreicher Ersteinrichtung einen Installations-Lock.

## Verbindliche Regeln

- Eine Installation darf nur bei erfüllten Pflichtanforderungen abgeschlossen werden.
- Der Installations-Lock verhindert eine unbeabsichtigte erneute Initialisierung.
- Produktive Zugangsdaten werden nicht in den Lock geschrieben.
- `ext-zip` bleibt optional; Funktionen, die ZIP benötigen, müssen dies gesondert prüfen.
