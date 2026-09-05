# RC1.8-FC1-HF25 – Table Management Workspace

Der bisherige Tabellen-Platzhalter wurde durch eine echte Tabellenverwaltung
ersetzt.

## Datenquellen
Die Tabellenansicht kann zwischen folgenden Quellen umschalten:
- interne Projekt-Datenbank
- aktivierte MySQL/MariaDB-Datenquellen
- SQLite
- CSV-Engine
- Oracle

## Tabellen-Explorer
Für jede Quelle werden:
- Tabellen aufgelistet,
- Tabellen-/View-Typ angezeigt,
- Spaltenstruktur untersucht,
- Datensatzanzahl bestimmt,
- maximal 20 Datensätze als Vorschau angezeigt.

Externe Datenquellen werden ausschließlich lesend untersucht.

## Interne Projekt-Datenbank
In der Systemquelle können neue physische Tabellen angelegt werden. Die
Pflichtspalte `id` wird automatisch als Auto-Increment-Primärschlüssel
erzeugt.

Spaltendefinitionen verwenden `name:typ`, z. B.:
- `artikelnummer:varchar(100)`
- `preis:decimal(12,2)`
- `aktiv:boolean`

Zulässige Typen sind varchar(n), text, int, bigint, decimal(p,s), date,
datetime, timestamp und boolean.

## Löschschutz
Nur Tabellen, die über diese Tabellenverwaltung erzeugt wurden, können über
die Oberfläche gelöscht werden. Sie werden in `dataform_managed_tables`
registriert. Interne DataForm-/Systemtabellen sind damit vor versehentlichem
Löschen geschützt.

Für Neuinstallationen wurde die Projektmigration
`004_table_workspace.php` ergänzt; bestehende Projekte werden idempotent
nachgerüstet.
