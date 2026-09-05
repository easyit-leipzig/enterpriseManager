# HOTFIX HF72 – Projektsicherung als Download vor dem Löschen

- Der Projekt-Löschdialog bietet jetzt die Option **„Projekt vor dem Löschen als Download sichern“**; sie ist beim ersten Öffnen standardmäßig aktiviert.
- Die Sicherung wird vollständig **vor** jeder destruktiven Projekt- oder Datenbanklöschung erzeugt. Schlägt die Sicherung fehl, wird bei aktivierter Option nichts gelöscht.
- Das ZIP-Archiv enthält `project.json`, `manifest.json`, `README_RESTORE.txt` und einen SQL-Dump `database.sql` mit physischem Schema und Datensätzen der Projektdatenbank.
- Der SQL-Dump wird in einem konsistenten Datenbank-Snapshot erzeugt und enthält Tabellen, Daten, Views und – soweit mit dem Projekt-DB-Benutzer lesbar – Trigger.
- Das Sicherungsarchiv erhält eine SHA-256-Prüfsumme.
- Der Download erfolgt ausschließlich über einen zufälligen, an die angemeldete Session gebundenen Download-Token. Der Link ist 24 Stunden gültig; das Backup-Verzeichnis ist gegen direkten Webzugriff geschützt.
- Nach erfolgreicher Projektlöschung erscheint der Downloadlink in der Projektliste. Schlägt ein späterer Löschschritt nach bereits erfolgreicher Sicherung fehl, bleibt der Downloadlink direkt im Löschdialog verfügbar.
- Audit und Events unterscheiden `backup_requested`, `backup_created`, Dateiname und SHA-256 von der weiterhin unabhängigen Option `database_delete_requested`.
- Das Löschen der Projektdatenbank bleibt weiterhin standardmäßig deaktiviert und unabhängig von der Sicherungsoption.
