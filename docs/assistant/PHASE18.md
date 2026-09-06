# Assistant Phase 18 – Mehrfach-DataForm-/Modulpaket-Assistent

Phase 18 erweitert den DataForm-Transport um ein Modulformat für mehrere voneinander abhängige DataForms.

## Assistent

`dataform.module-transport`

## Paketformat

- Dateiendung: `*.dataform-module.zip`
- Manifest: `easyit-dataform-module.json`
- Schema: `easyit.dataform.module-package.v1`
- Graph: `module/graph.json`
- Graph-Schema: `easyit.dataform.module-graph.v1`

## Export

Ausgehend von einem oder mehreren Start-DataForms kann der Assistent transitive Kind-DataForms aus gespeicherten Beziehungen automatisch aufnehmen. Je DataForm werden – sofern vorhanden – die Bereiche `dataform.create`, `dataform.fields`, `dataform.relations`, `dataform.events` und `dataform.actions` exportiert.

Das Paket enthält außerdem einen Beziehungsgraphen, Datenquellenabhängigkeiten, optional einen bereinigten Datenquellen-Snapshot und SHA-256-Prüfsummen für jeden Paketbestandteil sowie das gesamte ZIP.

## Import

Vor der Übernahme werden alle DataForms separat gemappt. Zusätzlich können Profil-, Tabellen-, View-, CSV- und andere exakte Referenzen über `alt|neu` umgeschrieben werden. Beziehungen werden mit demselben Mapping aktualisiert.

Ein bestehendes Ziel wird standardmäßig blockiert. Nur ausdrücklich freigegebenes Überschreiben ist möglich und läuft über den vorhandenen `AssistantStateStore`, damit Phase 14 (Historie / Undo / Redo) erhalten bleibt.

Nach dem Import wird für jedes DataForm ein eigener Diagnosebericht erzeugt.

## Mehrfach-DataForm-Persistenz

Phase 18 ergänzt für `dataform.create` den DataForm-spezifischen Scope:

`<projectId>|<dataFormId>`

Der bisherige projektweite Zustand bleibt als Legacy-Fallback erhalten. DataForm-Assistent, Einzeltransport und Diagnose wurden rückwärtskompatibel erweitert.

## Sicherheit

- keine Klartext-Secrets in Modul-Snapshots
- SHA-256 je Datei
- unbekannte ZIP-Einträge werden abgewiesen
- absolute Pfade und `..`-Traversal werden abgewiesen
- maximal 512 Manifestdateien
- maximal 5 MiB je Paketbestandteil
- maximal 50 MiB Upload
- sicherer Download-Endpunkt `dataform-module-download.php`

## Tests

- Phase-18-Smoke-Test
- transitive 1:n-Aufnahme
- Modulgraph
- Zwei-DataForm-Mapping
- Relationsmapping
- Datenquellenmapping
- Secret-Sanitizing
- Importhistorisierung
- Zielkonfliktschutz
- Diagnose je importiertem DataForm
- Regression Phase 6–18
- PHP-/JavaScript-Syntaxprüfung
- Admin-HTTP-Rendering
