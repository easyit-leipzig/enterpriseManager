# Assistant-System – Phase 2: DataForm-Assistent

Phase 2 erweitert den Assistant-Core aus Phase 1 um den registrierten Fachassistenten `dataform.create`.

## Schritte

1. Datenquelle und Hauptquelle
2. DataForm-Name und Primärschlüssel
3. Volltextsuche, Filter und Paginierung
4. CRUD-Aktionen
5. Feldgrundkonfiguration
6. Validierung / Review und JSON-Export

## Verbindliche DataForm5-Regeln

- `fullTextSearch`: Ja/Nein konfigurierbar.
- `filter`: Ja/Nein konfigurierbar.
- Pagination ist aktiviert und befindet sich **immer unter den Datensätzen**.
- Pagination-Fenster: erster Datensatz/erste Seite, zwei Positionen links, aktuelle Position, zwei Positionen rechts, letzter Datensatz/letzte Seite.
- CRUD-Konfiguration enthält `create`, `show`, `edit`, `delete`, `save`.
- Die konkrete grafische Darstellung von CRUD-Aktionen wird nicht lokal im Assistenten definiert; dafür bleibt die zentrale Button-Registry verbindlich.
- Felder werden in Phase 2 grundlegend als `name`, `label`, `type`, `required`, `readOnly` erfasst.

## Wizard-Zustand

Der aktuelle Entwurf wird serverseitig in der PHP-Session gespeichert. Der Scope ist projektbezogen (`project_id`), ansonsten `global`.

## Export

Ein vollständig validierter Entwurf kann über `admin/assistants/export.php` als JSON-Konfiguration ausgegeben werden. Das Schema lautet `easyit.dataform.assistant.v1`.

## Noch nicht Teil von Phase 2

- Datenbankschema automatisch auslesen
- Beziehungen 1:n / n:m
- gebundene Eltern-/Kind-Formulare
- Event-/JavaScript-Methoden
- direktes Persistieren in eine bestehende projektspezifische DataForm-Konfigurationsdatei

Diese Punkte werden in den folgenden Phasen ergänzt.
