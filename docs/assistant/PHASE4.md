# Assistant Phase 4 – Event- und JavaScript-Assistent

## Ziel

Phase 4 erweitert den kumulativen Assistant-Core um die Konfiguration und Laufzeitunterstützung für DataForm-Ereignisse.

Unterstützte Events:

- `beforeSave`
- `afterSave`
- `beforeDelete`
- `afterDelete`

Jede konfigurierte JavaScript-Methode erhält genau ein benanntes Übergabeobjekt. Standardname: `dataFormContext`.

Beispiel:

```javascript
afterSave(dataFormContext)
```

## Einheitliches DataFormActionContext-Schema

Das Objekt verwendet das Schema `easyit.dataform.action-context.v1` und enthält:

- `event`
- `action`
- `project`
- `dataForm`
- `record.original`
- `record.current`
- `changes`
- `relation`
- `pagination`
- `operation`
- `ui`
- `meta`

Damit kann dasselbe Übergabeobjekt auch für weitere DataForm-Aktionen wie Öffnen, Neu, Bearbeiten, Speichern oder Löschen verwendet werden.

## Abbruchlogik

`beforeSave` und `beforeDelete` sind blockierende Events. Liefert der Handler exakt `false`, wird die zugehörige Aktion nicht ausgeführt.

## Sicherheit

Handler werden als globale JavaScript-Namenspfade aufgelöst, beispielsweise `afterSave` oder `app.forms.afterSave`. `eval` wird nicht verwendet.
