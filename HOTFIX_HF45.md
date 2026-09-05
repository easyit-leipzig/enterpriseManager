# DataForm HF45 – eigener Paketname

HF45 erweitert den selektiven Projektpaket-Export um einen optionalen eigenen Paketnamen.

## Verhalten

- Der automatisch generierte Standardname bleibt sichtbar und wird weiterhin verwendet, wenn kein eigener Name eingegeben wird.
- Ein optionaler eigener Paketname kann direkt vor dem Export eingetragen werden.
- Der Anzeigename wird unverändert im Manifest und in `project.json` gespeichert.
- Für den Dateinamen wird der eigene Name sicher normalisiert; Paketversion und Zeitstempel bleiben automatisch.
- Die Importvorschau zeigt den Paketnamen zusätzlich zum Ursprungsprojekt.

Beispiel: `ed-events-demo` → `ed-events-demo-1.1.0-YYYYMMDD_HHMMSS.dfpkg`.
