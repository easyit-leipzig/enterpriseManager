# RC1.8-FC1-HF19 – DataForm Scalar Escape Repair

## Laufzeitfehler
Der HF18-Renderfehler zeigte exakt:

`e(): Argument #1 ($value) must be of type string, int given`

Ursache war die Breiten-Auswahlliste. PHP konvertiert numerisch aussehende
Array-Schlüssel wie `'25'` und `'100'` in Integer. Dadurch wurde `e(25)`
aufgerufen, obwohl `e()` einen String erwartet.

## Korrektur
- Breitenwert vor `e()` explizit nach `string` konvertiert
- Breitenwert auch beim strikten `selected`-Vergleich nach `string` konvertiert
- Label defensiv nach `string` konvertiert
- HF18-Render-Fehlergrenze bleibt erhalten
- sichtbarer Laufzeitmarker: `HF19 DATAFORM RUNTIME ACTIVE`
