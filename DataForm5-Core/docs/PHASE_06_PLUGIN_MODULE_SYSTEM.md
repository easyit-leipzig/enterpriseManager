# Phase 6 – Plugin- und Modulsystem

Build 0006 führt ein produktneutrales Modulsystem ein.

## Bestandteile
- JSON-Manifeste (`module.json`)
- automatische Erkennung unter `modules/`
- Abhängigkeitsauflösung und Zyklenerkennung
- Aktivierung/Deaktivierung über das Manifest
- Modul-Lebenszyklus `register()` und `boot()`
- zentrale `ModuleRegistry`
- synchroner `HookDispatcher`
- Referenzmodul `DemoModule`

Produkte und optionale Enterprise-Funktionen können damit außerhalb des Core-Codes erweitert werden.
