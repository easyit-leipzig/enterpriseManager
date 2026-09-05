# RC1.8-FC1-HF9 – Benutzer, Rollen und Rechte

## Neu
- Dashboard-/Navigationspunkt **Benutzer & Rechte**
- Benutzer-CRUD: anlegen, bearbeiten, aktivieren/deaktivieren, Rollen zuweisen, Kennwort neu setzen, löschen
- Rollen-CRUD: anlegen, bearbeiten, löschen
- Capability-Zuweisung je Rolle
- Systemrolle `admin` geschützt
- letzter aktive Administrator geschützt
- CSRF-Schutz und Audit-Einträge für Änderungen
- Kennwörter ausschließlich via `password_hash()`

## Testziel
Für Test 5.6 kann nun ein eingeschränkter Benutzer (z. B. `test_viewer`) angelegt und einer Rolle ohne `permissions.manage` zugewiesen werden.
