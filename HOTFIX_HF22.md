# RC1.8-FC1-HF22 – Parent Field Inheritance

Das Standardwertmodell besitzt nun eine vierte Quelle:

- Wie definiert [Wert]
- CURRENT_TIMESTAMP
- JavaScript-Funktion
- Wert aus Eltern-DataForm

## Modell
Ein DataForm gilt in diesem Zusammenhang als Kind, wenn es die Quellseite
einer aktiven 1:n-Beziehung ist und diese Beziehung ein Lookup-Feld im
Kind-DataForm besitzt.

Für `default_mode=parent_field` speichert das Feld:
- `parent_relation_id`
- `parent_field_id`

## Designer
Unter `Erweitert → Standardwert → Wert aus Eltern-DataForm` werden nur
gültige Elternbeziehungen des aktuellen Kind-DataForms angeboten.
Danach wird das zu übernehmende Feld des Eltern-DataForms gewählt.

## Datensatzerfassung
Das Lookup-Feld der Elternbeziehung wird als Auswahl realer Eltern-Datensätze
gerendert. Wird ein Eltern-Datensatz gewählt, lädt `parent-value.php` genau
den konfigurierten Elternfeldwert und setzt ihn als Vorgabewert im Kindfeld.

Die Übernahme erfolgt nur beim Anlegen eines neuen Kind-Datensatzes.
Normale editierbare Felder können danach weiterhin überschrieben werden.

`parent-value.php` ist authentifiziert und validiert Projekt, Kind-DataForm,
aktive 1:n-Beziehung, Eltern-Datensatz und Elternfeld.
