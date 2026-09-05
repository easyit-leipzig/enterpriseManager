# HOTFIX HF51 – Detaillierte Relationsvorschau in Projektpaketen

## Problem

Bei **Projektpaket importieren → Paket prüfen** wurde `dataform_relations` lediglich als Konfigurationstabelle mit einer Anzahl wie `4` angezeigt. Damit war vor dem Import nicht sichtbar, welche 1:n- und n:1-/Lookup-Beziehungen das Paket tatsächlich enthält.

## Lösung

HF51 liest die im `.dfpkg` enthaltenen `dataforms`, `dataform_fields` und `dataform_relations` bereits während der Paketprüfung gemeinsam aus und erzeugt daraus eine semantische Vorschau.

Die Importvorschau zeigt nun für jede Beziehung separat:

- Beziehungsname,
- Kardinalität (`1:n`, `n:1`, `n:m`),
- Ausgangs-/Eltern-DataForm,
- Ziel-DataForm oder Basistabelle,
- konkrete Feldzuordnung,
- Anzeigefeld eines Lookups,
- Aktiv-/Pflichtstatus.

Für das Event-Paket erscheinen damit beispielsweise:

- `Ed Ev.id → Ed Ev Info.to_ev_id`,
- `Ed Ev Info.typ → ed_ev_type.id` · Anzeige `ev_type`,
- `Ed Ev Info.from_person → ed_ev_person.id` · Anzeige `email`,
- `Ed Ev Info.to_person → ed_ev_person.id` · Anzeige `email`.

Die bisherige Konfigurationszählung bleibt als technische Übersicht erhalten und verweist bei `dataform_relations` auf die Detailtabelle.

## Kompatibilität

Das Paketformat bleibt `1.1`; bestehende HF44–HF50-Pakete müssen nicht neu erzeugt werden. Sie können nach Installation von HF51 erneut über **Paket prüfen** geladen werden und erhalten dann die detaillierte Relationsvorschau.
