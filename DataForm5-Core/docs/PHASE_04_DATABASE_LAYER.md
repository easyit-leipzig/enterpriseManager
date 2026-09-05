# Phase 04 – Database Layer

Build 0004 integriert die bereits vorbereitete Datenbankschicht vollständig in den DataForm5-Core.

## Verbindliche Struktur

- `system/database/01_interfaces` – gemeinsame Verträge
- `system/database/02_core` – Factory, Manager, Query-, Schema-, Relations- und Migrationsdienste
- `system/database/03_adapters` – CSV, MySQL/MariaDB, SQLite und Oracle
- `system/database/04_extensions` – reservierte Erweiterungspunkte

## Zentrale Verwendung

```php
$kernel = require __DIR__ . '/bootstrap/app.php';
$manager = $kernel->container()->get(\DataForm\Database\Core\DatabaseManager::class);
$db = $manager->connection();
```

Der Produktcode greift nicht direkt auf konkrete Adapter zu. Die Auswahl erfolgt ausschließlich über `config/database.php` beziehungsweise `.env`.

## Mehrere Verbindungen

Die Verbindungen `default`, `admin`, `project`, `sqlite` und `oracle` sind vorbereitet. Admin- und Projektdaten bleiben damit voneinander getrennt.

## CSV-Relationen

Der CSV-Adapter unterstützt CRUD, Transaktionen, 1:n und n:m über Pivot-CSV-Tabellen.
