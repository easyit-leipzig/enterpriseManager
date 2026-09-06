# Assistant Phase 28 – Trust-Disaster-Recovery und Mehrinstanz-Vertrauen

Phase 28 ergänzt `dataform.trust-federation`.

## Rollen

- `PRIMARY`: aktive Signierinstanz; darf Release-Signaturen und autoritative Sync-Pakete erzeugen.
- `SECONDARY`: synchronisierte Standby-/Recovery-Instanz; darf verifizieren, aber nicht signieren.
- `VERIFY_ONLY`: reine Prüf-/Leserolle ohne Signierrecht.

Sobald eine lokale Phase-28-Instanz initialisiert ist, blockiert `ReleaseTrustStore` Signierung und Schlüsselrotation auf allen Nicht-PRIMARY-Rollen. Installationen ohne Phase-28-Instanzdatei bleiben für bestehende Phase-25–27-Installationen rückwärtskompatibel.

## Public-Trust-Synchronisation

PRIMARY erzeugt signierte `*.trust-sync.zip`-Pakete. Sie enthalten Public Trust Store, Policy, Audit, Release-Katalog und Quellinstanz-Metadaten.

Die Prüfung unterscheidet:

- `IN_SYNC`: identischer Public-Trust-Digest.
- `FAST_FORWARD`: lokaler Digest entspricht exakt dem vorherigen PRIMARY-Digest.
- `DIVERGED`: lokaler Stand ist nicht direkter Vorgänger; Anwendung erfordert `FORCE TRUST SYNC <source-id>`.
- `PRIMARY_CONFLICT`: beide Instanzen sind PRIMARY; automatische Synchronisation wird blockiert.
- `BOOTSTRAP_REQUIRED`: leere Zielinstanz; zuerst Phase 27 Public-Trust-Import oder Phase-28-Disaster-Restore.

Eine Rotation auf dem PRIMARY kann über die im Sync-Paket enthaltene Ed25519-Rotationskette gegen einen bereits lokal bekannten alten Trust-Anker verifiziert werden.

## Disaster-Recovery

PRIMARY kann ein vollständiges `*.trust-disaster.zip` erzeugen. Enthalten sind:

- Public Trust Store
- Policy
- Trust-Audit
- Release-Katalog
- Instanz-/Peer-Metadaten
- Federation-Audit
- verschlüsseltes Phase-27-Private-Key-Backup
- signiertes Manifest und SHA-256-Bindungen

Der Restore ist nur auf einer leeren Trust-Instanz erlaubt. Die neue Installation startet absichtlich als `SECONDARY`. Eine Promotion auf `PRIMARY` erfordert gültige Trust-Chain, gültiges Audit, vorhandenen privaten aktiven Signing-Key und die exakte Bestätigung `PROMOTE <instance-id> TO PRIMARY`.

## Federation-Audit

Instanzinitialisierung, Rollenwechsel, Sync-Export/-Import sowie Disaster-Export/-Restore werden in einer eigenen hashverketteten Federation-Auditdatei dokumentiert.

## Sicherheit

- kein Sync-Bootstrap per Trust-on-first-use
- Public Anchor muss bereits vertrauenswürdig sein oder über Disaster-Recovery wiederhergestellt werden
- Dual-PRIMARY wird beim Sync erkannt und blockiert
- Divergenz benötigt explizites FORCE und erzeugt vorher ein Public-Trust-Backup
- Disaster-Restore überschreibt keinen bestehenden Trust-Stand
- Restore startet nicht automatisch als PRIMARY
- Passphrases werden nicht persistiert
- private Schlüssel verbleiben außerhalb des Overlay-Pakets
