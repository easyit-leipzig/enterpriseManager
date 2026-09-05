# RC1.8-FC1-HF27 – Column Default, Secondary Index & Extra Management

Die Feldbearbeitung physischer DataForm-verwalteter Tabellen wurde erweitert.

## Vorgabewert
Der bisher nur angezeigte Standardwert ist jetzt editierbar.

Unterstützt werden:
- Kein Vorgabewert
- NULL
- Fester Wert
- CURRENT_TIMESTAMP

CURRENT_TIMESTAMP ist nur für DATETIME/TIMESTAMP zulässig. NULL erfordert ein
nullable Feld. Numerische und BOOLEAN-Vorgabewerte werden validiert.

## Sekundärindex
Pro Feld kann DataForm einen eigenen sekundären Einspaltenindex verwalten:
- kein DataForm-Index
- INDEX
- UNIQUE INDEX

Primär- und Fremdschlüssel sind ausdrücklich ausgeschlossen und bleiben
geschützt. Bereits vorhandene externe oder mehrspaltige Indizes werden nur
angezeigt und niemals verändert. DataForm-eigene Indizes erhalten reservierte
Namen mit `idx_df_` bzw. `uq_df_`.

Vor dem Anlegen eines UNIQUE-Index wird auf vorhandene doppelte Nicht-NULL-
Werte geprüft.

## Extra – Mehrfachauswahl
Die Feldmaske bietet eine Mehrfachauswahl:
- UNSIGNED
- ZEROFILL
- AUTO_INCREMENT
- ON UPDATE CURRENT_TIMESTAMP

Validierung:
- UNSIGNED/ZEROFILL nur numerisch
- AUTO_INCREMENT nur INT/BIGINT, NOT NULL, nur einmal je Tabelle und nicht
  zusammen mit einem eigenen Vorgabewert
- ON UPDATE CURRENT_TIMESTAMP nur DATETIME/TIMESTAMP

Die Änderungen werden gemeinsam über ALTER TABLE ausgeführt. Die Pflichtspalte
`id`, Primärschlüssel und Fremdschlüssel bleiben unveränderbar.
