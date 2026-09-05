# DataForm 5 – dataformContext Help Package

## Ziel

Dieses Paket integriert die vollständige `dataformContext`-Referenz in den
DataForm-5-Projektbaum und stellt sie über eine zentrale Help-Registry sowohl
als permanente kontextbezogene Hilfe als auch als vollständige HTML-Dokumentation
zur Verfügung.

## Zielpfad

Den Inhalt des enthaltenen Ordners `DataForm5-Core/` in den bestehenden
Projektordner

    easyIT-Enterprise/DataForm5-Core/

kopieren bzw. mergen.

## Enthaltene Komponenten

    DataForm5-Core/
    ├── assets/
    │   ├── css/dataform-help.css
    │   └── js/dataform-help.js
    ├── config/help_registry.php
    ├── docs/dataform/dataformContext.html
    ├── examples/
    │   ├── afterSaveEdEv.js
    │   └── recordset-designer-help.html
    ├── help/dataform/
    │   ├── callbacks.json
    │   ├── dataform-context.json
    │   ├── designer.json
    │   └── recordset.json
    ├── public/
    │   ├── api/dataform-help.php
    │   └── help.php
    ├── system/help/
    │   ├── HelpController.php
    │   ├── HelpRegistry.php
    │   └── HelpService.php
    └── tests/help_package_smoke.php

## Öffentliche Aufrufe

Vollständige Dokumentation:

    /help.php?id=dataformContext

JavaScript-Beispiele:

    /help.php?id=dataformContext.examples

Direkt zur Hilfe "Nach Speichern":

    /help.php?id=recordset.afterSave

JSON-Hilfe für das permanente Hilfefenster:

    /api/dataform-help.php?id=recordset.afterSave

## JavaScript-Integration

Im Layout des DataForm-/Recordset-Designers:

    <link rel="stylesheet" href="/assets/css/dataform-help.css">
    <script src="/assets/js/dataform-help.js"></script>

    <aside id="dataform-help-panel"></aside>

    <script>
    DataFormHelp.show(
        "recordset.afterSave",
        "#dataform-help-panel"
    );
    </script>

Die vollständige Dokumentation kann aus jeder Seite zentral geöffnet werden:

    DataFormHelp.open("dataformContext");

oder:

    DataFormHelp.open("recordset.afterSave");

## Empfohlene Zuordnung im UI

DataForm-Designer:
    dataform.designer

Allgemeine Kontextreferenz:
    dataformContext

JavaScript-Beispiele:
    dataformContext.examples

Recordset / Callbacks:
    recordset.callbacks

Feld "Vor Speichern":
    recordset.beforeSave

Feld "Nach Speichern":
    recordset.afterSave

Feld "Vor Löschen":
    recordset.beforeDelete

Feld "Nach Löschen":
    recordset.afterDelete

Navigation:
    recordset.afterNavigate

## Wichtige Architekturregel

Einzelne Seiten verlinken nicht direkt auf
`docs/dataform/dataformContext.html`.

Stattdessen wird immer eine zentrale Help-ID verwendet. Damit können Pfade,
Dokumente und Anker später zentral geändert werden.

## Callback-Standard

Im Recordset:

    afterSaveEdEv(dataformContext)

Im JavaScript:

    DataFormCallbacks.register(
        "afterSaveEdEv",
        afterSaveEdEv
    );

Ein `eval()` ist nicht vorgesehen.

## Bestehender Router

Falls DataForm 5 bereits einen zentralen Router verwendet, können
`public/help.php` und `public/api/dataform-help.php` entfallen. In diesem Fall
werden `HelpRegistry`, `HelpService` und `HelpController` direkt an vorhandene
Routen gebunden.

Empfohlene logische Routen:

    GET /help/{helpId}
    GET /api/help/{helpId}

Die Help-IDs und deren Semantik bleiben unverändert.
