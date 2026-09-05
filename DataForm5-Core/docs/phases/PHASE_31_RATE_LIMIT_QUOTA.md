# Phase 31 – Rate-Limit-, Quota- und Schutzschicht

Diese Phase schützt APIs, Anmeldung, Exporte und weitere teure oder missbrauchsgefährdete Vorgänge durch deterministische Zeitfenster und Verbrauchsquoten.

## Komponenten

- `RateLimiterInterface`
- `RateLimitStoreInterface`
- `RateLimiter`
- `RateLimitResult`
- In-Memory- und JSON-Dateispeicher
- `RateLimitServiceProvider`

## Sicherheitsregeln

- Schlüssel müssen einen kontrollierten Kontext enthalten, z. B. Benutzer-, Projekt- oder IP-Kennung.
- Passwörter, Tokens und vollständige Nutzdaten dürfen nicht Bestandteil eines Schlüssels sein.
- Ein abgelehnter Versuch liefert `Retry-After` und verändert den Verbrauch nicht weiter.
- Produktmodule können eigene Limits definieren; der Core stellt Mechanismus und Standards bereit.
