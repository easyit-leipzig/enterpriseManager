# DataForm5-Core 1.0.0 – build0045

Eigenständiger, stabiler Enterprise-Core unter `easyIT-Enterprise/DataForm5-Core`.

Dieser Stand ist das finale Release der ersten Core-Hauptversion. Neue fachliche Funktionen werden ab jetzt in `products/` oder `modules/` entwickelt. Der Core erhält innerhalb der Version 1 nur rückwärtskompatible Fehler-, Sicherheits-, Performance- und Dokumentationskorrekturen.

## Wichtige Dokumente

- `docs/FINAL_RELEASE_1_0_0.md`
- `docs/LTS_POLICY.md`
- `docs/PRODUCT_HANDOFF.md`
- `docs/BUILD_0045.md`

## Zentrale Prüfungen

```bash
php bin/dataform quality:check
php tests/final_release_layer.php
```

## Frühere Build-Hinweise


# DataForm5-Core build0023

Eigenständiger Enterprise-Core unter `easyIT-Enterprise/DataForm5-Core`.

Phase 23 ergänzt den Core um einen Test-, Qualitäts- und Abnahme-Layer mit Assertions, Test-Suites, Regressionstest-Runner, PHP-Syntaxprüfung, JSON-Berichten und den CLI-Befehlen `test` sowie `quality:check`.

Dokumentation: `docs/phases/PHASE_23_TEST_QUALITY_ACCEPTANCE.md`

## Phase 24 – Deployment, Release und Update

Build 0024 stellt Release-Manifeste, Dateiprüfsummen, Versionsvergleiche und einen zentralen Wartungsmodus bereit. Details: `docs/phases/PHASE_24_DEPLOYMENT_RELEASE_UPDATE.md`.

## Build 0026 – Kontext-Hilfe

Kontextbezogene Hilfe ist über `HelpResolver` und `HelpRenderer` verfügbar. Unterstützte Modi: `short`, `steps`, `expert`. CLI: `php bin/dataform help:show /admin/projects --mode=steps`.

## Phase 32 – Secrets und Verschlüsselung

Vertrauliche Werte werden über `DataForm5\Secrets\Core\SecretManager` verwaltet. Produktionsschlüssel müssen extern über `SECRETS_KEY` bereitgestellt werden. Siehe `docs/phases/PHASE_32_SECRETS_ENCRYPTION.md`.

## Build 0033

Phase 33 ergänzt Messaging, Command/Query Dispatching und einen Integration Event Bus.

## Build 0035 – Workflow und State Machine

Workflows werden über `DataForm5\Workflow\Core\WorkflowManager` registriert und ausgeführt. Details: `docs/phases/PHASE_35_WORKFLOW_STATE_MACHINE.md`.

## Build 0037 – Formular-Engine

Phase 37 ergänzt die produktneutrale Grundlage für Formulardefinitionen, Feldtypen, Datenbindung, Validierung, CSRF-Einbindung und sicheres HTML-Rendering.


## Build 0040 – Performance-Profiler

Phase 40 ergänzt Laufzeit-, Speicher- und Engpassmessungen über `DataForm5\Performance\Contracts\ProfilerInterface`. Dokumentation: `docs/phases/PHASE_40_PERFORMANCE_PROFILER_OPTIMIZATION.md`.

## Build 0042 – Enterprise-Härtung

Der Core enthält nun ein verbindliches Produktions-Gate und eine Middleware für sichere HTTP-Standardheader. Details: `docs/phases/PHASE_42_ENTERPRISE_HARDENING.md`.
