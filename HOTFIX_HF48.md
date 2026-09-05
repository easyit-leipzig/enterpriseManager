# HF48 – Gespeichertes Paket lädt Exportformular

## Ziel
Ein Klick auf eine Zeile unter **Gespeicherte Pakete** lädt die im `.dfpkg` persistierte Exportkonfiguration zurück in das obere Exportformular.

## Verhalten
- Paketname wird geladen.
- Paketbestandteile werden geladen.
- ausgewählte DataForms werden geladen.
- physische Basistabellen werden geladen.
- gewählte Paketzeile wird visuell markiert.
- die Ansicht scrollt zum Exportformular und bestätigt den geladenen Paketstand.
- CRUD-Schaltflächen in der Zeile behalten ihre eigene Funktion und lösen das Laden nicht zusätzlich aus.
- Enter/Leertaste auf einer Paketzeile unterstützt Tastaturbedienung.

Die Konfiguration stammt aus `manifest.json` (`includes` und `selection`) und nicht aus einer neu geratenen UI-Konfiguration.
