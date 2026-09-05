# HOTFIX HF71 – Projekt wahlweise mit Projektdatenbank löschen

- Der Löschdialog eines Enterprise-Projekts bietet jetzt eine ausdrückliche Checkbox **„Projektdatenbank ebenfalls endgültig löschen“**.
- Die Checkbox ist standardmäßig deaktiviert; damit bleibt das bisher sichere Verhalten erhalten.
- Wird sie aktiviert, erscheint unmittelbar eine deutliche Warnung mit dem konkreten Projektdatenbanknamen.
- Vor `DROP DATABASE` werden Datenbankname, Treiber, System-/Admin-Datenbanken und Mehrfachreferenzen durch andere Projekte geprüft.
- `DROP DATABASE` wird über die konfigurierte Projekt-DB-Verbindung ausgeführt und danach verifiziert.
- Bei einem DROP-Fehler bleibt die Enterprise-Projektregistrierung bestehen.
- Ist die gewählte Datenbank bereits nicht vorhanden, wird die verwaiste Registrierung entfernt und dieser Zustand separat protokolliert.
- Audit und `project.deleted`-Event enthalten die Felder `database_delete_requested`, `database_deleted` und `database_already_missing`.
