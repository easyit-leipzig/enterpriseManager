# HF76-PUBLISH15 – kompakte echte Formularansicht

Die Formularansicht wurde nach der visuellen Abnahme neu gefasst. Der aktuelle Datensatz wird nicht mehr durch die natürliche 128x128-PNG-Größe des `aktueller_ds`-Bildes aufgebläht. Alle Datensatz-Navigationselemente besitzen nun eine einheitliche kompakte Größe.

## Änderungen

- Formularnavigation: erster DS, vorheriger DS, aktueller DS, nächster DS, letzter DS und neuer DS in einheitlicher Größe.
- Der aktuelle Datensatz besteht aus einem festen 34-px-Marker und einer separat gesetzten Datensatznummer.
- Der Navigationspunkt für einen neuen Datensatz verwendet `neuer_ds.png` statt des allgemeinen `neu.png`.
- Formularfelder werden als responsives Feldraster gerendert und berücksichtigen die im Feld-Designer konfigurierte Breite.
- Datum-, Zeit- und Zahlenfelder werden nicht unnötig auf die volle Formularbreite gezogen.
- Speichern/Abbrechen befinden sich in einer kompakten Formular-Aktionsleiste und verwenden ausschließlich die zentral registrierten Buttonset-Grafiken.
- Die schlanke exportierte HTML5-Anwendung verwendet dieselbe kompakte Formularnavigation und dieselben Feldbreiten.
- Eltern-/Kind-, Realvorschau- und View-Mode-Logik bleiben unverändert erhalten.
