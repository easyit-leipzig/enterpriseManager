# Assistant Phase 27 – Trust-Backup, Offline-Verifikation und Recovery

Phase 27 erweitert Phase 26 um einen kontrollierten Recovery-Pfad für die Ed25519-Vertrauenskette.

## Assistent

`dataform.trust-recovery`

## Public-Trust-Bundle

Exportiert werden ausschließlich öffentliche Daten:

- `trusted-keys.json`
- `policy.json`
- `audit.jsonl`
- `release-catalog.json`
- `trust-public-manifest.json`

Jeder Bestandteil ist im Manifest mit SHA-256 gebunden. Private Schlüssel sind ausdrücklich ausgeschlossen.

Beim Import werden neue öffentliche Keys standardmäßig nur als `VERIFY_ONLY` übernommen. Ein Import mit `RELEASE`-Vertrauen verlangt die exakte Bestätigung `IMPORT PUBLIC ANCHORS AS RELEASE`.

## Verschlüsseltes Private-Key-Backup

Private Signing-Keys werden niemals unverschlüsselt exportiert. Das Backup verwendet:

- Argon2id über `sodium_crypto_pwhash`
- libsodium `secretbox` (XSalsa20-Poly1305)
- zufälliges Salt und Nonce
- eine Benutzer-Passphrase mit mindestens 12 Zeichen

Die Passphrase wird weder in Assistenten-State, Session-Persistenz noch Backup-Metadaten gespeichert.

Bestätigung für den Export:

`BACKUP PRIVATE KEYS`

Bestätigung für den Restore:

`RESTORE PRIVATE KEYS`

Beim Restore wird für jedes Secret geprüft, dass der daraus abgeleitete Public Key exakt zum registrierten Ed25519-Public-Key passt.

## Offline-Prüfpaket

Ein signierter Release-Katalogeintrag kann als `*.trust-offline.zip` exportiert werden. Enthalten sind:

- öffentlicher Trust Store
- Trust Policy
- hashverketteter Audit-Verlauf
- signierter Release-Katalogeintrag
- exaktes DataForm-Modulpaket
- Gate-Bericht, sofern vorhanden
- `offline-manifest.json`
- eigenständiges `verify.php`

Der Offline-Verifier prüft ohne private Schlüssel:

1. SHA-256 aller Bundle-Dateien,
2. Trust-Key-Status und Gültigkeit,
3. kryptographische Rotationskette,
4. Ed25519-Katalogsignatur,
5. Modul-Paket-SHA-256,
6. Gate-Berichts-SHA-256,
7. Trust-Audit-Hashkette.

## Lost-Key-Recovery

Wenn ein privater Signing-Key fehlt, erzeugt Phase 25/26 nicht mehr stillschweigend eine neue unverknüpfte Vertrauenswurzel. Stattdessen wird Phase 27 verlangt.

Bevorzugter Weg:

1. verschlüsseltes Private-Key-Backup wiederherstellen,
2. normale Schlüsselrotation verwenden.

Nur ohne verfügbares Backup darf ein administrativer Lost-Key-Rollover durchgeführt werden. Bestätigung:

`RECOVER LOST KEY <Key-ID>`

Dabei wird der verlorene Key auf `VERIFY_ONLY` zurückgestuft und ein neuer unabhängiger Recovery-Root erzeugt. Dieser Übergang wird ausdrücklich als `ADMINISTRATIVE_NOT_CRYPTOGRAPHIC` protokolliert, weil ohne den alten privaten Key keine kryptographische Alt→Neu-Kontinuität beweisbar ist.

## Download-Schutz

`admin/assistants/trust-recovery-download.php` liefert nur Dateien aus den fest definierten Recovery-Verzeichnissen aus. Basename-Prüfung, Realpath-Prüfung, Traversal-Schutz und `no-store` sind aktiv.

## Voraussetzungen

- PHP `ext-sodium`
- PHP `PharData` / Phar-ZIP-Unterstützung

## Sicherheit

Das Overlay enthält keine privaten Signing-Keys, keine Passphrases, keine Runtime-Recovery-Bundles und keine Testprojekte.
