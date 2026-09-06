# Assistant Phase 24 – Migrations-Gate und Release-Freigabe

Phase 24 führt einen verbindlichen Release-Gate-Zustand für neu über die Assistentenoberfläche definierte Modul-Releases ein.

## Statusmodell

- `DRAFT` – Release wurde geändert und ist nicht installierbar.
- `GATE_FAILED` – isolierter Gate-Lauf ist fehlgeschlagen; nicht installierbar.
- `GATE_PASSED` – technisches Gate vollständig bestanden; noch nicht installierbar.
- `RELEASED` – Gate bestanden und ausdrücklich freigegeben; installierbar.
- `LEGACY_RELEASED` – Rückwärtskompatibilität für vor Phase 24 entstandene Pakete ohne Gate-Metadaten.

Jede über `ModuleDependencyAssistant` neu gespeicherte Release-Version, Abhängigkeit oder Migration wird als `DRAFT` geschrieben. Die bisherige programmatische `setRelease()`-Signatur bleibt kompatibel; mit `requireGate=true` wird das neue Gate erzwungen.

## Isolierter Gate-Lauf

`dataform.module-release-gate` erstellt aus dem gewählten Referenzprojekt einen temporären Projektklon und prüft dort:

1. Paketintegrität und SHA-256,
2. SemVer-Releaseversion,
3. lokal rücksetzbare Prüfdatenquelle,
4. Upgradepfad oder Clean-Install-Pfad,
5. Modulabhängigkeiten,
6. Dry-Run,
7. reale Installation bzw. Migration im Klon,
8. Vorher-/Nachher-Schema-Diff,
9. Recovery-Checkpoint und SHA-256,
10. realen Rollback,
11. exakte Rollback-Verifikation (`diff.count = 0`).

Das Referenzprojekt selbst wird nicht verändert.

## CSV / SQLite / externe Datenbanken

CSV und SQLite können im temporären Projektklon vollständig geprüft und zurückgesetzt werden. MySQL und Oracle werden für die vollautomatische Release-Freigabe blockiert, solange kein isolierter und automatisch rücksetzbarer Datenbank-Prüfpfad zur Verfügung steht. Damit kann das Gate nicht versehentlich eine externe produktive Datenbank verändern.

## Release-Fingerprint

Der Gate-Bericht erhält einen SHA-256-basierten Release-Fingerprint über die fachlichen Paketbestandteile. `easyit-dataform-module.json` und `module/release-gate.json` werden dabei bewusst ausgeschlossen, damit eine reine Gate-Statusänderung den geprüften fachlichen Inhalt nicht verändert.

Vor der Freigabe wird der Fingerprint erneut berechnet. Jede fachliche Änderung nach dem Gate macht den alten Gate-Bericht dadurch unbrauchbar.

## Paketmetadaten

Freigegebene bzw. gegatete Pakete enthalten zusätzlich:

`module/release-gate.json`

Schema:

`easyit.dataform.module-release-gate.v1`

Enthalten sind u. a. Status, `installable`, Releaseversion, Gate-Report-ID, Report-SHA-256, Release-Fingerprint, Validierungsprojekt sowie Prüf- und Freigabezeitpunkt.

## Gate-Berichte

Gate-Berichte werden unter

`storage/assistant/module-release-gates/<scope>/<module-id>/`

gespeichert und besitzen das Schema

`easyit.assistant.module-release-gate-report.v1`.

## Freigabe

Ein `GATE_PASSED`-Release wird erst durch die explizite Bestätigung

`<module-id>@<version>`

zu `RELEASED`. Gate-Warnungen müssen separat ausdrücklich akzeptiert werden.

## Installationssperre

Nicht freigegebene Releases werden blockiert in:

- direkter Modulbibliotheksinstallation,
- Bibliotheks-Staging,
- Modulabhängigkeitsplanung,
- Modulupdate,
- Modulmigration.

Der Gate-Service besitzt ausschließlich für den isolierten Validierungsklon einen internen Bypass, damit ein DRAFT-Release dort geprüft werden kann.

## Tests

Phase 24 prüft insbesondere:

- DRAFT ist nicht installierbar,
- DRAFT wird durch den normalen Migrationspfad blockiert,
- isolierter Gate-Lauf besteht,
- Schema-Diff wird real erzeugt,
- Rollback ergibt `diff = 0`,
- Gate-Bericht und SHA-256 werden persistiert,
- `GATE_PASSED` bleibt nicht installierbar,
- falsche Freigabebestätigung wird blockiert,
- `RELEASED` wird installierbar,
- Release-Gate-Metadaten sind Bestandteil des geprüften Modulpakets,
- Release-Fingerprint bleibt über reine Gate-Metadatenänderungen stabil,
- Legacy-Phasen 6–23 bleiben rückwärtskompatibel.
