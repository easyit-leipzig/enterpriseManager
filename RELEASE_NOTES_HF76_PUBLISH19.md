# HF76 PUBLISH19 – DataForm Volltextsuche und Filter

DataForms besitzen jetzt zwei voneinander unabhängige Runtime-Eigenschaften:

- **Volltextsuche: Ja/Nein**
- **Filter: Ja/Nein**

## Speicherung

Aus Gründen der Abwärtskompatibilität wird die bereits vorhandene Metadatenspalte
`show_search` als Schalter für die Volltextsuche weiterverwendet. Neu hinzu kommt
`show_filter` für Feldfilter und gespeicherte Filter. Beide Eigenschaften haben
bei neuen bzw. migrierten DataForms den Standardwert `1` (Ja).

## Runtime

`Volltextsuche = Nein` bedeutet:

- kein Volltextsuchfeld in der DataForm-Runtime,
- über URL übergebene `q`-Werte werden nicht ausgeführt.

`Filter = Nein` bedeutet:

- keine Feldfilter,
- keine gespeicherten Filter,
- gespeicherte Filter können nicht über manipulierte Requests angewendet,
  angelegt oder gelöscht werden,
- über URL übergebene `filter[...]`-Werte werden nicht ausgeführt.

Beide Schalter sind unabhängig. Damit sind alle vier Kombinationen zulässig.

## Exportierte Anwendungen

Die Projekt-/Anwender-Runtime übernimmt beide DataForm-Eigenschaften. Die
exportierte App zeigt bzw. verarbeitet Volltextsuche und Feldfilter entsprechend
getrennt.

## dataformContext

Der Kontext enthält nun zusätzlich:

```json
{
  "dataform": {
    "fulltext_search": true,
    "filter": true
  },
  "ui": {
    "fulltext_search_enabled": true,
    "filter_enabled": true
  }
}
```
