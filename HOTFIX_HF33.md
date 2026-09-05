# RC1.8-FC1-HF33 – Parent/Child Relation Direction Repair

Die bisherige 1:n-Semantik war in UI und Runtime vertauscht bzw. missverständlich.
HF33 legt verbindlich fest:

- `source_dataform_id` = Eltern-DataForm
- `target_dataform_id` = Kind-DataForm
- Elternschlüssel = technische Datensatz-ID `id`
- `lookup_field_id` = Fremdschlüsselfeld im Kind, z. B. `to_ev_id`

Damit gilt:

`Eltern.id -> Kind.to_ev_id`

## Datensatzformular

Die Eltern-Auswahl wird ausschließlich im Kindformular angezeigt. Ein Basis-
oder Elternformular erhält kein Feld „Eltern-Datensatz wählen“.

Ein vorhandenes normales Feld wie `to_ev_id` kann direkt als Kind-
Fremdschlüsselfeld verwendet werden. Es muss nicht den Feldtyp `lookup`
besitzen; die aktive Relation rendert es im Kindformular als Elternauswahl.

## Beziehungsdesigner

Für 1:n werden ausdrücklich gewählt:

- Eltern-DataForm
- Eltern-Anzeigefeld (optional, technische ID bleibt Schlüssel)
- Kind-DataForm
- Fremdschlüsselfeld im Kind

Falls kein passendes Kindfeld existiert, kann DataForm ein Lookup-Feld im
Kind automatisch erzeugen.

## Legacy-Reparatur HF22–HF32

Bestehende 1:n-Beziehungen ohne die neue Semantik werden beim Laden geprüft.

1. Liegt das Lookup bereits im heutigen Kind, wird die Relation nur markiert.
2. Wurde durch die alte Oberfläche versehentlich ein synthetisches Lookup im
   Elternformular erzeugt und besitzt das Kind genau ein eindeutiges `*_id`
   bzw. `to_*_id`-Feld, wird die Relation auf dieses Kindfeld umgebunden.
3. Entspricht die Relation der früher dokumentierten Semantik
   `Quelle=Kind, Ziel=Eltern`, werden die Endpunkte automatisch getauscht.
4. Ein automatisch erzeugtes altes Lookup wird nur dann gelöscht, wenn es
   tatsächlich ein DataForm-`lookup` ist und von keiner anderen Relation
   verwendet wird.

Normale Fremdschlüsselfelder des Kindes werden beim Löschen einer Relation
nicht gelöscht.
