# Hotfix HF67 – Inline-Neuanlage nur am Tabellenende

## Problem
Seit HF58 wurde die `*`-Zeile zur Inline-Neuanlage auf jeder Seite der paginierten Datensatzliste dargestellt. Dadurch wirkte jede Seite wie ein eigenständiges Tabellenende.

## Korrektur
- Die Inline-Neuzeile wird nur noch auf der **letzten Seite** der aktuell angezeigten Ergebnismenge dargestellt.
- Bei einer leeren Ergebnismenge bleibt die `*`-Zeile auf Seite 1 sichtbar, damit der erste Datensatz direkt erfasst werden kann.
- **Neu** und **+ Neuer Datensatz** führen in der Tabellenansicht automatisch zur letzten Seite und fokussieren dort die `*`-Zeile.
- Bestehende Inline-Bearbeitung, Ad-hoc-Speichern, Speichern-Button und Datensatzzeiger bleiben unverändert.

## Sollverhalten
- Seite 1 … n-1: keine `*`-Zeile.
- Seite n: `*`-Zeile direkt nach dem letzten angezeigten Datensatz.
