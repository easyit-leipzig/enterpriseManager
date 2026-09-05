# DataForm HF46 – Paketname sichtbar und persistent

HF46 macht den aktuellen Projektnamen und den tatsächlich verwendeten Paketnamen im Projektpaket-Export ausdrücklich sichtbar. Ein eigener Paketname wird live in einer Vorschau angezeigt, pro Projekt im Browser zwischengespeichert und in der Paket-Historie separat protokolliert.

## Änderungen

- Aktueller Projektname wird im Exportbereich deutlich angezeigt.
- Eigener Paketname erhält eine Live-Vorschau.
- Voraussichtlicher Dateiname wird direkt angezeigt.
- Eingabe bleibt pro Projekt über `localStorage` erhalten.
- Paket-Historie besitzt eine eigene Spalte `Paketname`.
- `project_package_history` wird kompatibel um `package_name` erweitert.
