# RC1.8-FC1-HF6 – Full Backup & Restore

HF6 erweitert die Recovery-/Reset-Konsole um ein vollständiges Systembackup in einer einzigen Datei im Projektordner `backup/`.

## Backup

Ein Vollbackup enthält:

- vollständigen Projekt-Snapshot, ausgenommen den Backup-Ordner selbst,
- `DataForm5-Core/.env`,
- Install-Lock und weitere Projektdateien,
- SQL-Sicherung aller aus der easyIT-Konfiguration und Projektverwaltung ermittelten Datenbanken,
- `BACKUP_MANIFEST.json` mit Release-, Versions- und Datenbankinformationen.

Die Datenbank-Sicherung umfasst Tabellen, Daten, Views und – soweit vom Server verfügbar – Trigger. SQL-Anweisungen werden im Dump einzeln Base64-kapselt, damit Zeilenumbrüche und Semikolons in Daten keinen Restore beschädigen.

## Admin-Passwort

Das Admin-Passwort wird nicht im Klartext gespeichert. Die Admin-Datenbank enthält `users.password_hash`; dieser Hash wird vollständig gesichert und beim Restore wiederhergestellt. Dadurch gilt nach dem Restore wieder das Admin-Passwort, das zum Zeitpunkt des Backups gültig war.

## Restore

Der Restore ist ausschließlich über localhost möglich und verlangt die explizite Bestätigung `RESTORE EASYIT`.

Er ersetzt:

1. die im Backup enthaltenen Datenbanken,
2. alle Projektdateien durch den Snapshot aus dem Backup.

Der Ordner `backup/` selbst bleibt erhalten, damit die Restore-Datei während des Vorgangs nicht gelöscht wird.

## Sicherheit

Vollbackups enthalten `.env` und Datenbankinhalte und sind daher vertraulich. Der Ordner `backup/` wird per `.htaccess` gegen direkten HTTP-Zugriff gesperrt.
