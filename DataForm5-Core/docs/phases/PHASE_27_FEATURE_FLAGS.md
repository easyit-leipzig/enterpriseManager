# Phase 27 – Feature-Flag- und Konfigurationsfreigabe-Layer

Build 0027 ergänzt den DataForm5-Core um zentral verwaltete Funktionsfreigaben. Features können nach Umgebung, Benutzer, Gruppe und einem stabilen prozentualen Rollout aktiviert werden.

## Verbindliche Regeln

- Produktcode fragt Freigaben ausschließlich über `FeatureManagerInterface` ab.
- Unbekannte Features sind standardmäßig deaktiviert.
- Prozentuale Rollouts sind anhand eines stabilen Subjektschlüssels reproduzierbar.
- Sicherheitsfunktionen dürfen nicht allein über Feature Flags abgeschaltet werden.
- Neue Freigaben werden in `config/features.php` dokumentiert.
