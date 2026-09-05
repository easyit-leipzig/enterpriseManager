# RC1.8-FC1-HF30 – DataForm Delete CRUD

Die DataForm-Liste besitzt jetzt neben **Öffnen** eine kontrollierte Löschfunktion.

## Sicherheit
- CSRF-Schutz
- Browser-Warnung über abhängige Daten
- zweite Bestätigung durch exakte Eingabe des DataForm-Namens
- serverseitige Prüfung dieser Namensbestätigung
- gesamte Datenbereinigung in einer Transaktion

## Bereinigung
Beim Löschen werden DataForm-abhängige Strukturen bereinigt, darunter Felder,
Datensätze, Listen-/Filtereinstellungen, Importdaten, Layout/Verhalten/Versionen,
Workflow, Beziehungen und automatisch erzeugte Lookup-Felder.

Abhängige gespeicherte Abfragen sowie direkt darauf basierende Berichte und
API-Endpunkte werden ebenfalls entfernt. API-Request-Logs bleiben erhalten; ihr
Endpoint-Verweis wird vor dem Löschen auf NULL gesetzt.

Wenn das DataForm Elternseite einer 1:n-Beziehung war, werden zugehörige
Lookup-Felder im Kind-DataForm entfernt. Parent-Default-Konfigurationen, die
auf gelöschte Beziehungen zeigen, werden auf einen leeren definierten
Standardwert zurückgesetzt.
