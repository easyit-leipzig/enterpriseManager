# DataForm HF47 – CRUD für gespeicherte Projektpakete

HF47 trennt erstmals die physisch gespeicherten `.dfpkg`-Dateien von der unveränderlichen Paket-Historie und ergänzt für die Paketablage vollständiges CRUD.

## Änderungen

- Neuer Bereich **Gespeicherte Pakete** auf der Seite `DataForm → Projektpakete`.
- **Create:** Paketexport erzeugt und speichert ein neues `.dfpkg`; nach dem Download wird die Paketliste automatisch aktualisiert.
- **Read:** gespeicherte Pakete können direkt erneut heruntergeladen werden.
- **Update:** Paketname kann bearbeitet werden; dabei werden sichtbarer Paketname, Dateiname, `manifest.json`, `project.json` und `README.txt` konsistent aktualisiert. Die Paket-ID bleibt erhalten.
- **Delete:** gespeicherte Paketdatei kann gelöscht werden.
- Alle Paket-CRUD-Aktionen verwenden die globalen grafischen 3D-CRUD-Buttons.
- Die **Paket-Historie** bleibt bewusst unveränderlich und protokolliert zusätzlich `update` und `delete` als Auditnachweis.
- Gespeicherte Pakete werden aus der realen Paketablage `workspace/project-packages/exports` gelesen; nur Pakete des aktuellen Projekts werden angezeigt.
- Download, Bearbeiten und Löschen prüfen Dateiname, Paketformat und Projektzugehörigkeit.
- Umbenennen ist für Windows/XAMPP sicher implementiert und aktualisiert den SHA-256-Wert des Pakets.

## Ziel

Die Seite unterscheidet nun klar zwischen:

1. **Gespeicherte Pakete** – aktuelle physische Paketablage mit CRUD.
2. **Paket-Historie** – unveränderliches Auditprotokoll.
