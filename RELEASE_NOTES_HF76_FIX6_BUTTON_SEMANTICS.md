# RC1.8-FC1-HF76-FIX6 – Button-Semantik und Buttonset-Reset

FIX6 korrigiert die fachliche Bildzuordnung und den verbleibenden historischen CSS-Einfluss projektweit.

- DataForm/Formular öffnen → `assets/img/formular.png`
- Datensätze eines DataForms anzeigen → `assets/img/anzeigen.png`
- DataForm löschen → `assets/img/loeschen.png`
- Neues DataForm → `assets/img/neu.png`
- `normaler_ds.png`, `aktueller_ds.png`, `neuer_ds.png`, `erster_ds.png`, `vorheriger_ds.png`, `naechster_ds.png` und `letzter_ds.png` sind ausschließlich der echten Datensatznavigation vorbehalten.
- Allgemeine Aktionen dürfen nicht mehr automatisch auf ein DS-Bild fallen.
- Der Browser-Adapter verwirft DS-Bildzuordnungen außerhalb eines Datensatz-Navigationskontexts.
- Die abschließende FIX6-CSS-Schicht besitzt höhere Spezifität als die historischen 3D-Regeln. Bei registrierten Buttons sind Containerhintergrund, Rahmen, Schatten und Pseudoelemente vollständig transparent/deaktiviert. Sichtbar ist ausschließlich das registrierte PNG.
- Alte visuelle Zustandsklassen (`secondary`, `danger` usw.) werden bei dekorierten Buttons nicht mehr als Farbskin verwendet.
- Alle 52 PNG-Dateien unter `assets/img/` entsprechen bytegenau dem gelieferten `buttonset_dbfrontend.zip`.
