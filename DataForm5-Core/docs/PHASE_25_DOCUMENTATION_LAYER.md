# Phase 25 – Dokumentations-, Metadaten- und Entwicklerportal-Layer

Build 0025 ergänzt DataForm5-Core um einen automatisch erzeugbaren Systemkatalog. Der Layer scannt die öffentlich dokumentierbaren PHP-Komponenten, erzeugt maschinenlesbare JSON-Metadaten und eine Markdown-Übersicht für Entwickler.

## CLI

```bash
php bin/dataform docs:generate
php bin/dataform docs:generate --target=storage/documentation
```

## Artefakte

- `component-catalog.json`
- `component-catalog.md`
- `build-overview.md`

Der Katalog ist Build-Metadatum und kann später vom DataForm-Produkt, einem Entwicklerportal oder der interaktiven Kontexthilfe gelesen werden.
