# Assistant Phase 17 – DataForm-Import/Export und Transportpakete

Phase 17 erweitert die bisherige Vorlagenfunktion um einen echten DataForm-Transport zwischen Projekten.

## Neuer Assistent

`dataform.transport`

Der Assistent ist in `project.management`, `project.detail`, `dataform` und `assistant.center` verfügbar.

## Transportformat

Exportdatei: `*.dataform-package.zip`

Manifest: `easyit-dataform-package.json`

Schema: `easyit.dataform.package.v1`

Ein Paket enthält:

- `dataform.create`
- `dataform.fields`
- `dataform.relations`
- `dataform.events`
- `dataform.actions`
- Abhängigkeitsbeschreibung für Datenquellenprofil, Treiber und Hauptquelle
- optional einen rekursiv bereinigten Datenquellen-Snapshot
- SHA-256 und Größe für jeden Paketbestandteil

Klartextkennwörter, Secrets, Tokens und Credentials werden vor dem Export entfernt. Sichere Referenzen wie `passwordRef` bleiben erhalten.

## Exportablauf

1. Quellprojekt und DataForm wählen.
2. Optional Datenquellen-Snapshot aufnehmen.
3. Export bestätigen.
4. ZIP plus `.sha256` herunterladen.

Der Datenquellen-Snapshot dient der Übertragung von Konfiguration, wird beim späteren Import aber nicht automatisch angewendet.

## Importablauf

1. ZIP per multipart Upload hochladen.
2. Manifest, Schema, Eintragsliste und SHA-256 aller Bestandteile prüfen.
3. Zielprojekt und Ziel-DataForm festlegen.
4. Datenquellenprofil und Hauptquelle auf vorhandene Zielwerte abbilden.
5. Weitere Referenzen per `alterWert|neuerWert` mappen.
6. Vorschau/Diagnose ohne Schreibzugriff ausführen.
7. Import ausdrücklich bestätigen.
8. Zielzustände über den persistenten `AssistantStateStore` schreiben.
9. Nach dem Import vollständige DataForm-Diagnose aus Phase 8 ausführen.

## Konflikt- und Historienregeln

Vorhandene Zielkonfigurationen führen standardmäßig zu `FAIL`. Erst die Option „Vorhandene Zielkonfiguration historisiert überschreiben“ erlaubt die Übernahme als `PASS_WITH_WARNINGS`.

Alle geschriebenen DataForm-Zustände werden über Phase 14 versioniert und können per Undo/Redo zurückgesetzt werden.

## Datenquellen

Ohne Übernahme des Datenquellen-Snapshots muss das Zielprojekt bereits ein passendes persistentes Datenquellenprofil besitzen. Profilname und Hauptquelle werden vor dem Import geprüft.

Die Übernahme eines Snapshots ist ausdrücklich optional und muss gesondert bestätigt werden. Damit werden projektspezifische oder externe Verbindungsparameter nicht unbemerkt überschrieben.

## Sicherheit

- maximale Uploadgröße 20 MiB
- maximal 64 manifestierte Paketdateien
- maximal 5 MiB pro Paketbestandteil
- keine absoluten Archivpfade
- keine `../`-Pfadtraversal
- keine unerwarteten, nicht im Manifest registrierten ZIP-Einträge
- SHA-256-Prüfung jedes Bestandteils
- rekursives Secret-Sanitizing
- kein automatisches Überschreiben bestehender Zielkonfiguration
- Download-Endpunkt akzeptiert ausschließlich erzeugte DataForm-Paket- und SHA-Dateinamen

## Endpunkte

- Wizard: `admin/assistants/run.php?assistant=dataform.transport`
- Download: `admin/assistants/dataform-package-download.php`

## Tests

Phase 17 enthält einen CLI-Smoke-Test, einen echten HTTP/multipart-Test sowie die vollständige Regression Phase 6–17.
