# Phase 24 – Deployment-, Release- und Update-Layer

Build 0024 ergänzt den DataForm5-Core um reproduzierbare Release-Manifeste, SHA-256-Dateinachweise, Versionsvergleich und einen dateibasierten Wartungsmodus.

## Verbindliche Regeln

1. Ein Release wird vor der Auslieferung über `ReleaseManager::createManifest()` erfasst.
2. Die Manifestintegrität und alle enthaltenen Dateien werden vor Installation oder Rollback geprüft.
3. Updates werden nur im Wartungsmodus durchgeführt.
4. Vor jedem Update ist über den Recovery-Layer ein Wiederherstellungspunkt anzulegen.
5. Die Core-Version steht ausschließlich in `VERSION`.
