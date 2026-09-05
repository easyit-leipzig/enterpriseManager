# easyIT Enterprise RC1.8-FC1-HF76 – PUBLISH13

## Schwerpunkt
PUBLISH13 korrigiert die DataForm-Standardansicht. Die gespeicherte Eigenschaft `view_mode` ist nun eine tatsächlich ausgeführte Runtime-Eigenschaft und nicht mehr nur Metadatum.

## Korrekturen

- `table`: echte Tabellenansicht mit Inline-Bearbeitung, Suche, Filter, Paginierung und Datensatzzeigern.
- `form`: echte Einzel-Datensatzansicht. Ein historischer bzw. generierter `mode=list`-Aufruf fällt nicht mehr in die Tabelle zurück. Der aktuelle bzw. erste Datensatz wird als vollständiges Formular dargestellt.
- Formularansicht besitzt Datensatznavigation für erster, vorheriger, aktueller, nächster und letzter Datensatz sowie Neuanlage.
- `dialog`: echte modale Datensatzmaske über einer read-only Tabellenübersicht. Auswahl eines Datensatzes öffnet dessen Formular im Dialog; Neuanlage erfolgt ebenfalls im Dialog.
- Die konfigurierte Dialoggröße `small`, `medium`, `large` oder `fullscreen` wird tatsächlich angewendet.
- Dialog-Formulare sind in der normalen Runtime schreibfähig, in der Realvorschau weiterhin serverseitig schreibgeschützt.
- Die Realvorschau verwendet dieselbe echte View-Logik wie die Runtime.
- Eingebettete Kind-DataForms übernehmen in der Realvorschau jetzt ihre eigene konfigurierte Standardansicht; `mode=list` wird nicht mehr erzwungen.
- `Datensätze pro Seite` akzeptiert jetzt tatsächlich Werte von 1 bis 200. Die frühere interne Mindestgrenze 5 wurde entfernt.
- Das schlanke exportierte HTML5-Anwenderpaket besitzt dieselben drei echten Ansichten `table`, `form`, `dialog`.
- Die bisherige CSS-Simulation der Formularansicht im Anwenderpaket wurde entfernt und durch serverseitiges Formular-Rendering ersetzt.

## Kompatibilität

Die bestehende Datenbankstruktur bleibt kompatibel. Es ist keine neue Migration erforderlich. Vorhandene Werte in `dataforms.view_mode`, `default_per_page`, `dialog_size` und den übrigen Runtime-Eigenschaften werden direkt verwendet.
