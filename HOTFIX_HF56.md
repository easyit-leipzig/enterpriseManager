# HOTFIX HF56 – Datensatzzeiger in Tabellenansicht

HF56 ergänzt in `products/dataform/records.php` einen klassischen Datensatzzeiger als eigene Button-Spalte.

Zustände:
- aktiver Datensatz: `▶`
- neuer Datensatz: `*`
- nicht aktiver Datensatz: leerer Zeigerbutton

Der Zeiger ist von der Checkbox für Mehrfachauswahl getrennt. Ein Klick auf den Zeiger aktiviert ausschließlich den betreffenden Datensatz und erhält Suche, Filter, Sortierung und Seite. Die aktive Zeile wird zusätzlich dezent hervorgehoben. Die `*`-Zeile öffnet die Neuanlage.
