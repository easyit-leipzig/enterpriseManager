# RC1.8-FC1-HF14 – DataForm Runtime Layout Repair

## Ursache / Ansatz
HF13 hatte nur statisch geprüft, ob Enterprise-CSS und Designer-Klassen vorhanden sind. Der reale Browserlauf zeigte weiterhin unformatiertes HTML.

HF14 entkoppelt deshalb die kritische DataForm-Workspace-Ausgabe vom relativen `../../`-Layoutpfad:

- eigene Runtime-Shell `products/dataform/system/WorkspaceLayout.php`
- Projektwurzel-URL aus dem tatsächlich ausgeführten `SCRIPT_NAME`
- absolute Asset-URLs zur aktuell laufenden easyIT-Installation
- eigener `products/dataform/assets/workspace.css`
- minimales eingebettetes Runtime-Fallback-CSS
- sichtbarer Runtime-Marker `data-easyit-runtime="dataform-hf14"`
- DataForm `index.php` verwendet `dataform_runtime_render()` statt des generischen `render_page()` für den Workspace

## Runtime-Test
`tests_rc18_phase6_dataform_runtime_layout.php` rendert die komplette HTML-Shell ohne Datenbank und prüft `<head>`, beide Stylesheets, Topbar, Main-Shell, Workspace/Designer-Klassen und absolute URLs.
