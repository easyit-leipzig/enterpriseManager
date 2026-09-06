# Assistant Phase 16 – DataForm-Vorlagenbibliothek

Phase 16 erweitert die in Phase 15 eingeführten DataForm-Vorlagen zu einer verwalteten Bibliothek.

## Neuer Assistent

`dataform.template-library`

## Funktionen

- sichtbare Vorlagen im aktuellen Kontext anzeigen
- systemweite Vorlagen unter `storage/assistant/templates/`
- projektbezogene Vorlagen unter `projects/<project>/config/assistant/templates/`
- Projektisolation: projektbezogene Vorlagen sind nur im jeweiligen Projekt sichtbar
- bestehende Phase-15-Vorlagen ohne Library-Metadaten bleiben rückwärtskompatibel und gelten als systemweit
- Vorlagenname und Beschreibung ändern
- Vorlage in einen systemweiten oder projektbezogenen Bereich kopieren
- Freigabe zwischen `system` und `project` ändern
- versionierter Verlauf unter `.versions/<template-id>/`
- Versionsverlauf bleibt beim Freigabewechsel erhalten
- Import per JSON-Datei oder JSON-Text
- Importvalidierung gegen `easyit.assistant.dataform-template.v1`
- rekursives Secret-Sanitizing vor dem Speichern
- Export als `*.dataform-template.json`
- kontrolliertes Löschen: exakter Vorlagenname + Bestätigung; anschließend Verschieben nach `.trash/`

## Rückwärtskompatibilität zu Phase 15

Der Assistent `dataform.templates` zeigt im Projektkontext nun sowohl systemweite als auch projektbezogene Vorlagen. Projektbezogene Vorlagen können damit weiterhin über den bestehenden Mapping-/Diagnose-/Klon-Workflow angewendet werden.

## Freigabemodell

- `system`: für alle Projekte sichtbar
- `project`: nur für `ownerProject` sichtbar

Die Vorlage selbst enthält dazu unter `library` mindestens:

```json
{
  "visibility": "system|project",
  "ownerProject": null,
  "version": 1
}
```

## Sicherheit

- Importgröße ist begrenzt.
- Importierte Bundles müssen mindestens `dataform.create` enthalten.
- Passwörter, Secrets, Tokens und Credentials werden rekursiv entfernt.
- Löschen ist Soft-Delete in einen Bibliotheks-Papierkorb.
- Bestehende Zielvorlagen mit gleicher ID werden beim Freigabewechsel nicht überschrieben.

## Integration

Phase 16 ist in `project.detail`, `dataform` und `assistant.center` eingebunden. Der Wizard-Zustand wird über den bestehenden persistenten `AssistantStateStore` gespeichert und damit auch historisiert.
