# Assistant Phase 5 – Aktions- und Button-Assistent

## Ziel

Phase 5 führt eine zentrale DataForm-Aktionsregistry ein. Aktionslogik und Button-Metadaten werden nicht mehr lokal an einzelnen Formularen definiert.

## Verbindliche Regeln

- Aktionsbuttons verwenden ausschließlich die zentrale Registry.
- `title` und `aria-label` stammen aus derselben zentralen Definition.
- Lokale Tooltip-/ARIA-Überschreibungen sind nicht zulässig.
- Farbige oder verlaufende CSS-Hintergründe für Aktionsbuttons sind nicht zulässig.
- Bildressourcen werden unter `assets/img/` erwartet und über `buttonKey` adressiert.
- `open` ist Alias für `show`; `create` ist Alias für `new`.
- `show` und `edit` bleiben getrennte Aktionen mit passenden Buttondefinitionen.
- Jede ausgeführte Aktion erhält `easyit.dataform.action-context.v1`.
- `save` und `delete` durchlaufen zusätzlich die in Phase 4 definierten Before-/After-Events.

## Registrierte Aktionen

`new`, `show`, `edit`, `save`, `delete`, `first`, `previous`, `next`, `last`.

## Laufzeit-JavaScript

`assets/js/dataform-actions.js` stellt bereit:

- `normalizeAction()`
- `canRun()`
- `dispatch()`
- `buttonMetadata()`

Die eigentliche CRUD-/Navigationsimplementierung wird als Funktion an `dispatch()` übergeben. Dadurch bleibt die Action-Schicht unabhängig von der konkreten Datenbankengine.
