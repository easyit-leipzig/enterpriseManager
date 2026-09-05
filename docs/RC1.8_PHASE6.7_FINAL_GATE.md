# RC1.8 Phase 6.7 – Performance / Final Release Gate

Phase 6.7 ergänzt keine neuen Produktfunktionen. Sie führt die vorhandenen Release-Gates in eine finale Abnahmekette zusammen.

## Performance-Audit

```bash
php tools/rc18-performance-audit.php
```

Der Audit misst reproduzierbare interne Operationen wie Release-Manifest-Parsing, Provider-Registry-Laden und SDK-Dokumentationszugriff über 100 Iterationen und prüft zusätzlich den Peak-Memory-Verbrauch.

## Finales Release-Gate

```bash
php tools/rc18-final-release-gate.php
```

Es bündelt:

- Production Security
- Reproducibility
- Upgrade/Migrations
- Fresh Install
- Cross Component
- Integration Baseline
- Performance
- Architecture Audit
- Quality Center

Ein einzelnes FAIL blockiert den RC1.8-Finalkandidaten.
