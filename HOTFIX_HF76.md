# RC1.8-FC1-HF76 – DataForm erweitertes Feldtypsystem

HF76 ist ein vollständiger Nachfolger des HF19-Gesamtstands und enthält die in P1–P4 schrittweise eingeführte Erweiterung des DataForm-Feldtypsystems.

## Kernumfang
- Zentrale Registry mit 33 Feldtypen.
- Designer, CRUD, Validierung und Anzeige verwenden dieselbe Feldtypdefinition.
- Strukturierte Typen: JSON, Link, Mehrfachwerte, Tags, Koordinaten, UUID und berechnete Felder.
- Medien: `image` und `file` mit Datenbank- oder verwalteter Filesystem-Speicherung.
- Sichere Medienausgabe über kontrollierten Endpunkt, Integritätsprüfung und Bereinigung bei Ersetzen/Löschen.
- CSV, REST/OpenAPI und `.dfpkg` auf gemeinsamer Transportlogik.
- Backend-Mapping für MySQL/MariaDB, SQLite, Oracle und CSV.

## Upgrade-Hinweis
HF76 wird als vollständiges Gesamtpaket ausgeliefert. Es ist kein Overlay und benötigt kein PowerShell-Installationsskript. Vor dem endgültigen Entfernen eines vorhandenen HF19-Verzeichnisses sollen dessen projektspezifische `.env`-/Datenbank-/Upload-Inhalte gesichert bzw. in die neue Installation übernommen werden, sofern diese nicht ohnehin außerhalb des Programmordners verwaltet werden.
