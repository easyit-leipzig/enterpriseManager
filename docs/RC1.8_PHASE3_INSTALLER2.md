# RC1.8 Phase 3 – Installer 2.0 und Installationsabnahme

Der bisherige Setup-Tutorialpfad wurde zu einem echten Installer konsolidiert.

## Ablauf

1. System- und Extensionprüfung
2. Administrationsdatenbank anlegen/verbinden
3. versionierte Admin-Schema-Dateien aus `installer/schema/admin` ausführen
4. `.env` atomar schreiben
5. Erstadministrator mit `password_hash()` anlegen
6. Produkte registrieren
7. Health-Gate ausführen
8. erst danach Install-Lock und Installationsbericht schreiben

Der Lock wird nicht gesetzt, wenn das Health-Gate fehlschlägt.

## Sicherheit

- CSRF-Schutz im Webinstaller
- Datenbankname wird strikt validiert
- Admin-Passwort mindestens 12 Zeichen sowie Groß-/Kleinbuchstaben und Ziffer
- Passwort wird ausschließlich gehasht gespeichert
- `.env` wird mit restriktiven Dateirechten angelegt, soweit das Betriebssystem dies unterstützt
- Install-Lock verhindert versehentliche Neuinstallation

## CLI

`php tools/installer-preflight.php`

## Abschlussbericht

Nach erfolgreicher Installation:
`storage/logs/install-report.json`
