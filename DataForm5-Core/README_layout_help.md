# DataForm 5 – Layout Help Add-on

## Ziel

Dieses Paket ergänzt das zentrale DataForm-5-Hilfesystem um die kontextbezogene
Hilfe für die Designer-Seite **Layout**.

## Zielordner

In den bestehenden Projektbaum mergen:

    easyIT-Enterprise/DataForm5-Core/

## Enthaltene Dateien

    DataForm5-Core/
    ├── config/
    │   └── help_registry.d/
    │       └── dataform-layout.php
    ├── docs/
    │   └── dataform/
    │       └── layout.html
    ├── help/
    │   └── dataform/
    │       └── layout.json
    ├── system/
    │   └── help/
    │       ├── HelpRegistry.php
    │       └── HelpService.php
    ├── examples/
    │   └── layout-help-integration.js
    └── tests/
        └── layout_help_smoke.php

## Help-ID

    dataform.layout

## Aufruf im permanenten Hilfefenster

    DataFormHelp.show(
        "dataform.layout",
        "#dataform-help-panel"
    );

## Vollständige Dokumentation

    DataFormHelp.open("dataform.layout");

Bei Verwendung der zuvor eingerichteten öffentlichen Help-Route entspricht das:

    /help.php?id=dataform.layout

## Registry-Erweiterung

Das Paket erweitert `HelpRegistry.php` so, dass zusätzlich zum bestehenden

    config/help_registry.php

auch modulare Registry-Fragmente aus

    config/help_registry.d/*.php

geladen werden.

Damit müssen neue Hilfepakete künftig nicht mehr die zentrale Registry-Datei
überschreiben.

## Integration auf der Layout-Seite

Der Seitenkontext sollte beim Aufruf der Layout-Seite auf

    dataform.layout

gesetzt werden.

Beispiel:

    DataFormHelp.show(
        "dataform.layout",
        "#dataform-help-panel"
    );

Optional kann ein Hilfe-Button mit

    data-help-id="dataform.layout"

versehen werden.

## Inhalt der Hilfe

- Kurzhilfe
- Zusammenhang Einstellungen / Formular / Realvorschau
- Schritt-für-Schritt-Assistent
- Elementtypen Seite / Gruppe / Registergruppe
- Erklärung der Layoutstruktur
- Praxisbeispiele
- typische Fehler
- empfohlener Workflow
- Expertenmodus
- Help-System-Integration

## Test

Im Projektroot:

    php tests/layout_help_smoke.php

Erwartet:

    PASS: DataForm5 Layout Help Package
