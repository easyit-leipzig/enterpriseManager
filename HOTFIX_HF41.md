# RC1.8-FC1-HF41 – Synchronisation physischer SQL-Datentypen

HF41 behebt die Inkonsistenz zwischen einer real geänderten Tabellenspalte und dem bereits existierenden tabellengebundenen DataForm-Feld.

## Fehlerbild

Wurde beispielsweise `ed_ev_person.firstname` über **Tabellen → Bearbeiten** von `INT(11)` auf `VARCHAR(100)` geändert, blieb im DataForm weiterhin `number` sichtbar. Ursache war, dass HF38/HF40 bei existierenden Feldern nur die Tabellenbindung ergänzte, aber eine Änderung von `table_binding.sql_type` und `dataform_fields.field_type` nicht nachführte.

## Korrektur

- Der Synchronisierer vergleicht jetzt den bisher gespeicherten SQL-Typ mit dem realen Typ der physischen Spalte.
- Ändert sich der physische Typ z. B. von `INT` auf `VARCHAR`, wird ein automatisch abgeleitetes Feld von `number` auf `text` umgestellt.
- `table_binding.sql_type` wird mit der realen Tabellenstruktur aktualisiert.
- Bewusst konfigurierte Spezialtypen werden nicht blind überschrieben; eine automatische Typkorrektur erfolgt nur, wenn das DataForm-Feld noch dem automatisch aus dem alten SQL-Typ abgeleiteten Standardtyp entspricht.
- Nach **Tabellen → Feld bearbeiten** wird das gebundene DataForm sofort synchronisiert.
- Beim Öffnen eines tabellengebundenen DataForms wird die reale Tabellenstruktur erneut synchronisiert. Dadurch werden auch externe Schemaänderungen sichtbar.
- Der Runtime-Marker wurde auf **HF41** angehoben.

## Erwartetes Ergebnis für `ed_ev_person`

Nach der physischen Änderung

- `firstname VARCHAR(100) NOT NULL`
- `lastname VARCHAR(100) NOT NULL`

zeigt **DataForms → Ed Ev Person → Felder**:

- `firstname` → `text`
- `lastname` → `text`
