# RC1.8-FC1-HF37 – Beziehungseditor & physische Fremdschlüsselvalidierung

HF37 korrigiert den Beziehungsdesigner nach dem konsolidierten Stand HF20–HF36.

## 1. Bestehende Beziehungen sind bearbeitbar

Jede Zeile unter „Vorhandene Beziehungen“ besitzt jetzt zusätzlich die CRUD-Aktion **Bearbeiten**. Der bestehende Beziehungsdatensatz wird geladen und kann geändert werden:

- Name
- Typ (`1:n` / `n:m`)
- Eltern-DataForm / Seite A
- Kind-DataForm / Seite B
- Eltern-Anzeigefeld
- Fremdschlüsselfeld im Kind
- Pflichtstatus

Beim Speichern wird der vorhandene Datensatz aktualisiert; es wird keine zweite Beziehung angelegt.

## 2. Keine Phantom-Fremdschlüsselfelder mehr

Bei tabellengebundenen DataForms werden im Beziehungsdesigner nur Felder angeboten, deren physische Spalte in der gebundenen Projekttabelle tatsächlich existiert.

Damit ist eine Konfiguration wie

```text
ed_events.id -> ed_ev_info.to_basistabelle_events_id
```

ungültig, wenn `to_basistabelle_events_id` in `ed_ev_info` nicht vorhanden ist.

Ein vorhandenes reales Feld, z. B.

```text
ed_events.id -> ed_ev_info.to_ev_id
```

kann direkt gewählt werden.

## 3. Reparatur bestehender fehlerhafter 1:n-Beziehungen

Auch Beziehungen, die bereits die HF33-Semantik `parent-child-v2` tragen, werden erneut gegen den realen Datenspeicher geprüft.

- Existiert das bisher zugeordnete Feld nur als DataForm-Metadatum, aber nicht als physische Kindspalte, ist die Beziehung fehlerhaft.
- Gibt es im Kind genau ein eindeutig passendes reales `*_id`-/`to_*_id`-Feld, wird die Beziehung automatisch darauf umgebunden.
- Gibt es kein eindeutiges Feld, wird die Beziehung vorsorglich deaktiviert und im Designer als **fehlerhaft** gekennzeichnet.
- Wird eine auf diese Weise deaktivierte Beziehung anschließend korrekt bearbeitet, wird sie beim Speichern wieder aktiviert.

## 4. Neue Fremdschlüsselfelder nur noch ausdrücklich

Die bisherige implizite Vorgabe „Bitte wählen oder automatisch erzeugen“ wurde entfernt.

Standard:

```text
Bitte vorhandenes Feld wählen
```

Nur über die ausdrückliche Option **„Neues Fremdschlüsselfeld im Kind anlegen“** wird ein neues Feld erzeugt. Bei einem tabellengebundenen Kind-DataForm wird dabei auch die physische Spalte (`BIGINT UNSIGNED NULL`) in der Kindtabelle angelegt und anschließend an das DataForm-Feld gebunden.

## 5. Live-Semantik

Der blaue Semantikhinweis zeigt die tatsächlich gewählte Zuordnung live an, beispielsweise:

```text
ed_events.id -> Ed Ev Info.to_ev_id
```

## 6. CRUD-UI

Die neue Bearbeiten-Aktion verwendet das globale HF35-3D-CRUD-Design (`data-crud="edit"`). Aktivieren/Deaktivieren und Löschen bleiben erhalten.

## Regressionstest

`tests_rc18_phase6_dataform_hf37_relation_editor_physical_fk.php`

Prüft unter anderem:

- Bearbeiten bestehender Beziehungen
- physische Feldvalidierung
- Ausschluss von Phantomfeldern
- automatische eindeutige Reparatur
- sichere Deaktivierung mehrdeutiger Altbeziehungen
- explizite Feldanlage
- reale `ALTER TABLE`-Spaltenerzeugung
- 3D-Bearbeiten-Aktion
- Live-Anzeige der Eltern→Kind-Zuordnung
