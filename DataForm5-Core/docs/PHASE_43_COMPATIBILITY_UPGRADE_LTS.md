# Phase 43 – Compatibility, Upgrade and LTS Layer

Der Layer definiert unterstützte Ausgangsversionen, geordnete Upgrade-Schritte, Deprecation-Hinweise und sichere Upgrade-Pläne.

## Verbindliche Regeln

1. Vor jedem Upgrade wird ein Recovery-Punkt erzeugt.
2. Downgrades sind nicht Teil des Upgrade-Layers.
3. Major-Upgrades benötigen eine manuelle Freigabe.
4. Veraltete APIs werden mit Ersatz und geplantem Entfernungszeitpunkt registriert.
5. Produkte prüfen vor einem Update `inspect()` und führen anschließend ausschließlich den erzeugten `plan()` aus.
