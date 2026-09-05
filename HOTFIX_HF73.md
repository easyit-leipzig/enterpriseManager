# HOTFIX HF73 – Projekt-Restore aus ZIP-Sicherung

- Unter **Enterprise → Projekte** steht jetzt **„Projektsicherung wiederherstellen“** zur Verfügung.
- ZIP-Sicherungen aus HF72 werden zuerst validiert und als Restore-Vorschau angezeigt; verarbeitet werden ausschließlich `manifest.json`, `project.json` und `database.sql` aus dem geschützten temporären Restore-Bereich.
- Projektname, technischer Slug, Ziel-Datenbank und Status können vor dem Restore kontrolliert bzw. angepasst werden.
- Für die Datenbank stehen vier Strategien zur Verfügung:
  - **Automatisch**: vorhandene Originaldatenbank weiterverwenden, andernfalls aus dem ZIP wiederherstellen.
  - **Vorhandene Datenbank verwenden**: nur Projektregistrierung wiederherstellen.
  - **Aus Sicherung wiederherstellen**: Ziel-Datenbank muss noch fehlen.
  - **Vorhandene Datenbank ersetzen**: explizites `DROP DATABASE` mit exakter Namensbestätigung und anschließendem Restore.
- Systemdatenbanken und die Enterprise-Administrationsdatenbank sind als Restore-Ziel gesperrt; bereits von anderen Projekten verwendete Slugs/Datenbanken werden abgewiesen.
- Der SQL-Restore unterstützt die von der easyIT-Sicherung erzeugten MySQL-`DELIMITER`-Blöcke für Trigger sowie alternative Ziel-Datenbanknamen.
- Restore-Vorschauen sind zufällig tokenisiert, sessiongebunden, zeitlich begrenzt und werden nicht in das Webroot extrahiert.
- Erfolgreiche Restores werden als `project.restore` auditiert und als `project.restored` Event veröffentlicht.
- Neu erzeugte Projekt-Backups verweisen in `README_RESTORE.txt` auf den integrierten Restore-Assistenten.
