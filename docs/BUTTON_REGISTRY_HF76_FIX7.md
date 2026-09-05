# HF76-FIX7 – Zentrale Button-Registry

## Verbindliche Regeln

- Aktionsbuttons verwenden ausschließlich die PNG-Dateien aus `assets/img/`.
- Farbige oder verlaufende CSS-Hintergründe für Aktionsbuttons sind verboten.
- `title` und `aria-label` stammen aus `system/ui/ButtonRegistry.php`.
- Datensatzzeiger (`*_ds`) sind ausschließlich für echte Datensatznavigation zulässig.
- Textnavigation wie die Workspace-Menüs `Datei`, `Bearbeiten`, `Ansicht`, `Projekt`, `Werkzeuge`, `Fenster`, `Hilfe` bleibt Textnavigation und trägt `data-button-skip="navigation"` bzw. wird zentral nicht dekoriert.
- Nicht explizit markierte Altbestandsaktionen werden über zentral registrierte Alias-/Action-Regeln aufgelöst. Nicht auflösbare Buttons werden im DOM mit `data-button-unresolved="1"` markiert.

## Zentrale Typen

| Schlüssel | Zentraler Titel | PNG | Scope |
|---|---|---|---|
| `neu` | Neu anlegen | `assets/img/neu.png` | `action` |
| `bearbeiten` | Bearbeiten | `assets/img/bearbeiten.png` | `action` |
| `loeschen` | Löschen | `assets/img/loeschen.png` | `action` |
| `speichern` | Änderungen speichern | `assets/img/speichern.png` | `action` |
| `abbrechen` | Zurück / Vorgang abbrechen | `assets/img/abbrechen.png` | `action` |
| `anzeigen` | Anzeigen | `assets/img/anzeigen.png` | `action` |
| `kopieren` | Kopieren | `assets/img/kopieren.png` | `action` |
| `duplizieren` | Duplizieren | `assets/img/duplizieren.png` | `action` |
| `schliessen` | Ansicht schließen | `assets/img/schliessen.png` | `action` |
| `mehr` | Weitere Aktionen anzeigen | `assets/img/mehr.png` | `action` |
| `suchen` | Suchen | `assets/img/suchen.png` | `action` |
| `filter` | Filter anwenden | `assets/img/filter.png` | `action` |
| `filter_loeschen` | Filter zurücksetzen | `assets/img/filter_loeschen.png` | `action` |
| `aktualisieren` | Ansicht aktualisieren | `assets/img/aktualisieren.png` | `action` |
| `sortieren_auf` | Aufsteigend sortieren | `assets/img/sortieren_auf.png` | `action` |
| `sortieren_ab` | Absteigend sortieren | `assets/img/sortieren_ab.png` | `action` |
| `spalten` | Spalten auswählen | `assets/img/spalten.png` | `action` |
| `tabelle` | Tabellenansicht öffnen | `assets/img/tabelle.png` | `action` |
| `formular` | Formular öffnen | `assets/img/formular.png` | `action` |
| `importieren` | Daten importieren | `assets/img/importieren.png` | `action` |
| `exportieren` | Daten exportieren | `assets/img/exportieren.png` | `action` |
| `hochladen` | Datei hochladen | `assets/img/hochladen.png` | `action` |
| `herunterladen` | Datei herunterladen | `assets/img/herunterladen.png` | `action` |
| `drucken` | Ansicht drucken | `assets/img/drucken.png` | `action` |
| `erster_ds` | Zum ersten Datensatz | `assets/img/erster_ds.png` | `record_navigation` |
| `vorheriger_ds` | Zum vorherigen Datensatz | `assets/img/vorheriger_ds.png` | `record_navigation` |
| `aktueller_ds` | Aktueller Datensatz | `assets/img/aktueller_ds.png` | `record_navigation` |
| `naechster_ds` | Zum nächsten Datensatz | `assets/img/naechster_ds.png` | `record_navigation` |
| `letzter_ds` | Zum letzten Datensatz | `assets/img/letzter_ds.png` | `record_navigation` |
| `neuer_ds` | Neuer Datensatz | `assets/img/neuer_ds.png` | `record_navigation` |
| `normaler_ds` | Datensatz auswählen | `assets/img/normaler_ds.png` | `record_navigation` |
| `auswaehlen` | Auswählen | `assets/img/auswaehlen.png` | `action` |
| `beziehung` | Beziehungen anzeigen | `assets/img/beziehung.png` | `action` |
| `beziehung_neu` | Beziehung anlegen | `assets/img/beziehung_neu.png` | `action` |
| `beziehung_loeschen` | Beziehung entfernen | `assets/img/beziehung_loeschen.png` | `action` |
| `lookup` | Referenz auswählen | `assets/img/lookup.png` | `action` |
| `backup` | Datenbank sichern | `assets/img/backup.png` | `action` |
| `restore` | Datenbank wiederherstellen | `assets/img/restore.png` | `action` |
| `archivieren` | Datensatz archivieren | `assets/img/archivieren.png` | `action` |
| `wiederherstellen` | Archivierten Datensatz wiederherstellen | `assets/img/wiederherstellen.png` | `action` |
| `sperren` | Sperren / deaktivieren | `assets/img/sperren.png` | `action` |
| `entsperren` | Entsperren / aktivieren | `assets/img/entsperren.png` | `action` |
| `einstellungen` | Einstellungen öffnen | `assets/img/einstellungen.png` | `action` |
| `hilfe` | Hilfe anzeigen | `assets/img/hilfe.png` | `action` |
| `info` | Informationen anzeigen | `assets/img/info.png` | `action` |
| `warnung` | Warnhinweis anzeigen | `assets/img/warnung.png` | `action` |
| `bestaetigen` | Aktion bestätigen | `assets/img/bestaetigen.png` | `action` |
| `start` | Start- oder Übersichtsseite öffnen | `assets/img/start.png` | `action` |
| `anmelden` | Anmelden | `assets/img/anmelden.png` | `action` |
| `abmelden` | Abmelden | `assets/img/abmelden.png` | `action` |
| `verlauf` | Änderungsverlauf anzeigen | `assets/img/verlauf.png` | `action` |
| `favorit` | Als Favorit oder primär markieren | `assets/img/favorit.png` | `action` |

## Festgelegte DataForm-Zuordnung

- DataForm/Formular öffnen → `formular.png` → **DataForm öffnen**
- Datensätze anzeigen → `anzeigen.png` → **Datensätze anzeigen**
- DataForm löschen → `loeschen.png` → **DataForm löschen**
- Neues DataForm → `neu.png` → **Neues DataForm anlegen**
- `normaler_ds.png`, `aktueller_ds.png`, `neuer_ds.png` sowie die DS-Navigationsbilder werden niemals als allgemeine Aktionsbuttons verwendet.
