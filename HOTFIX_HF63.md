# HOTFIX HF63 – JavaScript-Toast für Speichern-Erfolg

## Ziel

Die je DataForm konfigurierbare Erfolgsmeldung nach dem Speichern wird nicht mehr als statische PHP-Notice im Seiteninhalt ausgegeben, sondern als nicht-modaler JavaScript-Toast.

## Verhalten

- **JavaScript-Erfolgsmeldung nach dem Speichern anzeigen = an**: Nach erfolgreichem Anlegen oder Speichern erscheint ein Toast mit `Datensatz gespeichert.` bzw. `Datensatz angelegt.`.
- **aus**: Es erscheint keine positive Speichern-Meldung.
- Validierungs- und Fehlermeldungen bleiben immer als normale, dauerhaft sichtbare Meldung erhalten.
- Der Toast schließt automatisch nach ca. 2,8 Sekunden und kann vorher über `×` geschlossen werden.
- Es wird bewusst kein blockierendes `alert()` verwendet.
- Manuelles und Ad-hoc-Speichern verwenden dieselbe Toast-Darstellung.

## Persistenz / Pakete

Die bestehende Einstellung `dataforms.show_save_success` wird weiterverwendet. Das Paketformat ändert sich nicht; die Einstellung wird weiterhin über `.dfpkg` exportiert/importiert.
