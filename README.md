# easyIT Enterprise Manager RC1.8-FC1-HF76

Bereinigtes Laufzeitpaket des easyIT Enterprise Managers mit integriertem DataForm5.

## Startverhalten

- `index.php` startet immer auf der Administrator-Anmeldung.
- Ist der Administrationsspeicher noch nicht eingerichtet, wird **Setup durchführen** angeboten.
- Ist der Administrationsspeicher eingerichtet, aber noch kein aktiver Superadministrator vorhanden, kann das erste Superadministratorkonto direkt auf der Startseite angelegt werden.
- Existiert bereits ein aktiver Superadministrator, wird ausschließlich die Anmeldung angezeigt.

## Datenquellen

Administrations- und Projektspeicher werden unabhängig gewählt. Datenbanktypen werden nur angeboten, wenn der dafür notwendige PDO-Treiber in der aktuellen PHP-Laufzeit vorhanden ist. CSV benötigt keinen PDO-Treiber und bleibt unabhängig davon verfügbar.

Standardzeitzone im Setup ist `Europe/Berlin`.

## Paketbereinigung

Dieses Paket enthält keine Test-Suites, Testreports, alten Hotfix-/Phasenstände, historischen Release-Notizen, Build-Gates, Beispiel-Backups, Beispiel-Exporte, Cache-Dateien oder Laufzeit-Logs. Die aktuelle SDK-Referenz und die zur Anwendung gehörenden HTML-Hilfen bleiben enthalten.

Installation: siehe `INSTALL.md`.
