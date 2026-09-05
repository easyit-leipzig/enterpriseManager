# RC1.8-FC1-HF76 – Phase 5 / Finalisierung

## Ziel
HF76 als vollständigen, eigenständig installierbaren Nachfolger des HF19-Gesamtstands freigeben.

## Finalisierung
- Versionskennung auf `RC1.8-FC1-HF76` vereinheitlicht.
- Sichtbare P4/HF75-Runtime-Marker durch HF76 ersetzt; historische Regression-Marker bleiben ausschließlich unsichtbar erhalten.
- Alte Framework-Caches mit absoluten HF19-Pfaden entfernt.
- Reale `.env` und alte Factory-Reset-`.env`-Snapshots aus dem Release entfernt.
- Bestehende physische INT-Spalten bleiben beim automatischen Schemaabgleich aus Kompatibilitätsgründen DataForm-Typ `number`; der neue Typ `integer` bleibt für bewusst neu definierte Felder verfügbar.
- Detaildarstellung für `derived_multienum` wieder an die relationale Labelauflösung angebunden.
- n:1-Felder verwenden wieder die eindeutige Aufforderung `Lookup-Datensatz wählen`.
- HF76-Migrationshinweise für die lokale `.env` und persistente Upload-/Backup-Verzeichnisse ergänzt.

## Freigabekriterium
HF19 darf erst nach erfolgreicher lokaler Übernahme der `.env` und gegebenenfalls neuer persistenter Dateien gelöscht werden. Der Programmcode selbst wird vollständig durch HF76 ersetzt.
