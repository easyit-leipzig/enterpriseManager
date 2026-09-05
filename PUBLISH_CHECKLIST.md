# Veröffentlichungs-Checkliste

## Automatisch im Release-Build geprüft

- Enterprise-Manager-Logo projektweit eingebunden.
- Keine reale `DataForm5-Core/.env` im Paket.
- Regenerierbare Framework-Caches und Sessions aus dem Paket entfernt.
- PHP-/JavaScript-Syntaxprüfung.
- Gesamte vorhandene Regression.
- RC1.8 Release-Gates: Security, Reproduzierbarkeit, Upgrade/Migration, Fresh Install, Cross-Component, Integration, Performance, Architektur und Quality Center.
- Release-Manifest und SHA-256.

## Vor öffentlichem Produktivbetrieb in der Zielumgebung

1. Release in einen neuen Webserver-Ordner entpacken; nicht über eine bestehende Installation kopieren.
2. Produktive `.env` aus `.env.production.example` erzeugen und echte Zugangsdaten setzen.
3. `APP_ENV=production`, `APP_DEBUG=false` und TLS/HTTPS aktivieren.
4. Installer-/Fresh-Install-Test gegen die reale MySQL/MariaDB-Version durchführen.
5. Anmeldung, Projektverwaltung, DataForm CRUD, Upload/Download, Backup/Restore und Export/Import im Zielbrowser prüfen.
6. Schreibrechte auf ausschließlich benötigte Storage-Verzeichnisse beschränken.
7. Webserver-Zugriff auf interne Storage-/Backup-Verzeichnisse prüfen.
8. Release-ZIP anhand der veröffentlichten SHA-256-Prüfsumme verifizieren.
