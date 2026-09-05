# RC1.8-FC1-HF38 – Synchronisation tabellengebundener DataForm-Felder

HF38 behebt eine Inkonsistenz zwischen realen Projekttabellen und `dataform_fields`, die im Beziehungsdesigner dazu führte, dass vorhandene physische Spalten nicht als Fremdschlüsselfeld auswählbar waren.

## Fehlerbild

Bei einem persistent an `ed_ev_info` gebundenen DataForm konnte die physische Spalte `to_ev_id` in MariaDB vorhanden sein, während in `dataform_fields` kein zugehöriger Feldeintrag existierte. Der HF37-Beziehungsdesigner validierte korrekt gegen reale Spalten, konnte aber nur bereits registrierte DataForm-Felder anzeigen. Dadurch fehlten unter anderem `to_ev_id` und `from_person` in der Kindfeld-Auswahl.

## Korrektur

- Vor der Relationsprüfung synchronisiert der Beziehungsdesigner alle Einträge aus `dataform_table_bindings` mit der realen Tabellenstruktur.
- Reale Spalten, die noch nicht in `dataform_fields` registriert sind, werden automatisch als DataForm-Felder angelegt.
- Die technische `id`-Spalte bleibt geschützt und wird nicht als normales DataForm-Feld dupliziert.
- Bereits vorhandene Felder werden nicht doppelt angelegt.
- Fehlen bei einem vorhandenen, real passenden Feld lediglich `table_binding`-Metadaten, werden nur diese ergänzt; Label, Feldtyp und sonstige Konfiguration bleiben erhalten.
- Nach einer Synchronisation wird die DataForm-Feldreihenfolge wieder an die reale Spaltenreihenfolge angeglichen.
- Phantomfelder, deren physische Spalte nicht existiert, bleiben weiterhin vom Relations-Dropdown ausgeschlossen.
- Die Synchronisation läuft vor `repairLegacyOneToMany()`, sodass eine Legacy-Beziehung nach Registrierung eines realen Feldes unmittelbar auf dieses Feld repariert werden kann.

## Konkreter erwarteter Effekt für `ed_ev_info`

Bei der im Testprojekt vorhandenen Tabelle

`id, to_ev_id, typ, date_time, bemerkung, from_person, to_person`

werden fehlende Metadaten für `to_ev_id` und `from_person` automatisch ergänzt. Ein vorhandenes `to_person` ohne physische Bindungsmetadaten wird mit `ed_ev_info.to_person` verknüpft. Anschließend kann der Beziehungsdesigner insbesondere die korrekte 1:n-Zuordnung

`ed_ev.id -> ed_ev_info.to_ev_id`

verwenden.

## Regressionstest

`tests_rc18_phase6_dataform_hf38_bound_field_sync.php`
