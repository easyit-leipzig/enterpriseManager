# Phase 31 – Konsolidierung und Oberflächenintegration

## Ziel

Phase 31 friert den Funktionsumfang der Assistenten ein und konsolidiert die Oberflächenintegration. Neue Fachfunktionen werden in dieser Phase nicht eingeführt.

## Zentrale Katalogquelle

`system/assistant/integration/AssistantCatalog.php` ist ab Phase 31 die kanonische Quelle für:

- Assistentengruppen,
- Surface-Zuordnungen,
- Kontextanforderungen,
- History-Fähigkeit.

Damit entfallen mehrfach gepflegte Assistenten-ID-Listen in Startseite, Launcher und History-Navigation.

## Funktionsgruppen

1. Workflow und Einstieg
2. Projekt und Datenquelle
3. DataForm-Konfiguration
4. Vorlagen und Transport
5. Module, Releases und Migrationen
6. Trust, Signierung und Cluster

Alle 27 registrierten Assistenten müssen genau einmal im Katalog vorkommen. `assistant.center` muss alle registrierten Assistenten erreichen.

## Einheitliche Navigation

`AssistantPageNavigationRenderer` erzeugt die gemeinsame Toolbar für:

- Assistentenzentrale,
- Kontext-Assistenten,
- Wizard-Seiten,
- History-Seite.

Die zentralen UI-Aktionsmetadaten stammen aus `AssistantUiActionRegistry` und enthalten `buttonKey`, `title` und `ariaLabel`.

Es werden weiterhin keine farbigen oder verlaufenden CSS-Aktionshintergründe eingeführt.

## Integration in bestehende easyIT-Seiten

Serverseitig:

```php
<?php
$assistantSurface = 'dataform';
require __DIR__ . '/../assistants/_launcher.php';
?>
```

Oder clientseitig:

```html
<div
    data-eit-assistant-launcher
    data-surface="dataform"
    data-project-id="musterprojekt"
    data-dataform-id="ed_ev"
    data-record-id="42"></div>
<script src="/assets/js/assistant-launcher.js" defer></script>
```

Empfohlene Surfaces:

- `project.management`
- `project.detail`
- `datasource`
- `dataform`
- `dataform.fields`
- `dataform.relations`
- `dataform.events`
- `dataform.actions`
- `dataform.diagnostics`

## Resolver

Der Surface-Resolver unterscheidet nun explizit Projektübersichten (`index.php`, `list.php`, `manage.php`) von echten Projekt-Unterpfaden und erkennt DataForm-Unterseiten vor dem allgemeinen DataForm-Muster.

## History-Konsolidierung

Die History-Fähigkeit wird nur noch im Katalog definiert. Damit verwenden Wizard-Navigation und `history.php` dieselbe Regel. Der frühere Widerspruch für Modul-/Trust-Assistenten ist damit beseitigt.

## Tests

`tests/assistant/phase31_smoke.php` prüft unter anderem:

- 27 registrierte Assistenten,
- keine Katalog-Duplikate,
- vollständige Katalogabdeckung,
- vollständige `assistant.center`-Abdeckung,
- gültige Surface-Referenzen,
- Kontextweitergabe,
- zentrale Button-/ARIA-Metadaten,
- einheitliche Toolbar,
- Surface-Resolver,
- zentrale History-Fähigkeit.
