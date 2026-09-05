# RC1.8 Phase 1 – Core-Konsolidierung

Diese Phase ist funktionsneutral. Sie reduziert technische Inkonsistenzen aus
dem RC1.7-Ausbau, ohne bestehende Enterprise-Funktionen zu entfernen.

## Änderungen

- zentrale Namespace-Map `bootstrap/namespaces.php`
- verkleinerter zentraler Autoloader
- zentrale ServiceProvider-Liste `config/providers.php`
- `bootstrap/app.php` registriert Provider generisch
- versehentlich ausgeliefertes Testmodul `modules/phase-o-smoke` entfernt
- Architektur-Audit unter `tools/architecture-audit.php`
- Legacy-Loader der historischen `DataForm\Database`-Schicht explizit als
  Übergangsbrücke dokumentiert

## Bewusst noch nicht geändert

Die Datenbankschicht verwendet historisch zusätzlich den Namespace
`DataForm\Database`. Eine aggressive Namespace-Migration würde sehr viele
bestehende Klassen und Tests gleichzeitig betreffen. Sie wird daher nicht
heimlich in Phase 1 vorgenommen, sondern bleibt als dokumentierte
Kompatibilitätsbrücke erhalten.

## Ziel

RC1.8 soll durch Konsolidierung stabiler werden, nicht durch riskantes
Groß-Refactoring. Jede folgende Bereinigungsphase soll weiterhin einen
vollständig testbaren Projektstand ergeben.
