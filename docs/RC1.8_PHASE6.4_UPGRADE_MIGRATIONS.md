# RC1.8 Phase 6.4 – Upgrade-/Migration-Abnahme

Phase 6.4 härtet den bestehenden Installer für Upgrades.

## Neue Regeln

Jede Schema-Datei wird mit SHA-256 in der Tabelle `migrations` registriert.

Beim erneuten Installations-/Upgrade-Lauf gilt:

1. Migration noch nicht registriert → ausführen und Checksum registrieren.
2. Migration registriert und Checksum identisch → überspringen.
3. Migration registriert, aber Datei verändert → Upgrade sofort blockieren.
4. Fehler während einer Migration → Transaktion zurückrollen.

Damit werden spätere Schema-Dateien nicht mehr unprotokolliert mehrfach ausgeführt.

## Manifest

`installer/MIGRATION_MANIFEST.json` enthält die erwartete Reihenfolge sowie SHA-256 und Dateigröße aller Admin- und Projektmigrationen.

## Gate

```bash
php tools/rc18-upgrade-migration-audit.php
```

Das Gate prüft Reihenfolge, Manifestprüfsummen sowie die Schutzmechanismen des Runners.

Eine echte Upgrade-Ausführung gegen eine bestehende MySQL/MariaDB-Installation bleibt eine separate Umgebungsabnahme.


## HF3-Hinweis: MySQL/MariaDB-DDL und Transaktionen

Der Schema-Runner kapselt MySQL/MariaDB-DDL nicht in eine PDO-Transaktion. DDL-Anweisungen wie `CREATE TABLE` verursachen dort implizite Commits. Eine Migration wird deshalb nach erfolgreicher Ausführung des Schema-Callables mit SHA-256 registriert. Ein pauschales transaktionales Rollback einer DDL-Migration wird nicht zugesichert.
