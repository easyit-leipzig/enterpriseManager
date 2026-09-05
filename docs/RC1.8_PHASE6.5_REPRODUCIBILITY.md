# RC1.8 Phase 6.5 – Release-Manifest und Reproduzierbarkeit

`RELEASE_MANIFEST.json` enthält Version, Release-Metadaten, deterministisch sortierte Dateiliste, Dateigrößen und SHA-256-Prüfsummen.

Erzeugung:
`php tools/rc18-build-release-manifest.php`

Prüfung:
`php tools/rc18-reproducibility-audit.php`

Flüchtige Runtime-Verzeichnisse wie Cache, Logs, Sessions, Temp- und Test-Runtime werden bewusst weder in das Release-Manifest noch in den finalen Release-Kandidaten übernommen.
