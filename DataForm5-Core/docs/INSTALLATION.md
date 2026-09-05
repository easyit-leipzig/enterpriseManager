# Installation

Den Ordner `DataForm5-Core-Database-Foundation` als neuen DataForm-5-Kernordner verwenden oder dessen Inhalte in den Root eines neuen DataForm-5-Stands kopieren.

1. Schreibrechte für `storage/` setzen.
2. `config/database.php` anpassen.
3. `system/database/autoload.php` früh im Bootstrap laden.
4. Test ausführen: `php system/database/tests/run.php`.

Benötigte PDO-Treiber hängen von den aktivierten Adaptern ab: `pdo_mysql`, `pdo_sqlite`, `pdo_oci`.
