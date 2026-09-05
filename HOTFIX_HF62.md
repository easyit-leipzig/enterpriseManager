# HOTFIX HF62 – konfigurierbares Speicherverhalten

## Ziel

Die tabellarische Inline-Bearbeitung kann je DataForm entweder explizit über den Zeilenbutton **Speichern** oder **Ad hoc** nach einer Feldänderung gespeichert werden. Zusätzlich kann die reine Speichern-Erfolgsmeldung je DataForm deaktiviert werden.

## Bedienung

`DataForm → Architektur / Designer Foundation → Verhalten → Speicherverhalten der Tabellenansicht`

- **Erst beim Klick auf „Speichern“**: bestehendes HF61-Verhalten.
- **Ad hoc – nach Änderung eines Feldes automatisch**: `change` eines editierbaren Tabellenfeldes validiert und speichert die gesamte Zeile automatisch.
- **Speichern-Erfolgsmeldung anzeigen**: steuert nur positive Anlegen-/Speichern-Meldungen. Validierungs- und Fehlermeldungen bleiben immer aktiv.

Die `*`-Zeile zur Neuanlage bleibt absichtlich explizit über **Datensatz anlegen**, damit unvollständige neue Datensätze nicht während der Eingabe vorzeitig gespeichert werden.

## Persistenz / Pakete

Die Einstellungen liegen in `dataforms.table_save_mode` und `dataforms.show_save_success`. Da `dataforms` Bestandteil des DataForm-Paketexports ist, werden beide Werte mit `.dfpkg` exportiert und beim Import wiederhergestellt.
