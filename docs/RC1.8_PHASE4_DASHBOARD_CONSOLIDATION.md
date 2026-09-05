# RC1.8 Phase 4 – Enterprise-Dashboard-Konsolidierung

Phase 4 reduziert die Navigation und führt Betriebsinformationen zusammen.

## Hauptnavigation

- Dashboard
- Projekte
- Produkte
- Lizenzen
- Module
- Betrieb
- Abmelden

Die technischen Detailseiten für Jobs, Monitoring, Cluster, Storage und
Replikation bleiben bestehen, werden aber über **Betrieb** gebündelt.

## EnterpriseDashboard

`DataForm5\Core\Dashboard\EnterpriseDashboard` aggregiert ausschließlich
bestehende Dienste. Es führt keine administrativen Schreiboperationen aus.

Zusammengeführt werden:

- Modulstatus
- Queue-/Jobstatus
- Monitoring/Warnungen
- Clusterzustand
- Storagezustand
- Replikationsstatus

## Ziel

Das Dashboard wird wieder zur Einstiegsseite für die gesamte Plattform und
nicht zu einer Sammlung einzelner historisch hinzugefügter Menüpunkte.
