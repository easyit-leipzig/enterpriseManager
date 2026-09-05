# HOTFIX HF70 – vollständiges CRUD in der Enterprise-Projektliste

- Projektliste um grafische CRUD-Aktionen **Öffnen**, **Bearbeiten** und **Löschen** erweitert.
- **Create** bleibt über „Neues Projekt anlegen“ und „Vorhandenes Projekt registrieren“ verfügbar.
- Neue Bearbeitungsseite für Projektname, technischen Slug, Status und Beschreibung.
- Produkt- und Datenbankbindung sind beim Bearbeiten bewusst schreibgeschützt.
- Löschvorgang verlangt die exakte Eingabe des Projektnamens als Bestätigung.
- DELETE entfernt ausschließlich die Enterprise-Projektregistrierung; die physische Projektdatenbank bleibt als Sicherheitsmaßnahme erhalten.
- UPDATE und DELETE werden im Audit protokolliert und lösen Domain-Events aus.
- Projekt-Detailseite enthält ebenfalls Bearbeiten- und Löschen-Aktionen.
