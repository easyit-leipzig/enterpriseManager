# DataForm 5 – Gebundene Parent→Child-Formulare

## Ziel

Dieses Add-on implementiert die verbindliche Belegung des gebundenen
Kindfeldes mit dem aktuellen Hauptwert.

Beispiel:

    ed_ev.id = 9201
            ↓
    ed_ev_info.to_ev_id = 9201

Für einen neuen `ed_ev_info`-Datensatz steht `to_ev_id` damit sofort auf 9201.

## Standard

    inheritParentValue   = true     // verbindlich
    boundFieldReadonly   = true     // Voreinstellung

`inheritParentValue` ist bei einer echten Bindung nicht abschaltbar.

## Verhalten

### boundFieldReadonly = true

- Child-Feld wird mit dem Parent-Wert belegt.
- Feld ist im UI nicht änderbar.
- Server erzwingt den Parent-Wert vor INSERT/UPDATE erneut.
- Manipulierte Request-Werte werden ignoriert.

### boundFieldReadonly = false

- Child-Feld wird zunächst mit dem Parent-Wert belegt.
- Benutzer darf den Wert ändern.
- Ein explizit geänderter Wert bleibt beim Speichern erhalten.
- Der Datensatz kann danach aus der aktuellen Parent-Ansicht verschwinden.

### Parent noch nicht gespeichert

Ohne persistierten Hauptschlüssel darf kein echter Kinddatensatz angelegt
werden. Der Child-New-Button soll deaktiviert werden.

## Paket in den Projektbaum mergen

Ziel:

    easyIT-Enterprise/DataForm5-Core/

## Migration

MySQL:

    migrations/admin/mysql/005_bound_form_binding_settings.sql

SQLite:

    migrations/admin/sqlite/005_bound_form_binding_settings.sql

Die neue Metadatentabelle lautet:

    df_bound_form_binding_settings

Sie enthält:

    relation_id
    inherit_parent_value
    bound_field_readonly

## Serverintegration

Beim Erzeugen eines neuen Kinddatensatzes:

    $defaults = $bindingService->applyNewRecordDefaults(
        $binding,
        $parentRecord,
        $defaults
    );

Vor INSERT/UPDATE:

    $submitted = $bindingService->enforceBeforePersist(
        $binding,
        $parentRecord,
        $submitted,
        $isInsert
    );

Beim Wechsel des Parent-Datensatzes:

    $filter = $bindingService->buildChildFilter(
        $binding,
        $parentRecord
    );

## Browserintegration

Einbinden:

    <script src="/assets/js/dataform-bound-form.js"></script>

Beim Parent-Wechsel:

    DataFormBoundBinding.applyParentChange({
        binding,
        parentRecord,
        childField:
            document.querySelector('[name="to_ev_id"]'),
        childNewButton:
            document.querySelector('[data-action="new-child"]'),
        onFilter: filter => childRecordset.setFilter(filter),
        onDefaults: defaults => childRecordset.setNewDefaults(defaults)
    });

## Designer

Unter der Relation bzw. der Konfiguration des gebundenen Formulars:

    ☑ Hauptwert automatisch übernehmen
      (verbindlich / nicht abschaltbar)

    ☑ Gebundenes Feld schreibgeschützt
      (Default = ja)

## dataformContext

`relations.parent` wird erweitert um:

    "binding": {
        "inherited": true,
        "readonly": true
    }

## Hilfe

Help-ID:

    dataform.boundForm

Permanente Hilfe:

    DataFormHelp.show(
        "dataform.boundForm",
        "#dataform-help-panel"
    );

Vollständige Hilfe:

    DataFormHelp.open("dataform.boundForm");

## Tests

    php tests/bound_form_binding_test.php

Erwartet:

    PASS: 10/10

## Korrektur v1.0.1

### 1. Neue Kindzeile

Bei vorhandenem Parent ist ein Platzhalter wie

    Eltern-Datensatz wählen

in der gebundenen Child-Spalte nicht zulässig.

Beispiel:

    ed_ev.id = 9201

muss in der *-Zeile unmittelbar ergeben:

    ed_ev_info.to_ev_id = 9201

sichtbar:

    #9201

Die Runtime bindet sowohl bereits vorhandene New-Record-Zeilen als auch später
per AJAX/Rendering erzeugte Zeilen. Dafür stehen zur Verfügung:

    DataFormBoundBinding.bindNewRow(...)
    DataFormBoundBinding.bindExistingNewRows(...)
    DataFormBoundBinding.observeNewRows(...)

### 2. Paginierung

Die Paginierung muss immer direkt unterhalb der Datensätze stehen.

Sie darf insbesondere nicht Bestandteil des horizontal scrollbaren
Datensatzcontainers sein.

Einbinden:

    <link
        rel="stylesheet"
        href="/assets/css/dataform-recordset-layout.css"
    >

    <script
        src="/assets/js/dataform-recordset-layout.js"
    ></script>

Initialisieren:

    DataFormRecordsetLayout.observe(
        childRecordsetRoot
    );

Empfohlener DOM-Aufbau:

    recordset
      ├── recordset-data / scroll
      │     ├── bestehende Datensätze
      │     └── neue *-Zeile
      └── recordset-pagination

