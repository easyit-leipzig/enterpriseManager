# RC1.8-FC1-HF76 FIX7 – Projektweite Button-Migration

FIX7 führt die Buttonset-Regel über den gesamten easyIT-Enterprise-/DataForm-Baum zusammen.

## Änderungen

- Der bisher historisch benannte Stylesheet `assets/css/easyit-crud-3d-buttons.css` enthält keine 3D-/Gradienten-Skins mehr, sondern nur noch die kanonische Image-Button-Schicht.
- 52 zentrale Buttontypen werden ausschließlich über die gelieferten PNG-Dateien unter `assets/img/` dargestellt.
- Alle Aktionsbuttons erhalten ihre Semantik, Titel und ARIA-Beschriftung aus `system/ui/ButtonRegistry.php`.
- Alte Klassen wie `primary`, `secondary`, `danger`, `success`, `warning` bestimmen nicht mehr die visuelle Buttonfarbe.
- Vor der JavaScript-Dekoration werden Aktionsbuttons bereits transparent zurückgesetzt, sodass kein farbiger Legacy-Button sichtbar aufblitzt.
- `link-button`-Aktionen sowie Passwort-Anzeige-Schalter werden ebenfalls zentral dekoriert.
- Die sieben `*_ds`-Typen sind strikt auf Datensatznavigation beschränkt.
- DataForm öffnen = `formular.png`; Datensätze anzeigen = `anzeigen.png`.
- Der Produktionsquellbaum wurde statisch auf buttonartige Controls geprüft: 310 Controls, davon 8 reine Menü-/Tabnavigation, 48 explizit registriert und 254 zentral ableitbar; 0 ungelöste Aktionsbuttons.

## Kompatibilität

Die Datei- und Layoutnamen aus früheren Hotfixes bleiben erhalten, damit bestehende Includes nicht brechen. Historische Tests, die farbige 3D-Hintergründe voraussetzten, wurden auf die neue verbindliche Image-Only-Regel umgestellt.
