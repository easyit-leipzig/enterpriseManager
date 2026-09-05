# Release-Candidate-Policy

Ab `1.0.0-rc.1` gilt für `DataForm5-Core`:

1. Keine neuen Framework-Layer oder produktbezogenen Funktionen.
2. Keine Breaking Changes an öffentlichen Interfaces.
3. Zulässig sind Bugfixes, Security-Fixes, Performancekorrekturen und Dokumentation.
4. Jede Änderung benötigt Regressionstest, Qualitäts-Gate, Recovery-Punkt und Prüfsumme.
5. Der RC wird erst zu `1.0.0`, wenn alle bekannten Blocker geschlossen und Installations-, Upgrade-, Restore- und Produktions-Gates nachgewiesen sind.
