# HF76-PUBLISH17 – DataFormActionContext 1.0 / afterSave

## Ziel
JavaScript-Aktionen eines DataForms erhalten ein benanntes, versioniertes Uebergabeobjekt. Fuer das Feld **Nach Speichern** kann direkt `afterSave(dataformContext);` eingetragen werden.

## DataFormActionContext 1.0
Das Objekt `dataformContext` enthaelt nach erfolgreichem Speichern:

- `schema`, `schema_version`, `object_name`
- `action` mit `name=save`, `phase=after`, Trigger und Zeitstempel
- `project`
- `dataform` einschliesslich View- und Storage-Modus
- `context` mit Create/Edit-Status, aktuellem Datensatz und Eltern-/Relationskontext
- `record.values` – tatsaechlich gespeicherter aktueller Datensatz
- `record.original_values` – Zustand vor dem Speichern
- `record.changes` – nur geaenderte Felder mit alt/neu
- `record.dirty_fields`
- `fields` – Feldmetadaten plus Wert/Originalwert/Dirty-Status
- `validation`
- `ui`
- `event`
- `result` mit Operation, Record-ID und Erfolg

## Callback
Im DataForm-Editor unter **Runtime-Events → Nach Speichern**:

```javascript
afterSave(dataformContext);
```

`detail` bleibt fuer vorhandene Skripte als Alias auf dasselbe Objekt erhalten.

## Laufzeiten
Die Schnittstelle ist sowohl in der Enterprise-DataForm-Runtime als auch in der exportierten schlanken HTML5-Projektanwendung implementiert. Der After-Save-Kontext wird serverseitig aus dem erfolgreich gespeicherten Datensatz erzeugt.

## Tests
- PUBLISH17 Action-Context: 14/14 PASS
- komplette Testsuite: 152/152 PASS
- PHP-Lint: 862/862 PASS
- JavaScript-Syntax: 6/6 PASS
- Reproduzierbarkeits-Gate: PASS
- Final Release Gate: PASS
