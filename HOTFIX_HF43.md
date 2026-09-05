# RC1.8-FC1-HF43 – n:1/Lookup direkt aus Basistabellen

HF43 erweitert die in HF39 eingeführte n:1-/Lookup-Beziehung um eine zweite Lookup-Quelle. Referenzwerte können jetzt nicht nur aus einem vorhandenen DataForm, sondern direkt aus einer physischen Projekttabelle gelesen werden.

## Beziehungsdesigner

Bei `n:1 / Lookup` steht jetzt die Auswahl **Lookup-Quelle** zur Verfügung:

- `DataForm` – bisheriges Verhalten.
- `Basistabelle` – direkte Auswahl einer physischen Projekttabelle ohne zusätzliches Referenz-DataForm.

Bei `Basistabelle` werden anschließend separat gewählt:

- Basistabelle,
- Schlüsselfeld der Basistabelle,
- Anzeigefeld der Basistabelle.

Beispiel:

`ed_ev_info.typ → ed_ev_type.id`

Dabei wird `id` gespeichert und `ev_type` im Auswahlfeld angezeigt.

## Runtime

- Lookup-Optionen werden direkt aus der gewählten Basistabelle gelesen.
- Die gespeicherte Auswahl wird serverseitig gegen die Basistabelle validiert.
- Listen- und Detailansichten lösen den gespeicherten Schlüssel über das konfigurierte Anzeigefeld auf.
- Schlüsselspalten dürfen auch nichtnumerische Werte enthalten; die Runtime behandelt Basistabellen-Schlüssel als skalare Werte.
- Bestehende DataForm-basierte n:1-Lookups bleiben vollständig kompatibel.

## Speicherung

Es ist keine Datenbankmigration notwendig. HF43 nutzt `configuration_json` der bestehenden `dataform_relations`:

- `lookup_source_kind = base_table`
- `base_table.table`
- `base_table.key_column`
- `base_table.display_column`

Das bestehende Pflichtfeld `target_dataform_id` wird bei Basistabellen-Lookups nur als Kompatibilitätsplatzhalter verwendet; die tatsächliche Referenzquelle steht ausschließlich in `configuration_json`.

## Sicherheit

Tabellen- und Spaltennamen werden gegen die reale Projekt-Datenbank validiert und nur nach Prüfung als SQL-Bezeichner verwendet. Interne DataForm-/Workflow-Systemtabellen werden im Basistabellen-Auswahldialog nicht angeboten.
