# Phase 34 – View-, Template- und Rendering-Layer

Der Core stellt eine produktneutrale Rendering-Schicht mit Layouts, Abschnitten, Komponenten, Themes, Asset-URLs und standardmäßig sicherem HTML-Escaping bereit.

## Regeln
- Views liegen unter `resources/views`.
- Themes dürfen Views überschreiben und Assets bereitstellen.
- Dynamische Inhalte sind mit `$view->e()` auszugeben.
- Produkte registrieren eigene Views, ohne Core-Views zu verändern.
- Layouts verwenden benannte Abschnitte; Komponenten bleiben klein und wiederverwendbar.
