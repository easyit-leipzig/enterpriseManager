# Mitarbeit und Entwicklungsregeln

1. Änderungen werden in einem Entwicklungszweig vorgenommen.
2. Der Enterprise Core enthält nur produktübergreifende Funktionen.
3. Fachlogik gehört in `products/<name>`.
4. Wiederverwendbare Erweiterungen gehören in `modules/<name>`.
5. Jede sichtbare Seite verwendet das gemeinsame Layout und die rechte Kontexthilfe.
6. Änderungen werden in `CHANGELOG.md` dokumentiert.
7. Zugangsdaten, `.env`, Logs und Laufzeitdaten dürfen nicht eingecheckt werden.
8. Vor einem Build ist `health.php` lokal zu prüfen.
