# Phase I – Modul-Versionierung und Kompatibilität

RC1.7.9-dev-phaseI erweitert die Modulplattform um semantische Versionsregeln.

## Manifest

Neue optionale Angabe:

```json
{
  "core_version": ">=1.7.1",
  "dependencies": {
    "export-core": "^1.2.0"
  }
}
```

Alte Listen wie `"dependencies": ["export-core"]` bleiben gültig und entsprechen `*`.

Unterstützt werden `*`, exakte Versionen, `>=`, `<=`, `>`, `<`, `^`, `~` und einfache UND-/ODER-Verknüpfungen.

Der Paketinstaller verweigert inkompatible Module vor dem Kopieren. Der Modulkatalog zeigt Core-Anforderung, Kompatibilität und verfügbare höhere Versionen aus `packages/`.
