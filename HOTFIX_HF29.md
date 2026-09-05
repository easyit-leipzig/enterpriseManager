# RC1.8-FC1-HF29 – Relations Runtime Repair

`relations.php` verwendete noch den entfernten Legacy-Renderer `render_enterprise_page()`.
Dadurch brach die Seite mit einem Fatal Error ab.

HF29 stellt die verbliebenen DataForm-Standalone-Seiten `relations.php`,
`modules.php` und `packages.php` auf den aktuellen `render_page()`-Renderer um.

Zusätzlich unterstützt `render_page()` jetzt seitenbezogene Stylesheets. Die drei
DataForm-Seiten laden darüber `products/dataform/assets/workspace.css`, erhalten
den korrekten Root-Pfad `../../`, die Enterprise-Navigation, den authentifizierten
Benutzerkontext und die `workspace-page`-Bodyklasse.

Ein Regressionstest stellt sicher, dass im produktiven PHP-Code kein Aufruf von
`render_enterprise_page()` mehr übrig bleibt.
