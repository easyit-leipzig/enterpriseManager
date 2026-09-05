# easyIT Button Registry – HF76-FIX2

Verbindliche UI-Regel: Aktionsbuttons verwenden die registrierten PNG-Dateien unter `assets/img/`. Farbig gefüllte oder verlaufende CSS-Button-Hintergründe sind für Aktionen nicht mehr zulässig. Neue Buttons erhalten `data-button="<Bildtext>"`.

| Bildtext | Titel | Datei |
|---|---|---|
| `neu` | Neuen Datensatz anlegen | `assets/img/neu.png` |
| `bearbeiten` | Datensatz bearbeiten | `assets/img/bearbeiten.png` |
| `loeschen` | Datensatz löschen | `assets/img/loeschen.png` |
| `speichern` | Änderungen speichern | `assets/img/speichern.png` |
| `abbrechen` | Bearbeitung abbrechen | `assets/img/abbrechen.png` |
| `anzeigen` | Datensatz anzeigen | `assets/img/anzeigen.png` |
| `kopieren` | Datensatz kopieren | `assets/img/kopieren.png` |
| `duplizieren` | Datensatz duplizieren | `assets/img/duplizieren.png` |
| `schliessen` | Ansicht schließen | `assets/img/schliessen.png` |
| `mehr` | Weitere Aktionen | `assets/img/mehr.png` |
| `suchen` | Datensätze durchsuchen | `assets/img/suchen.png` |
| `filter` | Filter anwenden | `assets/img/filter.png` |
| `filter_loeschen` | Filter zurücksetzen | `assets/img/filter_loeschen.png` |
| `aktualisieren` | Ansicht aktualisieren | `assets/img/aktualisieren.png` |
| `sortieren_auf` | Aufsteigend sortieren | `assets/img/sortieren_auf.png` |
| `sortieren_ab` | Absteigend sortieren | `assets/img/sortieren_ab.png` |
| `spalten` | Spalten auswählen | `assets/img/spalten.png` |
| `tabelle` | Tabellenansicht | `assets/img/tabelle.png` |
| `formular` | Formularansicht | `assets/img/formular.png` |
| `importieren` | Daten importieren | `assets/img/importieren.png` |
| `exportieren` | Daten exportieren | `assets/img/exportieren.png` |
| `hochladen` | Datei hochladen | `assets/img/hochladen.png` |
| `herunterladen` | Datei herunterladen | `assets/img/herunterladen.png` |
| `drucken` | Ansicht drucken | `assets/img/drucken.png` |
| `erster_ds` | Zum ersten Datensatz | `assets/img/erster_ds.png` |
| `vorheriger_ds` | Zum vorherigen Datensatz | `assets/img/vorheriger_ds.png` |
| `aktueller_ds` | Aktueller Datensatz | `assets/img/aktueller_ds.png` |
| `naechster_ds` | Zum nächsten Datensatz | `assets/img/naechster_ds.png` |
| `letzter_ds` | Zum letzten Datensatz | `assets/img/letzter_ds.png` |
| `neuer_ds` | Neuer Datensatz | `assets/img/neuer_ds.png` |
| `normaler_ds` | Normaler Datensatz / Platzhalter | `assets/img/normaler_ds.png` |
| `auswaehlen` | Datensatz auswählen | `assets/img/auswaehlen.png` |
| `beziehung` | Beziehungen anzeigen | `assets/img/beziehung.png` |
| `beziehung_neu` | Beziehung anlegen | `assets/img/beziehung_neu.png` |
| `beziehung_loeschen` | Beziehung entfernen | `assets/img/beziehung_loeschen.png` |
| `lookup` | Lookup / Referenz auswählen | `assets/img/lookup.png` |
| `backup` | Datenbank sichern | `assets/img/backup.png` |
| `restore` | Datenbank wiederherstellen | `assets/img/restore.png` |
| `archivieren` | Datensatz archivieren | `assets/img/archivieren.png` |
| `wiederherstellen` | Archivierten Datensatz wiederherstellen | `assets/img/wiederherstellen.png` |
| `sperren` | Datensatz sperren | `assets/img/sperren.png` |
| `entsperren` | Datensatz entsperren | `assets/img/entsperren.png` |
| `einstellungen` | Einstellungen öffnen | `assets/img/einstellungen.png` |
| `hilfe` | Hilfe anzeigen | `assets/img/hilfe.png` |
| `info` | Informationen anzeigen | `assets/img/info.png` |
| `warnung` | Warnung / Konflikt | `assets/img/warnung.png` |
| `bestaetigen` | Aktion bestätigen | `assets/img/bestaetigen.png` |
| `start` | Startseite | `assets/img/start.png` |
| `anmelden` | Anmelden | `assets/img/anmelden.png` |
| `abmelden` | Abmelden | `assets/img/abmelden.png` |
| `verlauf` | Änderungsverlauf anzeigen | `assets/img/verlauf.png` |
| `favorit` | Als Favorit markieren | `assets/img/favorit.png` |

## Zentrale Dateien

- `system/ui/ButtonRegistry.php` – serverseitige Registry
- `assets/js/easyit-button-registry.js` – Browser-Registry und Legacy-Adapter
- `assets/css/easyit-crud-3d-buttons.css` – abschließende transparente Image-Button-Schicht
- `assets/img/buttonset.png` – kanonische Gesamtübersicht des gelieferten Sets

## Kompatibilität

Bestehende `data-crud`-Attribute und ältere Form-/Link-Aktionen werden automatisch auf die neue Registry abgebildet. Die alten `assets/img/buttonset-icons/`-Dateien bleiben ausschließlich als rückwärtskompatible Alias-Dateien erhalten.
