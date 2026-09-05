# RC1.8-FC1-HF39 – n:1-/Lookup-Beziehungen

HF39 ergänzt den DataForm-Beziehungsdesigner um eine explizite **n:1-/Lookup-Beziehung**. Damit kann ein Datensatz eines DataForms genau einen Referenzdatensatz aus einem anderen DataForm auswählen, ohne die fachliche Bedienrichtung künstlich als umgedrehte 1:n-Eltern-Kind-Beziehung modellieren zu müssen.

## Ziel

Für den aktuellen Ereignisaufbau kann nun direkt modelliert werden:

- `ed_ev_info.typ -> ed_ev_type.id`
- `ed_ev_info.from_person -> ed_ev_person.id`
- `ed_ev_info.to_person -> ed_ev_person.id`

Dabei bleibt `ed_ev_info` das Ausgangs- bzw. Haupt-DataForm. `ed_ev_type` bzw. `ed_ev_person` dienen als Lookup-DataForms.

## Beziehungsdesigner

Der Typ **„n:1 / Lookup – ein Datensatz wählt einen Referenzdatensatz“** ergänzt die bisherigen Typen `1:n` und `n:m`.

Für n:1 werden separat gewählt:

1. Ausgangs-DataForm,
2. Lookup-DataForm,
3. Zuordnungsfeld im Ausgangs-DataForm,
4. Anzeigefeld im Lookup-DataForm.

Beispiel:

- Ausgangs-DataForm: `Ed Ev Info`
- Zuordnungsfeld: `typ`
- Lookup-DataForm: `Ed Ev Type`
- Anzeigefeld: `ev_type`

Persistiert wird `ed_ev_info.typ = ed_ev_type.id`; im Formular erscheint statt der technischen ID der Wert aus `ev_type`.

## Runtime

- n:1-Felder werden automatisch als Auswahlfeld gerendert.
- Die Optionen stammen aus dem Lookup-DataForm.
- Das konfigurierte Anzeigefeld wird für Auswahl, Listenansicht und Detailansicht verwendet.
- Beim Speichern wird geprüft, ob die gewählte Lookup-ID tatsächlich existiert.
- `is_required` der Beziehung macht das zugehörige Auswahlfeld zur Laufzeit zum Pflichtfeld.
- Ein Lookup-Datensatz kann nicht gelöscht werden, solange er von aktiven n:1-Beziehungen referenziert wird.

## Persistenz

Für n:1 wird das bereits vorhandene Schemafeld `dataform_relations.source_field_id` verwendet:

- `source_dataform_id`: DataForm, das die Auswahl besitzt,
- `source_field_id`: Feld, das die Lookup-ID speichert,
- `target_dataform_id`: Lookup-DataForm,
- `target_display_field_id`: optionales Anzeigefeld,
- `relation_type`: `n:1`.

Es ist keine Datenbankmigration notwendig, da `source_field_id` im aktuellen Relationsschema bereits vorhanden ist.

## Regressionstest

`tests_rc18_phase6_dataform_hf39_lookup_n1.php`
