# RC1.8-FC1-HF44 – selektive, selbsttragende DataForm-Projektpakete

HF44 schließt die Lücke zwischen einer reinen Metadaten-Sicherung und einem auf einem leeren Projekt tatsächlich wiederherstellbaren DataForm-Paket.

## Exportauswahl

Die Seite **DataForm → Projektpakete** zeigt jetzt getrennt und verständlich:

- DataForms und Feld-/Layout-Konfiguration,
- Beziehungen und Lookups,
- Tabellenbindungen,
- Basistabellen / physische Tabellenschemata,
- Workflows und Regeln,
- Projektmodule,
- Datensätze als Beispieldaten.

DataForms und Basistabellen können einzeln ausgewählt werden. Tabellen, die durch eine persistente DataForm-Bindung oder einen Basistabellen-Lookup benötigt werden, werden automatisch als Abhängigkeit ergänzt.

## Paketformat 1.1

Ein `.dfpkg` kann zusätzlich enthalten:

- `schema/<tabelle>.sql` – geprüftes `CREATE TABLE IF NOT EXISTS`,
- `records/<tabelle>.json` – optionale reale Test-/Beispieldaten.

Beim Import werden fehlende physische Tabellen vor den Metadaten erzeugt. Bereits vorhandene Tabellen werden nicht automatisch verändert. Anschließend werden Konfiguration und optionale Beispieldaten importiert.

## Sicherheit

Schema-Dateien dürfen ausschließlich ein einzelnes, zum Dateinamen passendes `CREATE TABLE` enthalten. Pfad-Traversal, zusätzliche SQL-Anweisungen und geschützte interne Tabellen werden abgewiesen.

## Ziel für das Event-Testpaket

Für `ed_ev` / `ed_ev_info` kann damit ein Paket erstellt werden, das zusätzlich die Lookup-Basistabellen `ed_ev_type` und `ed_ev_person` samt optionalen Mock-Daten enthält, ohne dass für reine Lookup-Tabellen ein eigenes DataForm erforderlich ist.
