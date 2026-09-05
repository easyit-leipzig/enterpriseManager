# RC1.8-FC1-HF35 – Global CRUD 3D UI

HF35 integriert ein zentrales 3D-Stylesheet für CRUD-Aktionen in das gesamte easyIT-Enterprise-Projekt.

## Zentrale Datei

`assets/css/easyit-crud-3d-buttons.css`

Das Stylesheet wird in `system/ui/layout.php` nach allen normalen Seiten-Stylesheets geladen. Damit gilt die Darstellung automatisch für alle Seiten, die `render_page()` verwenden, insbesondere:

- Dashboard / Projekte
- Benutzer und Rollen
- Capabilities
- Audit
- Module
- Betrieb / Recovery
- Setup-nahe Enterprise-Seiten
- DataForm-Unterseiten, die über das Enterprise-Layout gerendert werden

Die DataForm-Standalone-Runtime und `WorkspaceLayout` binden dasselbe Stylesheet zusätzlich direkt ein.

## Darstellung

- CREATE / Neu / Hinzufügen: grüner 3D-Button mit Plus
- READ / Öffnen / Anzeigen: blauer 3D-Button mit Augen-Symbol
- EDIT / UPDATE: orangefarbener 3D-Button mit Editor/Stift-Symbol
- SAVE: grün-türkiser 3D-Speicherbutton
- DELETE / REMOVE / DROP: roter 3D-Button mit Kreuz

Neben explizitem `data-crud="..."` erkennt das Stylesheet vorhandene easyIT-Formulare über `input[name="action"]`, darunter `create`, `update`, `save`, `delete`, `remove`, `create_*`, `update_*`, `save_*`, `delete_*`, `drop_*`, `add_*` sowie weitere bestehende CRUD-Muster.

## Browser

Die automatische Erkennung verwendet CSS `:has()`. Der aktuell eingesetzte Chrome unterstützt diese Selektoren. Für neue bzw. später konsolidierte Oberflächen ist `data-crud="create|read|edit|save|delete"` die dauerhaft eindeutigste Markierung.
