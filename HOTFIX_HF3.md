# RC1.8-FC1-HF3

## Korrektur

Der technische Datenbank-Installer `installer/database.php` hat MySQL/MariaDB-DDL-Migrationen in eine PDO-Transaktion gekapselt. MySQL/MariaDB führt bei DDL (z. B. `CREATE TABLE`) implizite Commits aus. Der anschließende Aufruf von `PDO::commit()` führte deshalb zu `There is no active transaction`.

HF3 entfernt den ungeeigneten Transaktionswrapper aus dem Schema-Runner. Eine Migration wird erst nach erfolgreichem Ausführen des Schema-Callables mit SHA-256 in `migrations` registriert.

Hinweis: DDL-Rollback kann unter MySQL/MariaDB nicht pauschal als transaktionale Garantie behandelt werden.
