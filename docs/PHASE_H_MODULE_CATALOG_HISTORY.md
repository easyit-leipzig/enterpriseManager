# Phase H – Modulkatalog und Installationshistorie

RC1.7.8 erweitert die Modulverwaltung um einen zentralen Katalog und eine persistente Historie.

## Neu
- `ModuleCatalog`: installierte Module, Herkunft, Version, Status und verfügbare ZIP-Pakete.
- `ModuleInstallHistory`: protokolliert Install, Update, Remove, Enable, Disable und Fehler.
- `modules/.history.json`: persistentes, auf 500 Einträge begrenztes Verlaufsprotokoll.
- Admin-Oberfläche zeigt Katalog, Pakete aus `packages/` und die letzten 50 Aktionen.
- CLI-Installationen, -Updates und -Entfernungen schreiben ebenfalls in die Historie.

Die Historie ergänzt, ersetzt aber nicht das Enterprise-Audit-Log.
