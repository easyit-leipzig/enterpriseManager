# RC1.8-FC1-HF21 – Default Value Providers

Das DataForm-Feldmodell besitzt drei explizite Standardwert-Modi:

1. `defined` – **Wie definiert [Wert]**
   Der gespeicherte `default_value` wird beim Anlegen eines Datensatzes verwendet.

2. `current_timestamp` – **CURRENT_TIMESTAMP**
   Der Wert wird bei jedem neuen Datensatz serverseitig neu erzeugt.
   - `date`: `YYYY-MM-DD`
   - `datetime`: `YYYY-MM-DDTHH:MM`
   - `text` / `textarea`: `YYYY-MM-DD HH:MM:SS`
   Für inkompatible Feldtypen wird die Konfiguration abgewiesen.

3. `javascript` – **JavaScript-Funktion**
   In `configuration_json` wird nur `default_js_provider` gespeichert.
   Die eigentliche Funktion liegt in `products/dataform/assets/default-providers.js`.
   JavaScript-Provider werden ausschließlich beim Anlegen eines neuen Datensatzes ausgeführt.

Mitgelieferte Provider: `uuid`, `today`, `currentIso`, `timestampMillis`.

Eigene Provider:
`window.easyITDefaultProviders.customerNumber = function(context) { return 'KD-' + Date.now(); };`

Es wird weder `eval()` noch `new Function()` verwendet.
