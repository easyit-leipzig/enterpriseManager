# Assistant Phase 13 – persistente Assistenten- und Workflow-Konfiguration

Phase 13 ergänzt die sessionbasierte Arbeitsweise um eine projektbezogene, dauerhafte Persistenz. Die Session bleibt der schnelle Laufzeitzustand; projektbezogene Assistentenzustände werden zusätzlich atomar unter `projects/<projekt-id>/config/assistant/state/` gespeichert.

## Persistierte Fachassistenten

- `datasource.configure`
- `dataform.create`
- `dataform.fields`
- `dataform.relations`
- `dataform.events`
- `dataform.actions`
- `dataform.diagnostics`
- `workflow.standard`

`project.create`, `project.recovery` und `core.status` werden bewusst nicht als dauerhafter Wizard-Zustand gespeichert.

## Wiederaufnahme

Der Standardworkflow speichert Projekt, DataForm, optionale Entscheidungen, Fortschritt und die zuletzt besuchten Teilschritte der Fachassistenten. Ein minimaler Zeiger unter `storage/assistant/active-standard-workflow.json` enthält nur das zuletzt aktive Projekt/DataForm. Dadurch kann eine neue Browser-Sitzung den letzten Workflow auch ohne Query-Parameter wieder aufnehmen.

Wird ein anderer Projektkontext geöffnet, lädt der Workflow den Zustand dieses Projekts. Skip-Entscheidungen oder Navigationspositionen werden nicht zwischen Projekten vermischt.

## Sicherheitsregeln

Persistente JSON-Dateien werden über temporäre Dateien und atomisches Rename geschrieben. Ein Lock schützt parallele Schreibvorgänge. Klartextfelder wie `password`, Secrets, Tokens, Upload- und Temp-Daten werden vor dem Schreiben entfernt. `passwordRef` bleibt als zulässige Referenz erhalten.

## Backup und Restore

Da die Zustände innerhalb des Projektverzeichnisses liegen, werden sie automatisch mit dem Projektbackup gesichert. Beim Restore unter einer neuen Projekt-ID werden `projectId`, projektbezogene Scopes, Pfade und die scopeabhängigen Dateinamen der Assistentenzustände auf die neue Projekt-ID retargetet.

## Schema

Persistente Zustände verwenden `easyit.assistant.state.v1`. Der globale Wiederaufnahmezeiger verwendet `easyit.assistant.workflow-pointer.v1`.
