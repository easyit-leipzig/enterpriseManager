# Assistant Phase 14 – Historie und Undo/Redo

Phase 14 erweitert die projektbezogene Persistenz aus Phase 13 um eine versionierte Historie je Assistent und Scope.

## Speicherstruktur

Aktueller Zustand:

`projects/<projekt-id>/config/assistant/state/`

Historie:

`projects/<projekt-id>/config/assistant/history/<assistant>--<scope-hash>/`

Jeder Historienordner enthält `history.json` sowie unveränderliche Versionsdateien unter `versions/`.

## Verhalten

- Jede echte persistente Zustandsänderung erzeugt automatisch eine Historienversion.
- Die erste Änderung erzeugt eine Baseline und den ersten neuen Zustand.
- Undo verschiebt den aktiven Versionszeiger um eine Version zurück und stellt diesen Zustand persistent wieder her.
- Redo verschiebt den Zeiger wieder vorwärts.
- Eine neue Änderung nach Undo erzeugt einen neuen aktiven Zweig. Der alte Redo-Zweig bleibt als abgelöste Historie erhalten, ist aber nicht mehr über Redo erreichbar.
- Eine beliebige alte oder abgelöste Version kann gezielt wiederhergestellt werden. Der Restore wird als neue Version protokolliert und enthält `sourceVersionId`.
- Auch ein Reset/Clear wird historisiert und kann per Undo rückgängig gemacht werden.
- Zwei Versionen können rekursiv verglichen werden. Der Bericht nennt konkrete JSON-Pfade und `added`, `removed` oder `changed`.

## Sicherheit

Historienzustände durchlaufen dieselbe Sanitizing-Policy wie Phase 13. Klartextkennwörter, Secrets, Tokens und Upload-Tempdaten werden nicht in Versionsdateien geschrieben. `passwordRef` bleibt als zulässige Referenz erhalten.

Historienaktionen im Admin verwenden einen CSRF-geschützten POST. Restore verlangt zusätzlich eine Benutzerbestätigung im Browser.

Die Historienaktionsdefinitionen sind zentral in `AssistantHistoryActionRegistry` hinterlegt. `buttonKey`, `title` und `ariaLabel` stammen aus dieser Registry; lokale Titel-/ARIA-Überschreibungen sind nicht vorgesehen. Der Asset-Root bleibt `assets/img/`, farbige oder verlaufende CSS-Aktionshintergründe werden nicht eingeführt.

## Backup und Restore

Die Historie liegt im Projektverzeichnis und wird dadurch mit dem Projektbackup gesichert. Beim Restore unter einer neuen Projekt-ID werden Historienindex, Versionsdateien, projektbezogene Scopes, Projektpfade, Prüfsummen und scopeabhängige Historienordner auf die Ziel-ID retargetet.

## Admin-Oberfläche

Für persistente Assistenten erscheint in `run.php` der Link `Verlauf / Undo / Redo`. `history.php` bietet:

- Undo / Redo,
- Liste aller Versionen,
- Kennzeichnung des aktuellen Zustands,
- Kennzeichnung abgelöster Zweige,
- Zustand anzeigen,
- Version mit aktuellem Stand vergleichen,
- beliebige Version kontrolliert wiederherstellen,
- JSON-Bericht über die Historie.

## Schemas

- `easyit.assistant.history.v1`
- `easyit.assistant.history-version.v1`
- `easyit.assistant.history-report.v1`
- `easyit.assistant.history-action-registry.v1`
