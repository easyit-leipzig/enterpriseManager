# RC1.8-FC1-HF34 – Master/Detail DataForm Composition

HF34 verbindet die bisher getrennten Ebenen „DataForm-Schema“ und physische
Projekttabelle zu einem echten Tabellen-DataForm-Runtime.

## 1. Tabellengebundene DataForms arbeiten auf der realen Tabelle

Wurde ein DataForm über „DataForm aus Projekttabelle erzeugen“ angelegt, wird
die Bindung aus `dataform_table_bindings` jetzt auch für Datensatz-CRUD
verwendet.

Damit gilt für ein DataForm aus `ed_ev`:

- Liste liest direkt `ed_ev`
- Neu schreibt direkt `ed_ev`
- Bearbeiten aktualisiert direkt `ed_ev`
- Löschen löscht direkt aus `ed_ev`

Dasselbe gilt für `ed_ev_info`.

Ungebundene DataForms verwenden weiterhin kompatibel `dataform_records`.

## 2. Master/Detail 1:n

Für eine Relation

`ed_ev.id -> ed_ev_info.to_ev_id`

ist `ed_ev` das Eltern-DataForm und `ed_ev_info` das Kind-DataForm.

In der Detailansicht eines `ed_ev`-Datensatzes werden automatisch alle
Kinddatensätze angezeigt, deren `to_ev_id` der aktuellen Eltern-ID entspricht.

Die Kindliste bietet:

- Kinddatensatz anlegen
- Kinddatensatz öffnen
- Kinddatensatz bearbeiten

## 3. Kinddatensatz aus dem Elternformular

„+ Kinddatensatz“ öffnet das Kindformular mit festem Elternkontext.

`to_ev_id` wird dabei nicht frei ausgewählt, sondern serverseitig und in der
Oberfläche auf die aktuelle Eltern-ID festgelegt. Dadurch kann ein Kind nicht
versehentlich dem falschen Elternobjekt zugeordnet werden.

## 4. Eltern-Auswahl und vererbte Werte

Die Elternauswahl sowie der `parent-value.php`-Endpunkt lesen Elternrecords
ebenfalls über den neuen RecordStore. Dadurch funktionieren diese Funktionen
für physische Tabellen und generische DataForms identisch.

## 5. Löschschutz

Ein Eltern-Datensatz kann nicht gelöscht werden, solange aktive
1:n-Kinddatensätze auf ihn verweisen. Zuerst müssen die Kinder entfernt
werden.

## 6. Direkter Einstieg aus der DataForm-Liste

Die DataForm-Liste zeigt noch nicht gebundene DataForm-verwaltete
Projekttabellen in einem eigenen Bereich „DataForm aus Projekttabelle
erzeugen“. Dadurch kann z. B. `ed_ev` direkt aus der DataForm-Seite erzeugt
werden, ohne zuerst zurück zur Tabellenverwaltung zu wechseln.

## Testfall ed_ev / ed_ev_info

1. DataForms öffnen.
2. `ed_ev` unter „DataForm aus Projekttabelle erzeugen“ erzeugen.
3. Beziehungen öffnen.
4. 1:n anlegen:
   - Eltern: DataForm von `ed_ev`
   - Kind: `Ed Ev Info`
   - Kind-Fremdschlüssel: `to_ev_id`
   - kein zusätzliches Lookup erzeugen
5. Im Eltern-DataForm einen Datensatz anlegen.
6. Eltern-Datensatz öffnen.
7. Unter `Ed Ev Info` „+ Kinddatensatz“ wählen.
8. Kinddaten erfassen und speichern.
9. Zum Eltern-Datensatz zurückkehren.
10. Der neue `ed_ev_info`-Datensatz muss dort erscheinen und physisch
    `to_ev_id=<id des ed_ev-Datensatzes>` besitzen.
