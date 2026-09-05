# HOTFIX HF58 – Neuer Datensatz direkt in der Tabellenansicht

HF58 ersetzt die bisherige separate Link-Zeile „Neuen Datensatz anlegen“ durch eine echte editierbare Neuzeile in der tabellarischen Datensatzansicht.

## Verhalten

- Der Datensatzzeiger der Neuzeile bleibt `*`.
- Die Neuzeile enthält für jede sichtbare Listenspalte ein editierbares, typgerechtes Eingabeelement.
- n:1-/1:n-Lookups werden in der Neuzeile als Auswahlfelder gerendert und speichern weiterhin die technische Referenz-ID.
- Text, Zahl, Datum, Datum/Zeit, E-Mail, URL, Checkbox, Auswahl, Textarea und abgeleitete Mehrfachauswahl werden unterstützt.
- „Datensatz anlegen“ speichert direkt aus der Tabellenzeile heraus.
- Nach erfolgreichem Speichern bleibt die Tabellenansicht geöffnet; der neue Datensatz wird zum aktiven Datensatz (`▶`).
- Die Inline-Eingabefelder sind über ein separates HTML-Formular angebunden und werden nicht vom Bulk-Delete-Formular mitgesendet.
- Auch bei einer noch leeren Datensatzliste bleibt die Tabellenstruktur mitsamt editierbarer Neuzeile sichtbar.
- Der Stern-Button fokussiert das erste editierbare Feld der Neuzeile.

## Regression

HF55 Typed Record List, HF56 Datensatzzeiger und HF57 CRUD-Isolation bleiben unverändert erhalten.
