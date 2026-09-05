# HF76-FIX5 – Buttonset-Darstellung

## Ziel
Alle Aktionsbuttons verwenden projektweit die registrierten PNG-Grafiken aus `assets/img/`. Farbige oder verlaufende CSS-Buttonflächen dienen nicht mehr als sichtbare Aktionsträger.

## Umsetzung
- alle 52 PNG-Dateien aus `buttonset_dbfrontend.zip` erneut bytegenau nach `assets/img/` übernommen;
- zentrale Registry bleibt `system/ui/ButtonRegistry.php`;
- zentrale Titel und `aria-label` bleiben Registry-gesteuert;
- `button`, `a.button` und andere registrierte HTML-Aktionscontainer erhalten durch `assets/js/easyit-button-registry.js` ein echtes `<img class="easyit-button-image">`;
- der umgebende Button wird transparent und ohne CSS-Verlauf, Schatten oder Ersatzsymbol dargestellt;
- `input[type=submit|button|reset]` kann keine Kindgrafik enthalten und nutzt deshalb das gleiche Registry-PNG als erzwungenes Bild-Background;
- die Bild-URL wird aus der tatsächlichen URL der zentralen Registry-JavaScriptdatei abgeleitet und funktioniert damit auch bei umbenanntem Enterprise-Ordner;
- DataForm-Zuordnungen bleiben: `Öffnen -> formular.png`, `Datensätze -> normaler_ds.png`, `Neu -> neu.png`, `Löschen -> loeschen.png`.

## Prüfung
- 52/52 Registry-Einträge;
- 52/52 PNG-Dateien bytegenau identisch mit dem gelieferten Buttonset;
- FIX5-Hard-Rendering-Test 14/14 PASS;
- vollständige Root-Regression 126/126 PASS;
- PHP-Syntax 827/827 PASS;
- JavaScript-Syntax 4/4 PASS.
