# HOTFIX HF59

## Inline-Neuanlage: Validierungsfehler stabilisieren

- Validierungsfehler in der `*`-Zeile brechen die Datensatzliste nicht mehr ab.
- `sort` und `dir` sind auch im Fehlerfall definiert; keine `Undefined variable`-Warnings mehr.
- Die Datensatzliste wird nach einem fachlichen Eingabefehler normal neu geladen.
- Bereits eingegebene Werte der Inline-Neuzeile bleiben erhalten.
- Der Fehler wird als normale DataForm-Fehlermeldung innerhalb des Workspace angezeigt.
