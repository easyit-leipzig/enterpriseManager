# Assistant Phase 11 – Oberflächenintegration

Phase 11 integriert die Assistenten kontextbezogen in easyIT Enterprise / DataForm5.

## Oberflächen

- `project.management` – Projektverwaltung
- `project.detail` – aktuelles Projekt
- `datasource` – Datenquelle
- `dataform` – DataForm
- `dataform.fields` – Felder
- `dataform.relations` – Beziehungen
- `dataform.events` – Events
- `dataform.actions` – Aktionen/Buttons
- `dataform.diagnostics` – Diagnose
- `assistant.center` – zentrale Übersicht

## Kontextübergabe

Die Starter-URLs übernehmen, soweit vorhanden:

- `project_id`
- `dataform_id`
- `record_id`
- `surface`

Damit bleibt der aufrufende Kontext auch über Wizard-Schritte erhalten.

## PHP-Drop-in

Auf einer bestehenden Seite kann direkt eingebunden werden:

```php
$assistantSurface = 'dataform';
require __DIR__ . '/../assistants/_launcher.php';
```

Wenn Projekt/DataForm nicht bereits in der URL stehen, kann vorher ein `AssistantContext` gesetzt werden.

## JavaScript-Drop-in

```html
<div data-eit-assistant-launcher
     data-surface="dataform"
     data-project-id="muster-csv"
     data-dataform-id="ed_ev"
     data-record-id="42"></div>
<script src="/assets/js/assistant-launcher.js" defer></script>
```

Die Assistentenliste wird aus `admin/assistants/integration-api.php` geladen. Es gibt keine lokale Kopie der Assistenten- oder Buttondefinitionen.

## UI-Regeln

- keine lokalen farbigen/verlaufsbasierten Aktionsbuttons
- Assistenten-Starter werden als Links gerendert
- DataForm-spezifische Assistenten werden ohne DataForm-Kontext deaktiviert
- Titel und Beschreibungen stammen aus der zentralen Assistant-Registry
