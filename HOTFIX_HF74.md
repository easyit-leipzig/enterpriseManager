# HOTFIX HF74 – Löschbutton je Datensatzzeile

## Problem
In der tabellarischen DataForm-Datenansicht konnten einzelne Datensätze zwar über Detailansicht oder Sammellöschung entfernt werden, es fehlte jedoch ein direkter Löschbutton pro bestehendem Datensatz.

## Korrektur
- Jede bestehende Datensatzzeile besitzt in **Aktionen** jetzt einen eigenen **Löschen**-Button.
- Der Button nutzt die vorhandene serverseitige `delete_record`-Logik einschließlich Kinddatensatz-/Referenzschutz.
- Vor der Löschung wird der konkrete Datensatz per JavaScript-Confirm bestätigt.
- Für die `*`-Neuzeile wird ausdrücklich **kein** Löschbutton dargestellt.
- Inline-Speichern, Ad-hoc-Modus, Anzeigen und Sammellöschung bleiben unverändert verfügbar.

## Sollverhalten
- Bestehender Datensatz: `Speichern | Anzeigen | Löschen`.
- Neuer Datensatz (`*`): nur `Datensatz anlegen`, kein Löschen.
