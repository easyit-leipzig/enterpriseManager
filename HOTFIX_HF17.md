# RC1.8-FC1-HF17 – DataForm Control-Flow Repair

## Ursache
HF16 bewies, dass Apache die richtige Installation und die richtige `index.php` auf der Festplatte sieht. Im Browser erschien dennoch die alte Workspace-Ausgabe ohne HF16-Shell. Damit musste der kritische `index.php`-Ausführungspfad vollständig umgangen werden.

## Korrektur
- neuer eindeutiger Frontcontroller `products/dataform/runtime.php`
- Enterprise-Projekte öffnen DataForm direkt über `runtime.php`
- `products/dataform/index.php` ist nur noch ein 302-Kompatibilitätsredirect
- `.htaccess` rewritet `index.php` vor PHP direkt auf `runtime.php`, sofern `mod_rewrite` aktiv ist
- sichtbarer Marker `HF17 DATAFORM RUNTIME ACTIVE`
- Response-Header `X-EasyIT-DataForm-Runtime: HF17`
- `runtime-proof.php` weist Hash und realen Pfad von `runtime.php` nach

Damit gibt es drei unabhängige Wege weg vom problematischen alten `index.php`-Bytecode: direkter Enterprise-Link, Apache-Rewrite und PHP-Redirect.
