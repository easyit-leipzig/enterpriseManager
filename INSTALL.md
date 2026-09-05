# Installations- und Setup-Tutorial

## Ziel

Nach Abschluss dieser Anleitung erscheint unter der Projekt-URL die easyIT-Enterprise-Startseite statt des Apache-Verzeichnislistings. Alle bereitgestellten Seiten besitzen rechts die gemeinsame Kontexthilfe.

## Voraussetzungen

- Windows 10 oder 11
- XAMPP mit Apache und MariaDB/MySQL
- PHP 8.2 oder neuer
- Browser
- optional Git

## 1. ZIP entpacken

Entpacken Sie das vollständige ZIP nach:

```text
D:\xampp\htdocs\easyit-enterprise-RC1.0.6-dev\
```

Kontrollieren Sie unmittelbar danach:

```text
D:\xampp\htdocs\easyit-enterprise-RC1.0.6-dev\index.php
```

Existiert stattdessen ein weiterer gleichnamiger Unterordner, verschieben Sie dessen Inhalt eine Ebene nach oben.

## 2. XAMPP starten

Starten Sie im XAMPP Control Panel:

- Apache
- MySQL

Beide Einträge sollten grün markiert sein.

## 3. Zentralen Einstiegspunkt öffnen

Öffnen Sie:

```text
http://localhost/easyit-enterprise-RC1.0.6-dev/
```

Erwartetes Ergebnis: Die easyIT-Enterprise-Startseite wird angezeigt. „Index of …“ darf nicht mehr erscheinen.

## 4. Systemprüfung

Öffnen Sie auf der Startseite **System prüfen**. Kontrolliert werden PHP-Version, wichtige Erweiterungen und Schreibrechte.

Für spätere Paketfunktionen sollte insbesondere `zip` aktiviert sein. Unter XAMPP erfolgt dies gewöhnlich in `php.ini` durch Aktivieren von:

```ini
extension=zip
```

Nach Änderungen an `php.ini` muss Apache neu gestartet werden.

## 5. Verzeichnisse

Diese Verzeichnisse müssen beschreibbar sein:

```text
storage/
workspace/
packages/
```

Unter einer normalen lokalen XAMPP-Installation sind keine zusätzlichen Windows-Rechte erforderlich. Bei einer Schutzmeldung starten Sie XAMPP nicht blind als Administrator, sondern prüfen zuerst Besitz und Schreibschutz des Projektordners.

## 6. Umgebungsdatei

Die Datei `DataForm5-Core/.env.example` ist nur eine Vorlage. Eine echte `.env` wird erst benötigt, sobald die Installerphase aktiviert wird. Zugangsdaten gehören niemals in Git.

## 7. Datenbankkonzept

DataForm 5 trennt:

1. Administrationsdatenbank, beispielsweise `easyit_admin`
2. Projektdatenbank, beispielsweise `easyit_project_demo`

In RC1.0.6-dev werden diese Datenbanken noch nicht automatisch erzeugt. Das verhindert, dass beim reinen Struktur-Setup unbeabsichtigt Datenbestände angelegt oder verändert werden.

## 8. Git initialisieren

Optional:

```bat
cd D:\xampp\htdocs\easyit-enterprise-RC1.0.6-dev
git init
git add .
git commit -m "easyIT Enterprise RC1.0.6-dev basis"
```

## 9. Abschlussprüfung

Das Setup ist erfolgreich, wenn:

- die Enterprise-Startseite angezeigt wird,
- die rechte Hilfeleiste vorhanden ist,
- `health.php` keine kritischen Fehler meldet,
- die Markdown-Dokumente vorhanden sind,
- das Apache-Verzeichnislisting nicht mehr erscheint.

## Fehlerbehebung

### Es erscheint weiterhin „Index of …“

Die Datei `index.php` liegt nicht direkt im aufgerufenen Ordner oder Apache greift auf einen anderen Ordner zu.

### Seite bleibt weiß

Aktivieren Sie für die lokale Entwicklung vorübergehend PHP-Fehleranzeige oder prüfen Sie das Apache/PHP-Fehlerprotokoll. Prüfen Sie außerdem, ob PHP mindestens Version 8.2 hat.

### ZIP wird als fehlend angezeigt

Aktivieren Sie `extension=zip` in `php.ini` und starten Sie Apache neu.

## Lokale Konfiguration automatisch erzeugen

Öffnen Sie im Setup-Assistenten **Schritt 5 – lokale Konfiguration erzeugen**. Der Installer prüft die Vorlage und die Schreibrechte und erzeugt anschließend:

```text
DataForm5-Core/.env
```

Eine bereits vorhandene Datei wird nicht überschrieben. Die lokale `.env` ist über `.gitignore` vom Repository ausgeschlossen. Datenbanknamen und Passwörter werden erst in den folgenden Installationsphasen eingetragen.
