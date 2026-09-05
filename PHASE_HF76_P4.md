# RC1.8-FC1-HF76 – Phase 4

## Ziel
Transport- und Integrationsschicht für alle neuen DataForm-Feldtypen: CSV, REST/OpenAPI, `.dfpkg` und Backend-Mapping.

## Umgesetzt
- Zentrale `DataFormTransport`-Schicht für CSV, REST und Pakettransport.
- CSV-Import validiert über dieselbe Feldtyp-Registry wie das CRUD.
- CSV-Import/Import-Rollback arbeitet über `DataFormRecordStore` und damit auch mit physisch gebundenen DataForms.
- CSV-Export erhält strukturierte JSON-/Mehrfachwerte maschinenlesbar; Datei-/Bildwerte werden portabel mit Base64 ausgegeben.
- Media-Transport speichert über serverseitig erkannte MIME-Typen und die konfigurierte DB-/Filesystem-Strategie.
- REST liefert native JSON-Werte für Zahlen, Boolean, Arrays und Objekte und blendet Passwort-Hashes aus.
- REST-Create/Update akzeptiert Media-Objekte mit `name` + `data_base64`; `remove=true` entfernt ein Medium.
- REST-Delete bereinigt verwaltete Filesystem-Medien.
- REST unterstützt `api-runtime.php/<project>/<api>/<endpoint>/<id>`; Query-Aufrufe bleiben kompatibel.
- OpenAPI 3.0 beschreibt Request-/Response-Schemas endpunktbezogen einschließlich Media-Input und Media-Metadaten.
- `.dfpkg` Format 1.2 enthält Medien dedupliziert als `media/<sha256>.bin` plus `media/index.json`.
- Paketprüfung validiert Medienpfade, Größe und SHA-256.
- Paketimport legt Medien über die Speicherstrategie des Zielfelds neu ab; Quellpfade werden nicht weiterverwendet.
- Backend-Mapping für alle 33 Typen auf MySQL/MariaDB, SQLite, Oracle und CSV geprüft.

## Kompatibilität
- Bestehende `.dfpkg`-Pakete ohne `media/index.json` bleiben importierbar.
- Bestehende Query-basierte REST-Aufrufe bleiben gültig.
- Die bisherigen 10 Feldtypen und die Phase-2/Phase-3-Funktionen bleiben erhalten.
