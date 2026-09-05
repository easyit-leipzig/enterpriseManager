# RC1.8 Phase 6.6 – Security / Production Readiness

Phase 6.6 trennt Entwicklungs- und Produktionskonfiguration ausdrücklich.

## Änderungen

- `DataForm5-Core/.env` wird **nicht** mehr im Release ausgeliefert.
- `.env`, `.env.local` und `.env.production` sind in `.gitignore` geschützt.
- `.env.production.example` enthält sichere Produktionsdefaults:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `LOG_LEVEL=warning`
  - `HEALTH_ENABLED=false`
  - keine vorgegebene produktive Secrets-Key-Zeichenfolge
  - Datenbankpasswörter als verpflichtende `CHANGE_ME`-Platzhalter
- Enterprise-Sessions verwenden:
  - Strict Mode
  - Cookie-only
  - HttpOnly
  - SameSite=Lax
  - Secure-Cookie bei HTTPS
- Der Installer behält CSRF-Schutz, überschreibt bestehende `.env` nicht und setzt Dateirechte 0600.

## Gate

```bash
php tools/rc18-production-security-audit.php
```

TLS-Konfiguration, Reverse-Proxy-Header, CSP/HSTS und Penetrationstests bleiben Teil der späteren realen Deployment-Abnahme.
