# easyIT Enterprise RC1.8-FC1-HF76 – PUBLISH14

## Realvorschau folgt der Standardansicht

PUBLISH14 korrigiert die Synchronisation zwischen den DataForm-Einstellungen und der eingebetteten Realvorschau.

### Behoben

- Eine gespeicherte Standardansicht `Formular` oder `Dialog` wird dem Preview-iframe nun ausdrücklich als `runtime_view` übergeben.
- Ein Wechsel von `Tabelle` zu `Formular` oder `Dialog` im DataForm-Editor aktualisiert die Realvorschau sofort, auch bevor gespeichert wird.
- Die Vorschau kennzeichnet eine noch nicht gespeicherte Auswahl ausdrücklich als Live-Vorschau.
- "Vorschau aktualisieren" behält die aktuell im Editor ausgewählte Ansicht bei.
- Die normale Runtime-Subnavigation mit dem Umschalter "Tabelle" wird innerhalb der Realvorschau nicht mehr angezeigt.
- Die aktive Ansicht wird innerhalb des Preview eindeutig als Realvorschau der aktuell gewählten DataForm-Ansicht bezeichnet.

### Unverändert

Die Auswahl wird erst durch **DataForm-Einstellungen speichern** dauerhaft in der Projektdatenbank gespeichert. Die Live-Vorschau verändert keine DataForm-Konfiguration und keine Nutzdaten.

### Tests

- PUBLISH14 Realvorschau-/View-Sync: 11/11 PASS
- PUBLISH13 View Modes: 16/16 PASS
- PUBLISH9 iframe Real Preview: 12/12 PASS
- PUBLISH11 Preview Child Forms: 15/15 PASS
- vollständige Testsuite: 149/149 PASS
- PHP: 858/858 PASS
- JavaScript: 5/5 PASS
