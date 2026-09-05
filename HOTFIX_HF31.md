# RC1.8-FC1-HF31 – Create DataForm from Table

Eine DataForm-verwaltete Projekttabelle kann jetzt direkt als Ausgangspunkt
für ein zugehöriges DataForm verwendet werden.

## Tabellenansicht
Bei einer verwalteten Projekttabelle erscheint entweder:

- `DataForm aus Tabelle erstellen`, wenn noch keine Zuordnung existiert, oder
- `Zugehöriges DataForm öffnen`, wenn die Tabelle bereits gebunden ist.

## Automatische Feldableitung
Beim Erzeugen werden die physischen Spalten ausgewertet. Die technische
Spalte `id` (PRIMARY KEY + AUTO_INCREMENT) bleibt Datenbankschlüssel und wird
nicht als normales Formularfeld übernommen.

Abbildung der wichtigsten SQL-Typen:

- VARCHAR/CHAR -> Text
- TEXT/MEDIUMTEXT/LONGTEXT -> Mehrzeiliger Text
- INT/BIGINT/DECIMAL/FLOAT/DOUBLE -> Zahl
- TINYINT(1) -> Kontrollkästchen
- DATE -> Datum
- DATETIME/TIMESTAMP -> Datum und Uhrzeit
- ENUM -> Auswahlliste

NULL/NOT NULL wird in die Pflichtfeldeigenschaft übertragen. SQL-Standardwerte
werden in die DataForm-Vorgaben übernommen; CURRENT_TIMESTAMP bleibt als
dynamische Vorgabe erhalten.

## Persistente Zuordnung
Die neue Projekttabelle `dataform_table_bindings` speichert die Beziehung
zwischen DataForm und physischer Tabelle. Zusätzlich enthält jedes erzeugte
Feld in `configuration_json` seine Tabellen-/Spaltenherkunft.

Dadurch kann dieselbe Tabelle nicht versehentlich mehrfach über diese Funktion
in DataForms überführt werden. Bei vorhandener Zuordnung führt die
Tabellenansicht direkt zum bestehenden DataForm.

Eine gebundene physische Tabelle kann nicht gelöscht werden, solange das
zugehörige DataForm existiert. Beim Löschen des DataForms wird die Zuordnung
über den Foreign Key automatisch entfernt.

## Migration
Neu: `installer/schema/project/005_dataform_table_bindings.php`.
Bestehende Projekte werden zusätzlich idempotent beim Start des DataForm-
Workspaces nachgerüstet.
