# HOTFIX HF50 – robuster Projektpaket-CRUD

HF50 behebt den Fehler "Das gespeicherte Paket wurde nicht gefunden" bei CRUD-Aktionen auf gespeicherten DataForm-Projektpaketen.

## Änderungen

- DELETE versucht zuerst die stabile Paket-ID und fällt bei einem veralteten/inkonsistenten ID-Verweis auf den gespeicherten Dateinamen zurück.
- Bereits physisch entfernte Paketdateien werden beim DELETE idempotent behandelt: die Paketliste wird aktualisiert, statt einen Fehlerzustand zu erzeugen.
- UPDATE (Umbenennen) verwendet dieselbe ID→Dateiname-Fallback-Strategie.
- READ/Download führt bei Bedarf ebenfalls einen Dateinamen-Fallback aus.
- CRUD-Formulare posten explizit auf `packages.php?project=...`; ein zuvor über Zeilenklick gesetztes `load_package` wird nicht versehentlich in die CRUD-Anfrage übernommen.
- Nach UPDATE/DELETE erfolgt Post/Redirect/Get. Dadurch entstehen keine Doppelaktionen beim Browser-Reload.
- Erfolgs-/Statusmeldung wird über eine Session-Flashmeldung erhalten.
