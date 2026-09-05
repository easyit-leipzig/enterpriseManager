# HF76-PUBLISH9 – DataForm-Realvorschau

- Im DataForm-Bereich steht eine echte Runtime-Vorschau als iframe zur Verfügung.
- Ziel ist `records.php` mit denselben Projektdaten, Feldtypen, Beziehungen, Runtime-Einstellungen und AddCSS.
- Suche, Paginierung, Datensatzwechsel und Detailnavigation bleiben in der Vorschau interaktiv.
- Schreibende POST-Aktionen sind im Preview-Modus gesperrt.
- Vorschau kann manuell aktualisiert und die vollständige Runtime in einem neuen Fenster geöffnet werden.
- Die iframe-Höhe passt sich per postMessage/ResizeObserver an.
