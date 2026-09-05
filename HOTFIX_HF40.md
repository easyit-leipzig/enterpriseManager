# RC1.8-FC1-HF40 – Feld-CRUD für Projekt-Anwendungstabellen

HF40 korrigiert die Tabellenklassifizierung im DataForm Workspace. Physische Anwendungstabellen der Projekt-Datenbank, die nicht ursprünglich über `dataform_managed_tables` angelegt wurden, wurden bisher fälschlich gemeinsam mit internen Systemtabellen schreibgeschützt.

## Neu

- Projekt-Anwendungstabellen wie `ed_ev`, `ed_ev_info`, `ed_ev_type` und `ed_ev_person` erhalten vollständiges Feld-CRUD.
- In der Spaltenliste erscheinen **Aktionen**, **Bearbeiten** und **Löschen** sowie **+ Feld hinzufügen**.
- Spalten können weiterhin mit ↑/↓ real verschoben werden.
- Das technische Pflichtfeld `id` bleibt geschützt.
- Primär-/Fremdschlüsselfelder bleiben gegen destruktive Änderungen geschützt.
- Aus einer vorhandenen Anwendungstabelle kann direkt ein tabellengebundenes DataForm erzeugt werden.
- Die Anwendungstabelle selbst bleibt gegen versehentliches Löschen geschützt, solange sie nicht explizit als DataForm-verwaltete Tabelle registriert ist.
- Interne Tabellen (`dataform_*`, `workflow_*`, `data_sources`, `migrations`) bleiben vollständig read-only.

## Zielzustand für Testprojekt 63

Bei `ed_ev_person` erscheinen für `saturation`, `firstname` und `lastname` die CRUD-Aktionen. `id` bleibt geschützt. Damit kann insbesondere `firstname`/`lastname` anschließend von `INT` auf einen fachlich passenden `VARCHAR`-Typ geändert werden.
