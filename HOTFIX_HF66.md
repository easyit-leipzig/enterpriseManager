# HOTFIX HF66 – Speichern-Button bleibt auch bei Ad-hoc-Speicherung sichtbar

## Ziel

Der zeilenweise Speichern-Button der tabellarischen DataForm-Ansicht bleibt immer verfuegbar – unabhaengig davon, ob das DataForm auf manuelles oder Ad-hoc-Speichern eingestellt ist.

## Verhalten

- `manual`: Aenderungen werden wie bisher erst durch Klick auf `Speichern` persistiert.
- `adhoc`: Aenderungen werden weiterhin automatisch bei Feld-Aenderung gespeichert.
- `adhoc`: Zusaetzlich bleibt in jeder bestehenden Datensatzzeile der Button `Speichern` sichtbar und kann jederzeit explizit verwendet werden.
- Der Ad-hoc-Status wird weiterhin als Badge angezeigt.
- Die `*`-Zeile fuer Neuanlagen bleibt weiterhin ueber `Datensatz anlegen` steuerbar.
- Erfolgsdialog und Validierungslogik aus HF65 bleiben unveraendert.
