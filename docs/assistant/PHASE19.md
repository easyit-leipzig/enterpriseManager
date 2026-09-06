# Assistant Phase 19 – DataForm-Modulbibliothek

Phase 19 erweitert Phase 18 um eine persistente Bibliothek für komplette Mehrfach-DataForm-Module.

## Assistent

`dataform.module-library`

## Bibliotheksbereiche

Systemweit:

`storage/assistant/modules/`

Projektbezogen:

`projects/<project-id>/config/assistant/modules/`

Jedes aktive Modul besteht aus Metadaten (`<id>.json`) und dem unveränderten, erneut prüfbaren Phase-18-Paket (`<id>.dataform-module.zip`).

## Funktionen

- Modul aus einem Projekt und seinen Start-DataForms erzeugen
- transitive Kind-DataForms über den Phase-18-Beziehungsgraph übernehmen
- systemweite oder projektbezogene Freigabe
- Projektisolation
- Versionsverlauf mit Metadaten- und ZIP-Snapshot
- Modul umbenennen/beschreiben
- Modul kopieren / klonen
- Freigabe zwischen Projekt und System verschieben
- Phase-18-Modulpaket importieren
- gespeichertes Modulpaket exportieren
- kontrolliertes Löschen in `.trash/`
- gespeichertes Modul in ein Zielprojekt einspielen
- DataForm- und Referenzmapping vor Installation
- Konfliktschutz und historisiertes Überschreiben
- Phase-18-Vorschau und Diagnose unverändert wiederverwenden
- Phase-14-Historie/Undo/Redo für die installierten DataForm-Zustände

## Versionierung

Versionen liegen in:

`<library>/.versions/<module-id>/`

und enthalten je Version die Metadaten sowie das zugehörige `.dataform-module.zip`.

## Papierkorb

Kontrolliert gelöschte Module werden mit Metadaten und Paket verschoben nach:

`<library>/.trash/`

Das Löschen verlangt die exakte Eingabe des Modulnamens.

## Sicherheit

Phase 19 erzeugt keine zweite Paketprüfung. Import und Installation verwenden den Phase-18-`DataFormModulePackageService`. Dadurch gelten weiterhin SHA-256-Prüfung, Manifestprüfung, ZIP-Pfadschutz, Größenlimits und Secret-Sanitizing. Klartextkennwörter werden nicht in Bibliotheksmodule übernommen; Secret-Referenzen wie `passwordRef` bleiben möglich.

## Installation eines Bibliotheksmoduls

Bibliothek → Modul auswählen → Zielprojekt → DataForm-Mapping → Referenz-Mapping → Phase-18-Vorschau → ausdrückliche Übernahme → Diagnose je DataForm.

## UI

Phase 19 ist auf `project.management`, `project.detail`, `dataform` und `assistant.center` registriert. Download-Endpunkt: `admin/assistants/module-library-download.php`.
