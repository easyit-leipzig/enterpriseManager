# RC1.8-FC1-HF76 – FIX2 Button Registry

## Änderung

Das vom Benutzer bereitgestellte Datenbankfrontend-Buttonset ist vollständig in easyIT Enterprise registriert. Alle 52 Einzelgrafiken liegen direkt unter `assets/img/` und werden über eine zentrale server- und clientseitige Registry angesprochen.

Für Aktionsbuttons gilt ab diesem Stand verbindlich: keine farbigen oder verlaufenden CSS-Hintergründe mehr. Die visuelle Aktion wird durch das registrierte PNG dargestellt. Bestehende Alt-Aktionen werden über `data-crud`, Formularaktion, URL und Beschriftung automatisch auf die Registry abgebildet.

## Zentrale Komponenten

- `system/ui/ButtonRegistry.php`
- `assets/js/easyit-button-registry.js`
- `assets/css/easyit-crud-3d-buttons.css` (abschließende Image-Button-Schicht)
- `assets/img/*.png` (52 registrierte Aktionen)
- `docs/BUTTON_REGISTRY_HF76_FIX2.md`

## DataForm

Die Datensatzzeiger verwenden `normaler_ds.png`, `aktueller_ds.png` und `neuer_ds.png`. Der Sprung zum ersten bzw. letzten Datensatz verwendet `erster_ds.png` und `letzter_ds.png`. Neu/Bearbeiten/Löschen/Speichern sind explizit auf die entsprechenden Registry-Einträge gebunden.
