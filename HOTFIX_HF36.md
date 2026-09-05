# RC1.8-FC1-HF36 – Derived Multi-Enum / Multi-Lookup

HF36 ergänzt den neuen DataForm-Feldtyp `derived_multienum` („Abgeleitete Mehrfachauswahl“).

## Grundprinzip

Das Feld lädt auswählbare Werte dynamisch aus einer anderen Projekttabelle. Gespeichert werden **stabile Quellwerte**, normalerweise IDs, als kommaseparierte Liste:

```text
3,7,12
```

Die sichtbaren Texte werden bei der Anzeige erneut aus der Quelltabelle aufgelöst. Änderungen am Anzeigetext verändern deshalb keine gespeicherten Referenzen.

## Felddefinition

Im Formular-Designer stehen zur Verfügung:

- Quelltabelle
- Wertspalte
- Anzeigespalte
- optionale Abhängigkeit
- Filterspalte der Quelltabelle
- optionales Vergleichsfeld des aktuellen DataForms
- Mindestauswahl
- Maximalauswahl
- maximal geladene Optionen

Das Trennzeichen ist verbindlich `,`.

## Abhängigkeiten

Unterstützte Filtermodi:

1. keine Filterung
2. Filterspalte = ID des aktuellen Datensatzes
3. Filterspalte = aktuelle Eltern-ID
4. Filterspalte = Wert eines Feldes dieses DataForms

Beispiel für `ed_ev` / `ed_ev_info`:

```text
Quelltabelle:        ed_ev_info
Wertspalte:          id
Anzeigespalte:       bemerkung
Filtermodus:         current_record_id
Filterspalte:        to_ev_id
```

Beim Öffnen von `ed_ev` mit ID 17 werden damit nur Zeilen aus `ed_ev_info` angeboten, für die `to_ev_id = 17` gilt.

## Physische Speicherung

Bei tabellengebundenen DataForms muss das Zielfeld ein CHAR/VARCHAR/TEXT-Feld sein. Existiert für ein neu angelegtes `derived_multienum` noch keine physische Spalte, legt HF36 automatisch eine `TEXT NULL`-Spalte an und bindet sie über `table_binding` an das DataForm-Feld.

Bei generischen DataForms bleibt die Speicherung kompatibel in `dataform_records`.

## Datensatz-CRUD

- Create/Update: echte Mehrfachauswahl (`<select multiple>`)
- gespeicherte IDs werden beim Bearbeiten wieder markiert
- Server validiert jede Auswahl gegen die aktuell zulässigen Quellwerte
- doppelte Auswahlwerte werden entfernt
- Mindestauswahl/Maximalauswahl werden validiert
- Listen, Detailansichten, Kindlisten und CSV-Export zeigen die aufgelösten Beschriftungen
- nicht mehr vorhandene Quellwerte werden sichtbar als „nicht mehr vorhanden“ gekennzeichnet

## Referenzschutz

HF36 verhindert:

- Löschen eines Quelldatensatzes, solange seine Wertspalte noch in einem `derived_multienum` verwendet wird
- Löschen einer Quelltabelle, solange ein Feld daraus Werte ableitet
- Löschen einer Wert-, Anzeige- oder Filterspalte, solange sie von einer abgeleiteten Mehrfachauswahl verwendet wird

Damit besitzt die CSV-Speicherung zwar keinen nativen SQL-Fremdschlüssel, wird aber durch DataForm selbst referenziell abgesichert.

## Beispiel

```text
Tabelle ed_ev
id | date_time | info_ids
17 | ...       | 3,7,12

Tabelle ed_ev_info
id | bemerkung              | to_ev_id
3  | Motor geprüft          | 17
7  | Reifen kontrolliert    | 17
12 | Hauptuntersuchung      | 17
```

Im Formular erscheinen statt `3,7,12` die drei Texte als Mehrfachauswahl.
