# Assistant Phase 7 – Feld-, Lookup- und Enum-Assistent

## Ziel

Phase 7 ergänzt den Assistant-Core um `dataform.fields`. Der Assistent erweitert die bisherige Grundfelddefinition um typisierte Felder, Lookup-Quellen und abgeleitete Mehrfach-Enums.

## Felddefinition

Format im Wizard:

`name|label|type|required|readonly|default`

Unterstützte Feldtypen:

- `text`
- `textarea`
- `integer`
- `decimal`
- `boolean`
- `date`
- `datetime`
- `email`
- `url`
- `hidden`
- `lookup`
- `enum`
- `derived_enum`

Für jedes Feld werden mindestens folgende Eigenschaften kompiliert:

- `name`
- `label`
- `type`
- `required`
- `readOnly`
- `default`

## Lookup

Lookup-Felder speichern einen einzelnen Wert und beziehen die Anzeige aus einer anderen Quelle.

Wizard-Format:

`feld|profil|quelle|wertfeld|anzeigefeld|filterfeld|filterwertquelle`

Beispiel:

`to_type_id|project-main|ed_ev_type|id|name||`

Das Feld `to_type_id` speichert damit den Wert aus `ed_ev_type.id` und zeigt `ed_ev_type.name` an.

## Derived Enum

Ein `derived_enum` ist für Mehrfachabhängigkeiten vorgesehen. Die auswählbaren Werte werden aus einer anderen Tabelle/Quelle abgeleitet, mehrere Schlüssel werden in einem Feld gespeichert.

Wizard-Format:

`feld|profil|quelle|wertfeld|anzeigefeld|filterfeld|filterwertquelle`

Beispiel:

`tags|project-main|ed_ev_person|id|name|to_ev_id|record.id`

Hier werden nur Einträge aus `ed_ev_person` verwendet, deren `to_ev_id` dem aktuellen `record.id` entspricht.

### Verbindliche Speicherregel

Derived-Enum-Werte werden immer als kommaseparierte Schlüssel gespeichert.

Beispiel:

`3,7,9`

Verbindlich:

- `multiple = true`
- `delimiter = ,`
- Werte werden getrimmt
- doppelte Schlüssel werden entfernt
- ein einzelner Schlüssel darf selbst kein Komma enthalten

Die PHP-Laufzeit stellt dafür `DerivedEnumValueCodec` bereit. Die Browser-Laufzeit stellt dieselbe Semantik in `assets/js/dataform-fields.js` bereit.

## DataForm-Handoff

Der DataForm-Assistent kann den Feld-Assistenten nach seinem Review öffnen. Der Feld-Assistent kann den bestehenden DataForm-Kontext und die Grundfelder importieren.

Nach erfolgreichem Feld-Review können die angereicherten Felder wieder in `dataform.create` übernommen werden. Lookup- und Derived-Enum-Metadaten bleiben damit Bestandteil der endgültigen DataForm-Konfiguration.

## Export

Schema:

`easyit.dataform.fields.assistant.v1`

Dateiendung:

`.fields.json`

## Tests

- PHP-Syntaxcheck aller Overlay-PHP-Dateien
- `tests/assistant/phase7_smoke.php`
- Regression `tests/assistant/phase6_smoke.php`
- JavaScript-Codec-Test
- HTTP-Wizard inklusive Session und JSON-Export
