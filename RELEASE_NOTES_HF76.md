# easyIT Enterprise RC1.8-FC1-HF76

HF76 erweitert DataForm von der bisherigen festen Typauswahl zu einem zentral registrierten Feldtypsystem mit 33 Typen und führt diese Typen durch Designer, CRUD, physische Tabellenbindung, Validierung, Medienverwaltung, CSV, REST/OpenAPI und `.dfpkg`.

## Neue und erweiterte Feldtypen
Neben den bisherigen Typen stehen unter anderem JSON, Link, Richtext, Markdown, Ganzzahl, Dezimalzahl, Währung, Prozent, Boolean, Uhrzeit, Telefon, Mehrfachauswahl, Tags, Lookup, Multi-Lookup, UUID, Koordinaten, Farbe, berechnete und versteckte Felder sowie Datei und Bild zur Verfügung.

## Datei und Bild
`file` und `image` können pro Feld entweder in der Datenbank oder im verwalteten Dateisystem gespeichert werden. MIME-Typ, Größe und SHA-256 werden kontrolliert; Bildvorschauen werden nur für sichere Bildformate inline angeboten. Filesystem-Medien werden nicht direkt aus dem Uploadverzeichnis ausgeliefert, sondern über einen kontrollierten Medienendpunkt bereitgestellt.

## Integration
CSV, REST/OpenAPI und Projektpakete verwenden dieselbe Feldtyp-Normalisierung wie das normale CRUD. `.dfpkg` 1.2 transportiert referenzierte Medien dedupliziert nach SHA-256 und legt sie beim Import gemäß der Speicherstrategie des Zielprojekts neu ab.

## Upgrade
HF76 ist ein vollständiges Gesamtpaket und kein Patch. Hinweise für die Übernahme der lokalen Konfiguration und persistenter Dateien aus HF19 stehen in `MIGRATION_HF19_TO_HF76.md`.
