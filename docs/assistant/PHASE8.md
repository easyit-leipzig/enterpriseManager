# Assistant Phase 8 – Test- und Diagnose-Assistent

## Ziel

Phase 8 ergänzt den Assistant-Core um `dataform.diagnostics`. Der Assistent prüft die in Phase 1–7 gespeicherten Wizard-Zustände gemeinsam und erzeugt einen detaillierten Diagnosebericht mit den Zuständen `PASS`, `FAIL`, `WARN` und `SKIP`.

## Prüfbereiche

Der Bericht prüft:

- DataForm-Grundkonfiguration
- Volltextsuche und Filter als explizite Ja/Nein-Eigenschaften
- verbindliche Pagination unter den Datensätzen
- Pagination-Fenster: erste Seite, zwei links, aktuell, zwei rechts, letzte Seite
- Feld-, Lookup- und Derived-Enum-Konfiguration
- Datenquellenprofil und erkannte Hauptquelle
- optionalen erneuten realen Verbindungstest der Datenquelle
- CRUD-/Aktionskonsistenz
- nichtdestruktiven CRUD-Workflow für `new`, `show`, `edit`, `save`, `delete`
- Beziehungen 1:n und n:m
- gebundene Eltern-/Kindwerte
- read-only-Standard für gebundene Kindfelder
- Event-Konfiguration
- Struktur des zentralen `DataFormActionContext`
- zentrale Button-Registry, `title`, `aria-label` und Aliase

## Diagnosezustände

- `PASS`: Prüfung vollständig bestanden.
- `FAIL`: Fehler, der die Konfiguration oder Laufzeitfähigkeit verletzt.
- `WARN`: zulässige, aber auffällige oder nicht empfohlene Konfiguration.
- `SKIP`: Prüfung ist für die aktuelle Konfiguration nicht anwendbar oder wurde nicht angefordert.

Der Gesamtstatus lautet:

- `PASS`, wenn kein FAIL und keine WARN vorliegen,
- `PASS_WITH_WARNINGS`, wenn kein FAIL, aber mindestens eine WARN vorliegt,
- `FAIL`, sobald mindestens eine FAIL-Prüfung vorliegt.

## Datenquellentest

Im ersten Schritt kann `Datenquelle jetzt erneut real testen` aktiviert werden. Der Diagnose-Assistent verwendet dann den vorhandenen Datenquellenadapter. Bei MySQL/Oracle wird ein Kennwort ausschließlich über eine gespeicherte `passwordRef`/ENV-Referenz bezogen; Klartextkennwörter werden nicht in den Diagnosezustand übernommen.

Der reale Verbindungstest ist nicht schreibend.

## CRUD-Test

Der CRUD-Test ist absichtlich nichtdestruktiv. Er prüft:

- Vorhandensein der zentralen Aktionen `new`, `show`, `edit`, `save`, `delete`,
- erforderliche Datensatzbindung von `show`, `edit`, `delete`,
- dass `new` keinen vorhandenen Datensatz benötigt,
- `open -> show`,
- `create -> new`,
- Übereinstimmung von DataForm-CRUD und aktivierten Aktionen,
- Verwendung der zentralen Button-Registry.

Es werden dabei keine Testdatensätze in produktive Tabellen geschrieben oder gelöscht.

## Gebundene Formulare

Für 1:n-Beziehungen wird `BoundValueResolver` mit einem frei wählbaren Testwert ausgeführt. Standardtestwert ist `42`.

Beispiel:

`Eltern.id = 42 -> Kind.to_ev_id = 42`

Ist `readOnly=true` konfiguriert, muss das Zielfeld zusätzlich in `readOnlyFields` erscheinen.

## Event-/Context-Test

Der Assistent baut einen realen Test-Snapshot über `DataFormActionContextBuilder`. Dabei werden insbesondere geprüft:

- Schema `easyit.dataform.action-context.v1`,
- Originaldatensatz,
- aktueller Datensatz,
- automatisch ermittelte Änderungen,
- Aktion und Event,
- Pagination- und DataForm-Kontext.

## Bericht und Export

Schema:

`easyit.dataform.diagnostic-report.v1`

Export:

`*.diagnostic.json`

Jede Einzelprüfung enthält:

- `id`
- `group`
- `title`
- `status`
- `message`
- `details`
- optionale `recommendation`

Damit kann der Diagnosebericht später auch als Release-Gate oder automatisierter Testinput verwendet werden.

## Tests

Phase 8 enthält:

- `tests/assistant/phase8_smoke.php`
- Positivfall mit vollständig gültiger Konfiguration
- realen CSV-Verbindungstest
- Laufzeittest der Eltern-/Kindbindung `42 -> 42`
- Event-/ActionContext-Test
- CRUD-/Button-Konsistenztest
- nichtdestruktiven CRUD-Workflow-Test
- Negativfall mit absichtlich falscher Pagination, fehlender Datenquelle und CRUD/Button-Widerspruch
- Regression gegen Phase 7 und Phase 6
- PHP-Syntaxprüfung aller Overlay-PHP-Dateien
- HTTP-Test von Assistentenübersicht, Bericht und JSON-Export
