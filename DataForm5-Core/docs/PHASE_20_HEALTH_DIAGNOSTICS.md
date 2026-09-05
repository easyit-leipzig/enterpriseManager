# Phase 20 – Health-, Diagnose- und Systemstatus-Layer

Diese Phase ergänzt zentrale Liveness- und Readiness-Prüfungen, registrierbare Health-Checks, Laufzeitmessung, Systeminformationen und einen zusammengefassten Diagnosebericht.

## Kernklassen

- `HealthManager`
- `HealthResult` und `HealthReport`
- `CallbackHealthCheck`
- `DirectoryWritableCheck`
- `SystemInfo`
- `DiagnosticReport`

Checks können getrennt für Readiness und Liveness registriert werden. Fehler in einem Check werden abgefangen und als Status `down` mit Exception-Information ausgegeben.
