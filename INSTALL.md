# Installation

## 1. Entpacken

Das Verzeichnis `enterpriseManager` in den Webroot entpacken, z. B. unter XAMPP nach:

```text
D:\xampp\htdocs\enterpriseManager\
```

## 2. Voraussetzungen

- PHP 8.2 oder neuer
- Apache oder vergleichbarer Webserver
- Schreibzugriff auf `storage/`, `workspace/`, `packages/`, `modules/` und die Laufzeitverzeichnisse des `DataForm5-Core`
- Für SQL-Datenbanken der passende PDO-Treiber

## 3. Start

Im Browser die Projektwurzel öffnen:

```text
http://localhost/enterpriseManager/
```

Die erste Seite ist immer die Administrator-Anmeldung.

Wenn die Administrationskonfiguration fehlt, **Setup durchführen** wählen. Im Setup erscheinen nur Datenbanktypen, deren PDO-Treiber tatsächlich geladen ist. CSV ist immer verfügbar. Die Standardzeitzone ist `Europe/Berlin`.

## 4. Superadministrator

Nach eingerichteter Administrationsdatenbank gilt:

- Fehlt noch ein aktiver Superadministrator, wird auf der Startseite das Formular **Ersten Superadministrator anlegen** angezeigt.
- Ist bereits ein aktiver Superadministrator vorhanden, wird nur die normale Anmeldung angeboten.

## 5. Abschluss

Der Install-Lock wird erst nach erfolgreichem Abschluss des Installations-/Health-Gates gesetzt. Zugangsdaten und produktive Laufzeitdaten gehören nicht in das Auslieferungs-ZIP.
