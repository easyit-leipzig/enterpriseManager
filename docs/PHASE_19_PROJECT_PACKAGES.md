# Phase 19 – DataForm-Projektpakete

Phase 19 führt das Paketformat `.dfpkg` für vollständige DataForm-Projekte ein.

## Export

Der Export enthält Manifest, Projektmetadaten und JSON-Dateien der vorhandenen DataForm-Konfigurationstabellen. Produktivdatensätze werden nur auf ausdrückliche Auswahl aufgenommen. Jedes Paket erhält eine Paket-ID und SHA-256-Prüfsumme.

## Import

Der Import erfolgt zweistufig: Paketprüfung und bestätigte Übernahme. Erlaubte Inhalte sind strikt begrenzt. Pfadtraversierung, absolute Pfade, unbekannte Dateien, übergroße Archive und fremde Tabellen werden abgewiesen. Die Übernahme läuft transaktional.

## Konfliktstrategien

- **Aktualisieren:** Vorhandene Datensätze mit gleicher ID werden aktualisiert.
- **Überspringen:** Vorhandene Datensätze mit gleicher ID bleiben unverändert.

## Datenbank

Die Historie wird in `project_package_history` in der vorhandenen Projektdatenbank gespeichert. Es wird keine neue Datenbank erzeugt.
