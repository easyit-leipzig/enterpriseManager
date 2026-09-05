# easyIT Enterprise RC1.8-FC1-HF76

Dies ist der veröffentlichungsbereite Gesamtstand von easyIT Enterprise Manager mit integriertem DataForm-Modul.

## Enthaltener Gesamtstand

- `DataForm5-Core/` – gemeinsamer technischer Core
- `products/dataform/` – vollständiges aktuell vorhandenes DataForm-Produkt
- `app/` – Enterprise-Anwendungsoberfläche
- `app/licensing/` – zentrale Lizenzverwaltung
- `system/` – Enterprise-Bootstrap, UI und gemeinsame Anwendungsschicht
- `installer/` – Installer und Admin-/Projektschemata
- `modules/` – Zielordner für produktübergreifende installierbare Module
- `packages/` – Zielordner für Paketartefakte
- `storage/` – Enterprise-Laufzeitdaten
- `workspace/` – Build- und temporäre Arbeitsdaten

## Schnellstart

1. Archiv in einen Webserver-Ordner entpacken, z. B. unter XAMPP `htdocs/easyIT-Enterprise`.
2. Apache und die benötigte Datenbank starten.
3. `http://localhost/easyIT-Enterprise/` öffnen.
4. Setup/Installer ausführen, falls die Umgebung noch nicht eingerichtet wurde.
5. Danach über `login.php` anmelden.

Weitere Hinweise stehen in `INSTALL.md` und `docs/MASTER_WORKING_STATE.md`.

## DataForm

DataForm ist Bestandteil dieses Masterstands und liegt unter `products/dataform/`. Der technische Unterbau befindet sich in `DataForm5-Core/`.

## Lizenzverwaltung

Die technische Lizenzierungslogik liegt in `DataForm5-Core/system/licensing/`. Die zentrale Enterprise-Verwaltungsseite liegt in `app/licensing/index.php`. Das zugehörige Admin-Schema befindet sich in `installer/schema/admin/004_licensing.php`.

## Release-Hinweis

Das Paket ist als vollständiger Gesamtstand vorgesehen. Lokale `.env`-Dateien, produktive Zugangsdaten und Laufzeit-Caches sind nicht Bestandteil des Release-Archivs. Vor Produktivbetrieb müssen `INSTALL.md` und `PUBLISH_CHECKLIST.md` abgearbeitet werden.
