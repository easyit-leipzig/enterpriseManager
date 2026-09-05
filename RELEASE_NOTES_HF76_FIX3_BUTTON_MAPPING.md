# HF76-FIX3 – DataForm-Aktionsbutton-Zuordnung

Korrektur der DataForm-Liste im Workspace.

- `Öffnen` ist explizit dem Registry-Key `formular` und damit `assets/img/formular.png` zugeordnet.
- `Datensätze` ist explizit dem neutralen Datensatz-Key `normaler_ds` und damit `assets/img/normaler_ds.png` zugeordnet; der Aktionsbutton zeigt keinen sichtbaren Text.
- `Löschen` ist explizit `loeschen.png` zugeordnet.
- `Neues DataForm` ist explizit `neu.png` zugeordnet.
- `products/dataform/runtime.php` lädt jetzt die zentrale Browser-Registry `assets/js/easyit-button-registry.js`. Damit greift die zentrale Image-Button-Regel auch im alten Runtime-Renderer.
- Keine farbigen Aktionsbutton-Hintergründe: die registrierten PNGs bleiben die verbindliche visuelle Darstellung.

Regressionstest: `tests_rc18_phase6_dataform_hf76_fix3_dataform_action_buttons.php`.
