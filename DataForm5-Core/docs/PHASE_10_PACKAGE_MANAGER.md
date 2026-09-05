# Phase 10 – Package Manager

Der DataForm5-Core besitzt ab Build 0010 einen produktneutralen Paketmanager.

## Paketformat

Ein Paketordner enthält mindestens:

```text
package.json
payload/
```

`package.json` beschreibt Name, semantische Version, Typ, Abhängigkeiten und optionale SHA-256-Prüfsummen. Installierte Dateien werden ausschließlich unter `storage/packages/installed/<paketname>` abgelegt.

## Funktionen

- Installation, Aktualisierung und Deinstallation
- persistentes Installationsregister
- semantische Versionsprüfung
- Abhängigkeiten mit `*`, exakten Versionen, `^`, `~`, `>=`, `<=`, `>`, `<`
- SHA-256-Integritätsprüfung
- atomisches Update mit Rücksicherung bei Fehlern
- optionaler Lifecycle-Vertrag
