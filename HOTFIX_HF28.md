# RC1.8-FC1-HF28 – Type-Aware Field Options

Die Feldbearbeitung zeigt nur noch Optionen an, die zum aktuell gewählten
Datentyp und zur NULL-Eigenschaft passen.

## Dynamische Oberfläche

### Numerische Typen
Für `int`, `bigint` und `decimal(p,s)` werden angeboten:
- UNSIGNED
- ZEROFILL

AUTO_INCREMENT wird nur für `int`/`bigint` angeboten und nur dann, wenn:
- NULL-Werte nicht zugelassen sind,
- die Tabelle noch kein anderes AUTO_INCREMENT-Feld besitzt.

ZEROFILL ergänzt UNSIGNED automatisch.

### DATETIME / TIMESTAMP
Nur hier werden angeboten:
- Vorgabewert CURRENT_TIMESTAMP
- Extra ON UPDATE CURRENT_TIMESTAMP

### TEXT
Ein normaler oder eindeutiger DataForm-Sekundärindex wird nicht angeboten,
weil die aktuelle Feldverwaltung keine Indexpräfixe für TEXT verwaltet.

### NULL
DEFAULT NULL wird nur angeboten, wenn das Feld NULL-Werte zulässt.

## Automatische Korrektur
Ändert der Benutzer den Datentyp oder die NULL-Eigenschaft und wird dadurch
eine bereits gewählte Option ungültig, entfernt die Oberfläche diese
Einstellung sofort und zeigt direkt einen Informationshinweis an.

Die gleiche Normalisierung läuft zusätzlich serverseitig vor CREATE/ALTER
TABLE. Dadurch führen veraltete Formularzustände oder deaktiviertes
JavaScript nicht mehr zu den bisherigen Typ-Konfliktmeldungen. Korrigierte
Optionen werden entfernt bzw. ZEROFILL ergänzt UNSIGNED automatisch; nach
dem Speichern wird die Korrektur als Info angezeigt.

Unbekannte Optionen, ungültige konkrete Werte und echte Datenkonflikte
bleiben weiterhin Fehler und werden nicht stillschweigend übernommen.
