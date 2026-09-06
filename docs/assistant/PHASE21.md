# Assistant Phase 21 – Modul-Updates und Upgrades

Phase 21 erweitert Phase 20 um einen sicheren Update-/Upgrade-Prozess für bereits installierte Module.

## Assistent

`dataform.module-updates`

## Update-Suche

Das projektbezogene Installationsregister wird mit den sichtbaren Modulen der Projekt- und Systembibliothek verglichen. Pro installiertem Modul werden u. a. folgende Zustände unterschieden:

- `CURRENT`
- `UPDATE_AVAILABLE`
- `REINSTALL_AVAILABLE`
- `DOWNGRADE_AVAILABLE`
- `LIBRARY_MISSING`

Nur höhere SemVer-Releases werden als normales Update angeboten.

## Reverse-Dependency-Prüfung

Neben den normalen Abhängigkeiten des zu aktualisierenden Moduls prüft Phase 21 auch die umgekehrte Richtung: Ein Upgrade darf kein bereits installiertes Modul unbemerkt inkompatibel machen.

Beispiel:

- `base` installiert: `1.0.0`
- `feature` installiert: `1.0.0`, benötigt `base ^1.0.0`
- Bibliothek: `base 2.0.0`

Ein isoliertes Upgrade von `base` wird blockiert. Ist zusätzlich `feature 2.0.0` verfügbar und benötigt dieses `base ^2.0.0`, kann Phase 21 bei aktivierter Kaskade beide Updates automatisch gemeinsam planen.

## Kaskadierendes Upgrade

Bei aktivierter Option „Abhängige Module bei Bedarf automatisch mit aktualisieren“ werden kompatible Folgeupdates automatisch ergänzt. Anschließend wird der vollständige Versionssatz erneut geprüft.

Der Upgrade-Plan verwendet weiterhin den Phase-20-Dependency-Resolver und damit dieselbe topologische Installationsreihenfolge.

## Mapping-Wiederverwendung

Phase 20 speichert ab Phase 21 zusätzlich die beim Installieren verwendeten:

- DataForm-Mappings
- Referenz-Mappings
- Datasource-Übernahmeeinstellung

im Installationsregister. Dadurch kann ein späteres Upgrade dieselben Ziel-DataForms und Zielreferenzen erneut verwenden. Für ältere Registereinträge ohne diese Felder wird bei gleicher Anzahl der DataForms ein kompatibles positionsbasiertes Mapping verwendet.

## Rollback-Checkpoint

Vor dem ersten Schreibzugriff erzeugt Phase 21 über den bereits getesteten Phase-10-Recovery-Service ein vollständiges Projektbackup. Lokale Projektdatenbanken werden mitgesichert.

Das Backup besitzt:

- Projekt-ZIP
- SHA-256-Datei
- Phase-10-Manifest und Datei-Prüfsummen
- einen Phase-21-Checkpoint-Eintrag mit Upgrade-Zielversionen und Ergebnis

Checkpoint-Metadaten liegen unter:

`storage/assistant/module-update-rollbacks/<project-id>/`

Das eigentliche Recovery-ZIP liegt unverändert im zentralen Phase-10-Backupbereich:

`backups/projects/`

Dadurch wird keine zweite Recovery- oder ZIP-Sicherheitslogik eingeführt.

## Ablauf

1. Projekt auswählen.
2. Installierte Module und Bibliothek vergleichen.
3. Alle Updates oder ausgewählte Modul-IDs festlegen.
4. Reverse-Dependencies und normale Abhängigkeiten prüfen.
5. Kaskadierende Updates ergänzen.
6. Zielversionssatz vollständig validieren.
7. DataForm-/Referenz-Mappings vorprüfen.
8. Rollback-Projektbackup erzeugen.
9. Module in Abhängigkeitsreihenfolge aktualisieren.
10. Installationsregister aktualisieren.
11. Rollback-Checkpoint und neue Versionsstände anzeigen.

## Sicherheit

- kein Update ohne vollständigen PASS/PASS_WITH_WARNINGS-Plan
- kein impliziter Downgrade
- kein unbemerktes Brechen installierter Pflichtabhängigkeiten
- kein Schreibzugriff vor erfolgreichem Rollback-Backup
- bestehende Phase-18-/20-Prüfung, Mapping und Diagnose werden wiederverwendet
- DataForm-Zustände bleiben über Phase 14 historisiert
- Backup-Download verwendet den vorhandenen geschützten Phase-10-Endpunkt

## UI

Phase 21 ist auf `project.management`, `project.detail`, `dataform` und `assistant.center` registriert.
