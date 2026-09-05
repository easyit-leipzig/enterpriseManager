# RC1.8-FC1-HF2 – MariaDB-Diagnose im Datenbank-Assistenten

## Fehlerbild

Beim Aufruf von `installer/database.php` und der Aktion **1. Verbindung testen** konnte MariaDB mit SQLSTATE 42000 / Fehler 1064 bei `current_user` abbrechen.

## Ursache

`serverDiagnostics()` verwendete in der Diagnoseabfrage `CURRENT_USER() AS current_user`. Der Alias `current_user` kollidiert in der betroffenen MariaDB-Umgebung mit dem SQL-Schlüsselwort bzw. der eingebauten Funktion `CURRENT_USER`.

## Korrektur

Der Alias wurde ausschließlich in `serverDiagnostics()` von `current_user` auf `authenticated_user` umbenannt; der zugehörige PHP-Arrayzugriff wurde entsprechend angepasst. Die Semantik der Diagnose bleibt unverändert.

Zusätzlich schützt `tests_rc18_phase3_installer2.php` nun gegen eine erneute Verwendung des problematischen Alias und prüft das neue Mapping.

## Release-Identität

Das Originalpaket RC1.8-FC1 sowie HF1 werden nicht überschrieben. Die interne Release-Identität bleibt bewusst `RC1.8-FC1`; HF2 ist eine korrigierte Arbeits-/Abnahmevariante und wird über Dateiname, Root-Verzeichnis und dieses Dokument eindeutig gekennzeichnet.
