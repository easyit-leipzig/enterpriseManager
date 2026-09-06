# Assistant Phase 22 – Modul-Migrationen

Phase 22 ergänzt den Modul-Updatepfad um deklarative, versionierte Migrationen.

## Assistent

`dataform.module-migrations`

Ablauf:
1. Zielprojekt wählen.
2. Updates, Kaskade und bestehende Mappings festlegen.
3. Migrationen vollständig im Dry-Run simulieren.
4. Recovery-Backup erzeugen und den geprüften Plan ausführen.
5. Ergebnis, Migrationsregister und Rollback-Checkpoint anzeigen.

## Release-Schema

Migrationen liegen in `module/release.json` unter `migrations`.

Beispiel:

```json
{
  "schema": "easyit.dataform.module-release.v1",
  "version": "2.0.0",
  "dependencies": [],
  "migrations": [
    {
      "id": "add-status-column",
      "from": "^1.0.0",
      "to": "2.0.0",
      "order": 10,
      "phase": "pre",
      "type": "csv.add_column",
      "destructive": false,
      "dataForm": "base_df",
      "payload": {
        "source": "base_table",
        "column": "status",
        "default": "new"
      }
    }
  ]
}
```

## Unterstützte Migrationstypen

- `config.set`
- `config.unset`
- `field.add`
- `field.rename`
- `field.remove`
- `csv.create_table`
- `csv.add_column`
- `csv.rename_column`
- `csv.remove_column`
- `csv.map_values`
- `database.sql`

`field.remove` und `csv.remove_column` müssen ausdrücklich `destructive=true` deklarieren. Destruktives SQL wird ebenfalls nur bei gesetztem Destruktiv-Flag akzeptiert.

## Pre/Post

`phase=pre` läuft vor dem neuen Modulpaket. `phase=post` läuft nach erfolgreichem Modulupgrade. Innerhalb einer Phase entscheidet `order` über die Reihenfolge.

## Dry-Run

Vor jeder Änderung werden geprüft:
- Versionsbereich `from` und `to`
- Ziel-DataForm nach Mapping
- vorhandene bzw. fehlende Felder
- CSV-Tabellen und Spalten
- Datenquellentreiber
- SQL-PDO-Treiber
- Destruktivkennzeichnung
- bereits ausgeführte Migrationen
- kompletter Phase-21-Abhängigkeits-/Upgradeplan

## Rollback

Vor dem ersten Schreibzugriff wird der vorhandene Phase-10-`ProjectArchiveService` aufgerufen. CSV und lokale SQLite-Daten liegen im Projektbackup. SQL-Migrationen gegen externe MySQL-/Oracle-Datenbanken werden blockiert, wenn das erzeugte Backup keinen physischen DB-Dump enthält. Dafür muss `backup.dumpPath` konfiguriert sein.

## Migrationsregister

Ausgeführte Schritte werden projektbezogen gespeichert:

`projects/<projekt>/config/assistant/module-migrations.json`

Schema: `easyit.assistant.module-migrations.v1`

Damit wird ein bereits protokollierter Schritt für dieselbe Modul-Zielversion nicht nochmals ausgeführt.

## Phase-21-Schutz

`ModuleUpdateService` erkennt `migrationCount > 0`. Ein direkter Upgrade-Aufruf wird dann blockiert und auf `dataform.module-migrations` verwiesen. Updates ohne Migrationen bleiben unverändert ausführbar.

## Release-Authoring

Der Phase-20-Release-Assistent besitzt zusätzlich das Feld `Migrationen (JSON-Array; Phase 22)`. Die Paketprüfung validiert Typen, IDs, SemVer-Bedingungen, Phasen und Destruktivflags und schreibt `migrationCount` in die Metadaten.
