# Phase 26 – Kontext-Hilfe- und Assistenz-Layer

Build 0026 führt ein produktneutrales Hilfesystem ein. Hilfethemen werden über Kontexte oder Routen aufgelöst und in drei verbindlichen Modi ausgegeben:

1. **Kurzhilfe (`short`)** – kompakte Erklärung der aktuellen Seite.
2. **Schritt-für-Schritt (`steps`)** – konkrete Bearbeitungsfolge.
3. **Expertenmodus (`expert`)** – Architektur, technische Hintergründe und typische Zusammenhänge.

Jedes Thema kann Beispieleingaben enthalten. Produkte und Module können eigene `HelpProviderInterface`-Implementierungen registrieren. Die Core-Themen liegen unter `resources/help/<locale>`.

CLI-Beispiel:

```bash
php bin/dataform help:show /admin/projects --mode=steps
```
