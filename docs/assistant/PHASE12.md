# Assistant Phase 12 – Workflow-Assistent

Phase 12 verbindet die bisherigen Fachassistenten zu einem durchgängigen, wiederaufnehmbaren Standardablauf.

## Standardworkflow

1. Projekt
2. Datenquelle
3. DataForm
4. Felder
5. Beziehungen
6. Events
7. Aktionen
8. Diagnose

Der Workflow speichert keine zweite Kopie der Fachkonfigurationen. Er liest die vorhandenen Zustände der jeweiligen Assistenten und hält nur Workflow-Metadaten wie aktives Projekt, aktives DataForm, optionale Bestätigungen und die zuletzt offene Station.

## Pflicht- und optionale Schritte

Pflichtschritte können nicht übersprungen werden. Sie werden automatisch als abgeschlossen erkannt, sobald der jeweilige Fachassistent einen gültigen Zustand besitzt.

Optional sind derzeit:

- Beziehungen – kann ausdrücklich mit „Keine Beziehungen erforderlich“ abgeschlossen werden.
- Events – kann ausdrücklich mit „Keine Events erforderlich“ abgeschlossen werden.

Diese Bestätigung ist im Workflow-Zustand nachvollziehbar und kann wieder aufgehoben werden.

## Fortschritt und Wiederaufnahme

Der Workflow ermittelt:

- abgeschlossene Schritte
- offene Schritte
- blockierte Folgeschritte
- Fortschritt in Prozent
- nächste offene Station

Aus jedem über den Workflow geöffneten Fachassistenten kann direkt zum Workflow-Fortschritt zurückgekehrt werden. `workflow=standard` bleibt bei der Schrittnavigation erhalten.

## Kontext

Der Workflow übernimmt und speichert:

- `project_id`
- `dataform_id`

Ein vorhandenes Projekt/DataForm kann im Workflow explizit gesetzt werden. Sobald Projekt- oder DataForm-Assistent einen Kontext erzeugen, kann dieser vom Workflow automatisch erkannt werden.

## Abschlussbedingung

Der Standardworkflow ist erst abgeschlossen, wenn alle Pflichtschritte valide sind und die Gesamtdiagnose keinen `FAIL` enthält.

Der JSON-Export verwendet das Schema:

`easyit.assistant.workflow.v1`

## Sicherheit und Architektur

- keine Parallelkonfiguration der Fachassistenten
- keine Pflichtschritte überspringbar
- keine destruktiven Tests durch den Workflow
- Diagnose bleibt nichtdestruktiv
- bestehende zentrale Button-Registry bleibt verbindlich
- keine farbigen oder verlaufenden lokalen Workflow-Aktionsbuttons
