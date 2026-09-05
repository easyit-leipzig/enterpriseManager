# RC1.8-FC1-HF1 – Installer-Systemprüfung

Ausgangsstand: `RC1.8-FC1` / `RC1.8.6-dev-phase68`.

## Korrektur

Die Tabelle „1. Systemprüfung“ in `setup.php` verwendete für die Ergebnisse aus `SystemInspector` die falschen Felder. `SystemInspector` liefert pro Check `name`, `passed`, `required` und `message`; die View verwendete dagegen den numerischen Array-Index als Namen und erwartete `ok`. Dadurch wurden die Prüfungen als `0` bis `7` angezeigt und unabhängig vom tatsächlichen Ergebnis als Fehler markiert.

HF1 liest nun `name` und `passed`, zeigt die Detailmeldung an und unterscheidet fehlgeschlagene Pflichtprüfungen (`Fehler`) von fehlgeschlagenen optionalen Prüfungen (`Optional`). Die Ausgabe der separaten Enterprise-Pflichterweiterungen bleibt unverändert.

Das Originalpaket RC1.8-FC1 wurde nicht überschrieben. Die interne Release-Identität bleibt bewusst RC1.8-FC1; HF1 ist eine korrigierte Arbeits-/Abnahmevariante und wird über Dateiname, Root-Verzeichnis und dieses Dokument eindeutig gekennzeichnet.
