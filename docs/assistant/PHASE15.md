# Assistant Phase 15 – DataForm-Vorlagen und Klonen

Phase 15 ergänzt den Assistant-Core um wiederverwendbare DataForm-Vorlagen. Eine Vorlage bündelt die aktuell persistent gespeicherten Konfigurationen aus:

- `dataform.create`
- `dataform.fields`
- `dataform.relations`
- `dataform.events`
- `dataform.actions`

## Vorlagenspeicher

Vorlagen werden systemweit unter `storage/assistant/templates/` gespeichert und verwenden das Schema `easyit.assistant.dataform-template.v1`. Dadurch können sie projektübergreifend wiederverwendet werden. Projekt-ID und Zielkontext sind kein fester Bestandteil der Zielkonfiguration, sondern werden beim Klonen neu zugeordnet.

Klartextkennwörter, Secrets, Tokens und Upload-Temporärdaten werden vor dem Speichern entfernt. Zulässige Referenzen wie `passwordRef` bleiben erhalten.

## Klonablauf

1. Vorlage auswählen.
2. Zielprojekt und neuen DataForm-Namen festlegen.
3. Optional Datenquellenprofil und Hauptquelle ersetzen.
4. Zusätzliche exakte Referenzen als `alterWert|neuerWert` mappen.
5. Diagnosevorschau ausführen.
6. Erst nach ausdrücklicher Bestätigung übernehmen.

Der DataForm-Name wird automatisch in DataForm-, Feld- und Aktionskonfiguration umgeschrieben. Weitere exakte Vorkommen des Quell-DataForms werden rekursiv ersetzt, sodass beispielsweise Eltern-/Kindreferenzen konsistent auf das Ziel zeigen können. Weitere fachliche Referenzen können explizit gemappt werden.

## Diagnose vor Übernahme

Vor dem Schreiben prüft Phase 15:

- Zielkonflikte,
- DataForm-Grundkonfiguration,
- Feld-/Lookup-/Derived-Enum-Konfiguration,
- Beziehungen und gebundene Felder,
- Events,
- Aktionen und zentrale Button-Regeln.

Das Ergebnis ist `PASS`, `PASS_WITH_WARNINGS` oder `FAIL`. Bei `FAIL` wird nichts geschrieben.

Existierende Zielkonfigurationen erzeugen standardmäßig `FAIL`. Nur mit der ausdrücklichen Option `Vorhandene Zielkonfiguration versioniert überschreiben` wird die Übernahme zugelassen und als Warnung gekennzeichnet.

## Historie / Undo

Die eigentliche Übernahme verwendet den bestehenden `AssistantStateStore`. Damit erzeugen Änderungen automatisch die Phase-14-Historienversionen für die betroffenen Zielzustände. Eine versehentliche oder fachlich unerwünschte Übernahme kann daher über Undo zurückgenommen werden.

## Nachdiagnose

Nach erfolgreicher Übernahme bietet der Assistent einen direkten Übergang zu `dataform.diagnostics`, damit der geklonte Stand einschließlich projektbezogener Datenquelle und Querverbindungen vollständig geprüft werden kann.

## Admin-Integration

Der neue Assistent heißt `dataform.templates` und ist auf den Oberflächen `project.detail`, `dataform` und `assistant.center` verfügbar. Der Wizard selbst ist ebenfalls persistent und historisierbar.

## Tests

Phase 15 testet unter anderem:

- Erfassung aller fünf Konfigurationsbereiche,
- systemweiten Vorlagenspeicher,
- Secret-Sanitizing,
- automatische DataForm-Umbenennung,
- Profil-/Quellen-Mapping,
- Relations-Mapping,
- konfliktfreie Vorschau,
- Konfliktblockade ohne Überschreibbestätigung,
- versioniertes Überschreiben mit Warnung,
- persistente Zielübernahme,
- Historienintegration,
- vollständigen HTTP-Wizard,
- Regression Phase 6–15.
