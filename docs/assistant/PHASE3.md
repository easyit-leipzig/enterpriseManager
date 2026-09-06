# Assistant-System – Phase 3: Beziehungen und gebundene Formulare

Phase 3 erweitert die kumulative Phase 1/2 um den registrierten Fachassistenten `dataform.relations`.

## Unterstützte Beziehungen

- 1:n
- n:m über eine explizite Zwischentabelle

## 1:n und gebundene Kindformulare

Für gebundene Kindformulare gilt verbindlich:

1. Das übergeordnete DataForm liefert den **aktuellen Datensatz**.
2. Dessen Schlüsselfeld ist standardmäßig `id`.
3. Bei einem neuen Kinddatensatz wird der **aktuelle Wert** dieses Schlüsselfeldes automatisch in das Kind-Fremdschlüsselfeld geschrieben, z. B. `to_ev_id`.
4. Das gebundene Kindfeld ist standardmäßig `readOnly = true`; dies kann im Assistenten abgeschaltet werden.
5. Das Kind-Fremdschlüsselfeld und das gebundene Zielfeld müssen identisch sein.
6. Es wird kein statischer Elternwert in der Konfiguration gespeichert. Die konkrete Eltern-ID wird erst zur Laufzeit aus dem aktuell angezeigten Eltern-Datensatz übernommen.

Beispiel:

- aktueller Eltern-Datensatz: `id = 42`
- Kind-Fremdschlüsselfeld: `to_ev_id`
- neuer Kinddatensatz erhält automatisch: `to_ev_id = 42`

Die Laufzeitauflösung übernimmt `EasyIT\\Assistant\\Relation\\BoundValueResolver`.

## n:m

Der Assistent erfasst:

- Zwischentabelle
- Fremdschlüssel zur Elternseite
- Fremdschlüssel zur Kindseite
- Schlüsselfeld des Kinddatensatzes

## Verbindliche UI-Regeln

- Paginierung des Kind-DataForms bleibt unter den Datensätzen.
- Aktionsbuttons werden weiterhin über die zentrale Button-Registry abgebildet.

## Export

Eine validierte Beziehung kann über `admin/assistants/export.php?assistant=dataform.relations` als JSON exportiert werden.

Schema: `easyit.dataform.relation.assistant.v1`.
