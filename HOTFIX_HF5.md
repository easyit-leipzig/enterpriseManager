# RC1.8-FC1-HF5 – Recovery / Factory Reset

HF5 ergänzt eine Recovery Console für den Fall beschädigter oder nicht mehr startfähiger Datenbanken.

- Recovery-Link auf öffentlicher Startseite und Enterprise-Dashboard.
- Zugriff auf localhost auch ohne funktionierende Datenbank möglich.
- Außerhalb von localhost nur mit bestehender Admin-Session.
- Setup-Reset: archiviert und entfernt `.env` + Install-Lock, Datenbanken bleiben bestehen.
- Vollreset: explizite Doppelbestätigung, löscht bekannte Admin-/Projektdatenbanken und entfernt anschließend `.env` + Install-Lock.
- Vor dem Reset werden `.env` und Install-Lock unter `DataForm5-Core/storage/recovery/factory-reset/` archiviert.
- Neuer Regressionstest `tests_rc18_phase4_recovery_reset.php`.
