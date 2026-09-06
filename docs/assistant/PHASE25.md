# Assistant Phase 25 – Release-Katalog, Ed25519-Signierung und Vertrauenskette

Phase 25 ergänzt den Phase-24-Release-Gate-Lebenszyklus um eine kryptographische Vertrauensschicht.

## Release-Katalog

Moderne Releases mit `releaseGate.status = RELEASED` werden zentral unter `storage/assistant/release-catalog/catalog.json` katalogisiert. Ein Eintrag bindet mindestens Modul-ID, SemVer-Version, Bibliotheks-Locator, Paket-SHA-256, Release-Fingerprint, Gate-Berichts-ID und Gate-Berichts-SHA-256.

## Signatur

Die kanonische Release-Bindung wird mit Ed25519 signiert. Der aktive private Schlüssel wird beim ersten Bedarf lokal unter `storage/assistant/trust/private/` erzeugt und mit restriktiven Rechten gespeichert. Private Schlüssel sind niemals Teil des Overlay-Pakets und niemals Teil öffentlicher Exporte.

Der öffentliche Trust-Store liegt unter `storage/assistant/trust/trusted-keys.json`.

## Schlüsselrotation

Eine Rotation erzeugt ein neues Ed25519-Schlüsselpaar. Die Übergangsinformation `previousKeyId -> newKeyId` wird sowohl vom vorherigen als auch vom neuen Schlüssel signiert. Historische Releases bleiben mit ihrem bisherigen, weiterhin vertrauenswürdigen Schlüssel prüfbar.

## Installationssperre

Der bestehende `DataFormModuleLibraryService::stage()` prüft moderne `RELEASED`-Module vor dem Staging gegen den Release-Katalog. Dadurch gilt dieselbe Vertrauensprüfung automatisch für direkte Installation, Abhängigkeitsauflösung, Update und Migration.

Eine Abweichung bei Paketbytes, Paket-SHA, Release-Fingerprint, Gate-SHA oder Signatur blockiert den Vorgang.

`LEGACY_RELEASED` bleibt zur Rückwärtskompatibilität ohne Phase-25-Signatur installierbar.

## Öffentliche Exporte

- `admin/assistants/release-catalog-export.php?type=catalog`
- `admin/assistants/release-catalog-export.php?type=trust`

Beide Endpunkte geben ausschließlich öffentliche Daten aus.

## Voraussetzung

PHP `ext-sodium` wird für Ed25519 benötigt.
