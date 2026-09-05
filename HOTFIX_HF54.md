# HOTFIX HF54 – Paketformular-Zustand und anklickbare Paketliste

## Änderungen

- Nach einem erfolgreichen `.dfpkg`-Export bleiben die im Exportformular gewählten DataForms, Basistabellen und Paketbestandteile sichtbar.
- Der Export liefert die unveränderliche `packageId` im Response-Header `X-DataForm-Package-ID`; die Seite lädt danach gezielt den gerade erzeugten Paketstand statt das Formular auf Standardwerte zurückzusetzen.
- Fallback über `sessionStorage`, falls ein Proxy/Browser den zusätzlichen Header nicht verfügbar macht.
- Gespeicherte Pakete sind nicht mehr nur über JavaScript-Zeilenklick erreichbar: der Paketname ist ein echter Link auf `load_package=<packageId>`.
- Der bestehende Klick auf die komplette Paketzeile sowie Tastaturbedienung bleiben erhalten.
- Fehlerhafte/alte `data-package-load`-Daten fallen auf die stabile Paket-Link-URL zurück.

## Ziel

Der Export-/Bearbeitungsworkflow bleibt zustandsstabil und die Liste gespeicherter Pakete ist zuverlässig mit Maus, Tastatur und ohne JavaScript-Navigation bedienbar.
