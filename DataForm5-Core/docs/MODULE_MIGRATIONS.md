# Modulmigrationen

Ab RC1.7.10 / Phase J können Enterprise-Module eigene Datenbankmigrationen ausliefern.

## Verzeichnis

```text
modules/<modul>/database/migrations/
    20260807_001_create_table.php
    20260807_002_add_index.php
```

Eine Migration liefert ein Array mit `up` und optional `down` zurück:

```php
<?php
declare(strict_types=1);

return [
    'up' => static function (PDO $pdo): void {
        $pdo->exec('CREATE TABLE example (...)');
    },
    'down' => static function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS example');
    },
];
```

## Verhalten

- Dateiname ohne `.php` ist die Migrationsversion.
- SHA-256 jeder ausgeführten Migration wird gespeichert.
- Bereits ausgeführte und nachträglich geänderte Migrationen werden blockiert.
- Neue Migrationen eines Moduls werden bei Installation bzw. Update ausgeführt.
- Migrationen werden in Batches protokolliert.
- Bei einem Fehler versucht der Manager die im aktuellen Lauf ausgeführten Migrationen in umgekehrter Reihenfolge zurückzurollen.
- Das Enterprise-Admin zeigt je Modul Anzahl, ausgeführte/ausstehende Migrationen und Integritätsabweichungen.

Die Statusdaten liegen in der Admin-Datenbank in `enterprise_module_migrations`.
