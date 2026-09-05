# RC1.8-FC1-HF26 – Table Field CRUD

Die Feld-/Spaltenstruktur DataForm-verwalteter Projekttabellen ist jetzt CRUD-fähig.

## Create
In einer verwalteten Tabelle kann ein neues Feld mit Name, Datentyp und NULL-Eigenschaft angelegt werden.

## Read
Spaltenstruktur, Schlüssel, Standardwert, Extra-Merkmale, Datensatzanzahl und Datenvorschau bleiben sichtbar.

## Update
Nicht geschützte Felder können umbenannt sowie hinsichtlich Datentyp und NULL-Eigenschaft geändert werden. Ein vorhandener Standardwert wird beim Strukturwechsel bewahrt.

## Delete
Nicht geschützte Felder können mit expliziter Datenverlustwarnung gelöscht werden.

## Schutz
- `id` bleibt als DataForm-Pflichtfeld unveränderbar.
- Schlüssel-/Beziehungsfelder sind gegen Update/Delete geschützt.
- System-/Anwendungstabellen bleiben vollständig read-only.
- Externe Datenquellen bleiben read-only.
- Schreiboperationen sind ausschließlich für Tabellen erlaubt, die in `dataform_managed_tables` registriert sind.

## Zulässige Feldtypen
`varchar(n)`, `text`, `int`, `bigint`, `decimal(p,s)`, `date`, `datetime`, `timestamp`, `boolean`.
