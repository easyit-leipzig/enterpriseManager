# RC1.8 Phase 2 – Performance- und Cache-Konsolidierung

Diese Phase nutzt die vorhandene Cache-Schicht konsequenter, ohne neue Fachfunktionen einzuführen.

- `CacheNamespace` verhindert Schlüsselkonflikte zwischen Subsystemen.
- Modul-Discovery cached geparste `module.json`-Daten mit einem Fingerprint aus Pfad, mtime und Dateigröße.
- Route- und API-Registry werden innerhalb eines Requests memoisiert.
- Modul-UI wird pro Benutzer-Rechtebild innerhalb eines Requests memoisiert.
- `tools/cache-clear.php` leert Laufzeit- und Config-Cache.
- `tools/cache-status.php` zeigt den lokalen Cachezustand.

Der Discovery-Fingerprint sorgt dafür, dass geänderte Modulmanifeste automatisch einen neuen Cache-Key erzeugen.
