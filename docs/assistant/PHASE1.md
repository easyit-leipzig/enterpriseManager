# easyIT Enterprise / DataForm5 – Assistant-System – Phase 1

## Ziel

Phase 1 führt einen zentralen, wiederverwendbaren Assistant-Core ein. Fachlogik wie DataForm-Erzeugung, Beziehungen oder Events gehört ausdrücklich in spätere Phasen und wird nicht in UI-Seiten dupliziert.

## Enthaltene Komponenten

- `AssistantInterface` – verbindlicher Vertrag für jeden Assistenten.
- `AssistantContext` – einheitlicher Übergabekontext für Projekt, DataForm, Datensatz, Route, Eingaben und Metadaten.
- `AssistantStep` – standardisierte Schrittdefinition mit Zuständen `pending`, `current`, `complete`, `blocked`.
- `AssistantResult` – standardisiertes Ergebnisobjekt für HTML- und API-Ausgaben.
- `AssistantRegistry` – zentrale Registry aller Assistenten.
- `AssistantManager` – zentrale Ausführung mit Fehlerkapselung.
- `AssistantContextFactory` – erzeugt den Kontext aus einem Web-Request.
- `CoreStatusAssistant` – erster ausführbarer Diagnose-Assistent.
- `admin/assistants/index.php` – zentrale Übersicht.
- `admin/assistants/run.php` – generischer Runner.
- `admin/assistants/api.php` – JSON-Endpunkt.
- `assets/js/assistant.js` – Client-API.
- `assets/css/assistant.css` – neutrales Layout ohne farbige/Verlauf-Aktionsbuttons.

## Integrationsregel

Dieses Paket ist ein Overlay. Inhalt des Overlay-Verzeichnisses in das Root-Verzeichnis von `easyIT-Enterprise` kopieren.

Die bestehende zentrale Button-Registry wird nicht ersetzt. Phase 1 erzeugt bewusst keine neue lokale CRUD-Buttonwelt. Sobald ein Assistent echte Aktionen anbietet, müssen Grafik, `title` und `aria-label` aus der vorhandenen zentralen Button-Registry bezogen werden.

## Aufruf

Nach Integration:

`/admin/assistants/`

Für die Diagnose:

`/admin/assistants/run.php?assistant=core.status`

JSON:

`/admin/assistants/api.php?assistant=core.status&step=context`

## Erwartetes Ergebnis

Der Core-Status-Assistent zeigt drei Schritte:

1. Kontext erfassen
2. Assistant-Core prüfen
3. Bereit für Fachassistenten

Im Ergebnis muss `coreReady` den Wert `true` haben.

## Nächste Phase

Phase 2: DataForm-Assistent mit Datenquelle/Hauptquelle, Primärschlüssel, Volltextsuche, Filter, Pagination, CRUD-Optionen und Feldgrundkonfiguration.
