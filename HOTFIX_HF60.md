# HOTFIX HF60

## Ziel

Die tabellarische Datensatzansicht eines an eine physische Projekt-Tabelle gebundenen DataForms muss ein vollständiges Datenblatt darstellen. Alle DataForm-Felder der Tabelle müssen als Spalten sichtbar und in der `*`-Neuzeile anlegbar sein.

## Fehlerbild bis HF59

Die Listenansicht verwendete standardmäßig nur die ersten vier `list_visible`-Felder. Dadurch konnten bei `ed_ev_info` beispielsweise `from_person` und `to_person` fehlen. Ein Pflichtfeld konnte bei der Inline-Erfassung validiert werden, obwohl seine Eingabespalte in der Tabelle gar nicht sichtbar war.

## Korrektur

- Physisch gebundene DataForms verwenden in der Datenblattansicht den vollständigen Feldsatz.
- Die frühere Vier-Spalten-Defaultbegrenzung gilt nur noch für generische DataForms.
- Benutzer-/Alt-Einstellungen für sichtbare Listenspalten können physische Tabellenfelder nicht mehr ausblenden.
- Die `*`-Neuzeile rendert denselben vollständigen Feldsatz.
- Die Spaltenkonfiguration zeigt bei physischen Tabellen alle Felder fest aktiviert an.
- Horizontaler Tabellen-Scroll bleibt über den bestehenden Tabellen-Wrapper möglich.

## Beispiel `ed_ev_info`

Die Datenblattansicht zeigt nun mindestens:

- `id` (technische ID)
- `to_ev_id`
- `typ`
- `date_time`
- `bemerkung`
- `from_person`
- `to_person`
- `Geändert`
- `Aktionen`

Die `*`-Zeile besitzt Eingabefelder für alle sechs fachlichen Tabellenfelder.
