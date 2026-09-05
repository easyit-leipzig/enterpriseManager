# RC1.8-FC1-HF42 – Präzise Beschriftung im Beziehungsdesigner

HF42 überarbeitet die Beschriftungen und Hilfetexte des Beziehungsdesigners kontextabhängig nach Beziehungstyp.

## Änderungen

- `Typ` wird als `Beziehungstyp / Kardinalität` erläutert.
- Bei **1:n** werden Eltern-, Kind-, Anzeige- und Fremdschlüsselfeld fachlich eindeutig beschrieben.
- Bei **n:1 / Lookup** wird klar getrennt zwischen:
  - Ausgangs-DataForm,
  - Zuordnungsfeld, das die Referenz-ID speichert,
  - Lookup-/Referenz-DataForm,
  - festem technischen Referenzschlüssel `id`,
  - sichtbarem Anzeigefeld im Auswahlfeld.
- Die Pflichtoption erhält je Beziehungstyp eine konkrete Bedeutung.
- Nicht zum gewählten Beziehungstyp gehörende Steuerelemente werden zuverlässig ausgeblendet.
- Kontexthilfe für n:1/Lookup wurde erweitert.

## Beispiel

`ed_ev_info.typ → ed_ev_type.id`

- Ausgangs-DataForm: `Ed Ev Info`
- Zuordnungsfeld: `typ` – speichert `ed_ev_type.id`
- Lookup-/Referenz-DataForm: `Ed Ev Type`
- Anzeigefeld: `ev_type` – z. B. `Meeting`
