# Phase 38 – UI-Komponentenbibliothek

Die UI-Schicht stellt produktneutrale, themefähige und barrierearme HTML-Komponenten bereit. Sie baut auf der View-Engine aus Phase 34 und der Formular-Engine aus Phase 37 auf.

## Enthaltene Komponenten

- Button
- Alert
- Badge
- Card
- DataTable
- Dialog
- Navigation
- Pagination
- Status
- HelpPanel

## Sicherheitsregel

Textwerte werden standardmäßig HTML-escaped. Nur ausdrücklich als bereits vertrauenswürdig übergebene Inhaltsbereiche wie Card- oder Dialog-Inhalte dürfen HTML enthalten.

## Architekturregel

Die Komponenten enthalten keine Geschäftslogik und führen keine Datenbankzugriffe aus. Produkte und Module komponieren sie über `UiManager`.
