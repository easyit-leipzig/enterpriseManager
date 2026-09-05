# HF76 PUBLISH6 – Workflow-Kontext und Veröffentlichungsbereinigung

## Behoben

- Workflow-Navigation verliert die aktuelle DataForm-ID nicht mehr.
- `workflow.php?project=<id>` zeigt ohne DataForm-Auswahl keine fehlerhafte Workflow-Maske mehr, sondern eine Auswahl der vorhandenen DataForms.
- Ungültige DataForm-IDs werden verständlich abgefangen.
- Breadcrumb: `DataForms → <DataForm> → Workflow`; die doppelte Stufe `DataForm → DataForm` entfällt.
- Workflow-Explorer führt Datenmodell, Layout & Verhalten, Workflow und Datensätze immer mit derselben DataForm-ID.
- Sichtbare Entwicklungsmarker `Phase 12`, `Phase 15`, `Phase 16` sowie weitere DataForm-Phase-/Dev-Texte wurden aus der Benutzeroberfläche entfernt.

## Navigation

Ist im Workspace bereits ein DataForm ausgewählt, lautet der Workflow-Link nun sinngemäß:

`workflow.php?project=<project-id>&dataform=<dataform-id>`

Ohne ausgewähltes DataForm öffnet der Workflow-Einstieg eine DataForm-Auswahl.
