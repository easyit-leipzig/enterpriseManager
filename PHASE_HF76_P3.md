# RC1.8-FC1-HF76 – Phase 3

## Ziel
Vollständiger und sicherer Datei-/Bild-Lifecycle für die DataForm-Feldtypen `file` und `image`.

## Umgesetzt
- Speicherung wahlweise im Dateisystem oder im Datenbank-Deskriptor.
- Serverseitige MIME-Prüfung mit `finfo` und konfigurierbarem MIME-Whitelist-Feld.
- Größenlimit je DataForm-Feld.
- Bildprüfung über `getimagesize`, optionale Breiten-/Höhenlimits.
- Für `image` ausschließlich sichere Rasterformate JPEG, PNG, WebP und GIF; SVG/aktive Bildformate werden nicht inline akzeptiert.
- SHA-256- und Größenprüfung bei jeder Auslieferung.
- Atomisches Schreiben von Filesystem-Medien.
- Zufällige serverseitige Dateinamen; unbekannte MIME-Typen erhalten `.bin` statt einer Client-Erweiterung.
- Upload-Baum mit `.htaccess`, `-ExecCGI`, `Require all denied` und deaktiviertem Directory Listing geschützt.
- Kontrollierte Auslieferung ausschließlich über `products/dataform/media.php`.
- Inline-Vorschau nur für sichere Bildtypen und nur bei aktivierter Feldoption; sonst Attachment-Download.
- Security Header: nosniff, sandbox CSP, same-origin resource policy, no-store.
- Bestehende Medien in der Bearbeitung mit Vorschau/Download/Entfernen.
- Ersetzen: altes Filesystem-Medium wird erst nach erfolgreichem Datensatz-Commit gelöscht.
- Fehler-Rollback: bei Validierungs-/DB-Fehler wird nur ein neu hochgeladenes Medium entfernt.
- Datensatz- und Bulk-Löschung bereinigen verwaltete Filesystem-Medien.
- Deskriptor-Version 2 mit MIME, Größe, SHA-256, Speicherart, Zeitstempel und bei Bildern Dimensionen.
- Version-1-Deskriptoren aus Phase 2 bleiben lesbar.

## Zieltests
- Phase-2-Feldtypenregression: 33/33 PASS.
- Phase-3-Medientests: 17/17 PASS vor Abschlussprüfung.
- Vollständiger PHP-Syntaxcheck und Release-Manifest werden vor Paketierung neu erzeugt.
