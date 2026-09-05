# Wechsel von RC1.8-FC1-HF19 auf RC1.8-FC1-HF76

HF76 ist ein vollständiges Gesamtpaket und kein Overlay. Das neue Verzeichnis kann parallel zu HF19 entpackt und nach erfolgreichem Test als alleiniger Programmstand verwendet werden.

## Vor dem Löschen von HF19

1. Apache/MariaDB in XAMPP kurz anhalten, damit während der Übernahme keine Dateien geändert werden.
2. Den neuen Ordner `easyIT-Enterprise-RC1.8-FC1-HF76` vollständig nach `D:\xampp\htdocs\` entpacken.
3. Wenn die bestehende Installation eine konfigurierte Datei `DataForm5-Core\.env` besitzt, diese **lokal** aus HF19 nach `DataForm5-Core\.env` des HF76-Ordners kopieren. Die Release-ZIP enthält absichtlich keine reale `.env` und damit keine Datenbankpasswörter oder Anwendungsschlüssel.
4. Falls im live verwendeten HF19 nach Erzeugung des hier verwendeten Ausgangs-ZIP neue Dateien entstanden sind, diese persistenten Verzeichnisse übernehmen:
   - `storage\dataform\uploads\`
   - `storage\project-delete-backups\`
   - `backup\` soweit dort eigene Sicherungen liegen
5. Nicht übernehmen: `DataForm5-Core\storage\framework\cache\`, Sessions, Logs, temporäre Dateien oder alte Factory-Reset-Snapshots. Diese Daten werden regeneriert.
6. Apache/MariaDB starten und HF76 über den neuen Ordner aufrufen. Anmeldung, Projektliste, DataForm-Designer, vorhandene Datensätze und mindestens ein Speichern/Löschen prüfen.
7. Erst wenn diese Prüfung erfolgreich ist, kann der alte Ordner `easyIT-Enterprise-RC1.8-FC1-HF19` gelöscht werden.

Die administrativen und Projektdatenbanken werden durch den Ordnerwechsel nicht gelöscht. Die Anwendung verwendet nach Übernahme der lokalen `.env` weiterhin die dort konfigurierten Datenbanken.
