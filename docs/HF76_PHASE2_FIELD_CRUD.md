# easyIT Enterprise RC1.8 FC1 HF76 – Phase 2

## Ziel

Phase 2 integriert das in Phase 1 eingeführte zentrale DataForm-Feldtypsystem in die Datensatzverarbeitung. Die neuen Typen werden in Neuanlage, Inline-Neuanlage, Inline-Bearbeitung, Detailformular, Detailansicht und serverseitiger Validierung verwendet.

## Umgesetzt

- zentrale Normalisierung und Validierung über `DataFormFieldTypeRegistry`
- Ganzzahl, Dezimalzahl, Währung und Prozentwert
- Boolean/Checkbox
- Datum, Uhrzeit und Datum/Uhrzeit
- E-Mail, URL, Telefonnummer
- Linkobjekt mit URL, Bezeichnung und Ziel
- Auswahl, Mehrfachauswahl, Tags und Multi-Lookup-Werte
- JSON mit Parserprüfung und optionaler Pretty-Ausgabe
- UUID und Koordinaten
- Farbe
- Richtext mit serverseitigem Sanitizing
- Markdown
- Passwortfelder mit `password_hash()` und Erhalt des bestehenden Hashes bei leerer Bearbeitung
- berechnete Felder über Template-Platzhalter `{{feldname}}`
- versteckte Felder
- Dateiformulare sind `multipart/form-data`-fähig
- Tabellenansicht verwendet typgerechte Read-only-Darstellung
- Detailansicht rendert Link, URL, E-Mail, JSON, Farbe und strukturierte Werte typgerecht
- bestehende Legacy-Typen bleiben kompatibel

## Sicherheitskorrekturen

- Richtext wird beim Speichern und erneut bei der Ausgabe sanitisiert.
- Passwörter werden niemals im Klartext gespeichert oder wieder ausgegeben.
- Medienersetzung löscht den Altbestand erst nach erfolgreichem Datensatz-Commit.
- Bei Validierungs- oder DB-Fehlern werden neu erzeugte Medien-Zwischenstände aufgeräumt.

## Automatische Tests

`tests_dataform_hf76_p2_fieldtypes.php`

Erwartung: `PASS=33 FAIL=0`.

Zusätzlich wurde ein Media-Smoke-Test für Datenbank- und Filesystem-Deskriptoren durchgeführt sowie die Syntax aller PHP-Dateien geprüft.

## Abgrenzung zu Phase 3

Phase 3 vertieft die Medienfunktionalität: vollständiger Lifecycle für `image` und `file`, kontrollierte Auslieferung, Vorschau/Download, Storage-Härtung, Ersetzen/Löschen sowie End-to-End-Tests im Projektkontext.
