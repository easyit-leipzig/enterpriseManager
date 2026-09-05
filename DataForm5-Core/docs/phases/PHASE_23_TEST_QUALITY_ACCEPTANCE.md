# Phase 23 – Test-, Qualitäts- und Abnahme-Layer

Phase 23 führt eine produktneutrale Test- und Abnahmeschicht ein.

## Bestandteile

- `Assert` mit typisierten Assertions und Exception-Prüfung
- native `TestSuite`, `CallbackTest`, `TestRunner` und `TestResult`
- `LegacyScriptRunner` für die vorhandenen Phasentests
- `QualityGate` für PHP-Syntaxprüfung und Regressionstests
- JSON-Abnahmeberichte unter `storage/test-reports/`
- CLI-Befehle `test` und `quality:check`

## Verwendung

```bash
php bin/dataform test
php bin/dataform test --filter=database
php bin/dataform quality:check
php bin/dataform quality:check --report=storage/test-reports/release.json
```

Ein Exit-Code `0` bedeutet, dass das Gate bestanden wurde. `1` kennzeichnet fehlgeschlagene Prüfungen und `2` eine ungültige Konsoleneingabe.
