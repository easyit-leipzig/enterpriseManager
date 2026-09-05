# HOTFIX HF64 – easyIT DataForm Branding

## Ziel

Das bereitgestellte easyIT-DataForm-Logo wird zentral auf sämtlichen DataForm-Seiten dargestellt und als fester Branding-Bestandteil in DataForm-Projektpakete aufgenommen.

## Verhalten

- Alle Seiten unter `products/dataform/`, die den Enterprise-Layout-Renderer verwenden, erhalten automatisch den DataForm-Branding-Balken.
- Der separate DataForm-Runtime-Renderer zeigt dasselbe Logo.
- Das Logo liegt zentral unter `products/dataform/assets/branding/easyit-dataform-logo.png`.
- Das Styling liegt unter `products/dataform/assets/branding/dataform-branding.css`.
- `.dfpkg`-Exporte enthalten `assets/branding/easyit-dataform-logo.png` und referenzieren es im Manifest unter `branding.logo`.
- Die Paketprüfung erlaubt nur die fest definierte PNG-Datei und prüft Signatur und Größe.

## Import

Ein importiertes DataForm-Paket wird auf einem HF64-System automatisch mit dem zentralen easyIT-DataForm-Branding angezeigt. Das Paket selbst enthält das Logo zusätzlich zur Portabilität.
