# RC1.8-FC1-HF32 – Column Reordering

In der Tabellenansicht können Spalten DataForm-verwalteter Projekttabellen mit `↑` und `↓` jeweils um eine Position verschoben werden. Die reale MySQL/MariaDB-Spaltenreihenfolge wird geändert. `id` bleibt geschützt an erster Stelle.

Vor der Verschiebung wird die vollständige Spaltendefinition über `SHOW CREATE TABLE` gelesen und beim `ALTER TABLE ... MODIFY COLUMN ... AFTER ...` unverändert wiederverwendet. Damit bleiben Datentyp, NULL, Vorgabewert, Extra, Kommentar, Collation und weitere Attribute erhalten.

Ist die Tabelle an ein DataForm gebunden, wird anschließend die `position` der zugehörigen `dataform_fields` an die neue physische Reihenfolge angepasst.
