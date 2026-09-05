# RC1.7.6 Phase F – Modul-Paketinstaller

Phase F verbindet das Modul-SDK mit einem sicheren Installationsweg für ZIP-Module.

## Paketformat
Ein Modul-ZIP enthält entweder direkt `module.json`, `bootstrap.php`, `src/` usw. oder genau einen obersten Modulordner mit dieser Struktur.

## Funktionen
- ZIP-Installation nach `modules/<modulname>`
- Update mit Versionsprüfung und Rollback
- Deinstallation mit Abhängigkeitsprüfung
- persistentes Register `modules/.installed.json`
- Modulvalidierung vor jeder Installation
- Schutz gegen ZIP-Path-Traversal
- Limits für Dateianzahl und entpackte Größe
- CLI für Build, Install, Update und Remove

## Befehle
```bash
php tools/build-module-package.php modules/mein-modul packages/mein-modul.zip
php tools/install-module.php packages/mein-modul.zip
php tools/update-module.php packages/mein-modul-1.1.0.zip
php tools/remove-module.php mein-modul
```

## Voraussetzung
Für ZIP-Build und ZIP-Installation muss PHP `ext-zip` (`ZipArchive`) aktiviert sein. Der Installer bricht sonst mit einer eindeutigen Meldung ab.
