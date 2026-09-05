<?php
declare(strict_types=1);

/**
 * Project deletion backup helper (HF72).
 *
 * Creates a session-protected ZIP archive containing the project registration
 * metadata and a restorable SQL dump of the physical project database.
 */

function enterprise_project_backup_root(): string
{
    return dirname(__DIR__, 2) . '/storage/project-delete-backups';
}

function enterprise_project_backup_ensure_root(): string
{
    $root = enterprise_project_backup_root();
    if (!is_dir($root) && !mkdir($root, 0770, true) && !is_dir($root)) {
        throw new RuntimeException('Das Sicherungsverzeichnis konnte nicht angelegt werden.');
    }

    $denyFile = $root . '/.htaccess';
    if (!is_file($denyFile)) {
        @file_put_contents($denyFile, "Require all denied\nDeny from all\n");
    }
    $indexFile = $root . '/index.html';
    if (!is_file($indexFile)) {
        @file_put_contents($indexFile, "<!doctype html><title>403</title>\n");
    }

    return $root;
}

function enterprise_project_backup_cleanup(int $maxAgeSeconds = 86400): void
{
    $root = enterprise_project_backup_ensure_root();
    $cutoff = time() - max(3600, $maxAgeSeconds);
    foreach (glob($root . '/*.zip') ?: [] as $file) {
        if (is_file($file) && (int)@filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }
}

function enterprise_project_backup_slug(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'projekt';
    }
    $ascii = function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) : $value;
    if (is_string($ascii) && $ascii !== '') {
        $value = $ascii;
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9._-]+/', '-', $value) ?? 'projekt';
    $value = trim($value, '-._');
    return $value !== '' ? substr($value, 0, 80) : 'projekt';
}

function enterprise_project_backup_quote_identifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function enterprise_project_backup_server(array $env): PDO
{
    foreach (['PROJECT_DB_HOST', 'PROJECT_DB_PORT', 'PROJECT_DB_USERNAME'] as $key) {
        if (trim((string)($env[$key] ?? '')) === '') {
            throw new RuntimeException("Projekt-DB-Konfiguration {$key} fehlt. Die Sicherung wurde nicht erstellt.");
        }
    }

    return new PDO(
        'mysql:host=' . $env['PROJECT_DB_HOST'] . ';port=' . (int)$env['PROJECT_DB_PORT'] . ';charset=utf8mb4',
        (string)$env['PROJECT_DB_USERNAME'],
        (string)($env['PROJECT_DB_PASSWORD'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function enterprise_project_backup_database_exists(PDO $server, string $database): bool
{
    $stmt = $server->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$database]);
    return $stmt->fetchColumn() !== false;
}

function enterprise_project_backup_database_pdo(array $env, string $database): PDO
{
    return new PDO(
        'mysql:host=' . $env['PROJECT_DB_HOST'] . ';port=' . (int)$env['PROJECT_DB_PORT'] . ';dbname=' . $database . ';charset=utf8mb4',
        (string)$env['PROJECT_DB_USERNAME'],
        (string)($env['PROJECT_DB_PASSWORD'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function enterprise_project_backup_sql_value(PDO $pdo, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }

    $string = (string)$value;
    // Preserve binary/BLOB values without injecting raw NUL bytes into the dump.
    if (str_contains($string, "\0") || preg_match('//u', $string) !== 1) {
        return '0x' . bin2hex($string);
    }
    $quoted = $pdo->quote($string);
    if ($quoted === false) {
        throw new RuntimeException('Ein Datenbankwert konnte für das SQL-Backup nicht maskiert werden.');
    }
    return $quoted;
}

/** @return array{tables:int,views:int,rows:int,triggers:int} */
function enterprise_project_backup_dump_database(PDO $db, string $database, string $sqlFile): array
{
    $handle = fopen($sqlFile, 'wb');
    if ($handle === false) {
        throw new RuntimeException('Die temporäre SQL-Sicherungsdatei konnte nicht angelegt werden.');
    }

    $stats = ['tables' => 0, 'views' => 0, 'rows' => 0, 'triggers' => 0];
    $tables = [];
    $views = [];

    try {
        fwrite($handle, "-- easyIT Enterprise project backup\n");
        fwrite($handle, '-- Database: ' . $database . "\n");
        fwrite($handle, '-- Generated: ' . gmdate('c') . "\n\n");
        $schemaMetaStmt = $db->prepare('SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
        $schemaMetaStmt->execute([$database]);
        $schemaMeta = $schemaMetaStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $charset = preg_match('/^[A-Za-z0-9_]+$/', (string)($schemaMeta['DEFAULT_CHARACTER_SET_NAME'] ?? '')) === 1
            ? (string)$schemaMeta['DEFAULT_CHARACTER_SET_NAME'] : 'utf8mb4';
        $collation = preg_match('/^[A-Za-z0-9_]+$/', (string)($schemaMeta['DEFAULT_COLLATION_NAME'] ?? '')) === 1
            ? (string)$schemaMeta['DEFAULT_COLLATION_NAME'] : 'utf8mb4_unicode_ci';

        fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");
        fwrite($handle, 'CREATE DATABASE IF NOT EXISTS ' . enterprise_project_backup_quote_identifier($database) . ' CHARACTER SET ' . $charset . ' COLLATE ' . $collation . ";\n");
        fwrite($handle, 'USE ' . enterprise_project_backup_quote_identifier($database) . ";\n\n");

        $result = $db->query('SHOW FULL TABLES');
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $name = (string)($row[0] ?? '');
            $type = strtoupper((string)($row[1] ?? 'BASE TABLE'));
            if ($name === '') {
                continue;
            }
            if ($type === 'VIEW') {
                $views[] = $name;
            } else {
                $tables[] = $name;
            }
        }

        foreach ($tables as $table) {
            $qid = enterprise_project_backup_quote_identifier($table);
            $create = $db->query('SHOW CREATE TABLE ' . $qid)->fetch(PDO::FETCH_NUM);
            $ddl = (string)($create[1] ?? '');
            if ($ddl === '') {
                throw new RuntimeException('Tabellendefinition für `' . $table . '` konnte nicht gelesen werden.');
            }
            fwrite($handle, "-- Table {$table}\nDROP TABLE IF EXISTS {$qid};\n{$ddl};\n\n");
            $stats['tables']++;
        }

        foreach ($tables as $table) {
            $qid = enterprise_project_backup_quote_identifier($table);
            $columnRows = $db->query('SHOW COLUMNS FROM ' . $qid)->fetchAll();
            $columns = array_values(array_filter(array_map(static fn(array $row): string => (string)($row['Field'] ?? ''), $columnRows)));
            if ($columns === []) {
                continue;
            }
            $columnSql = implode(', ', array_map('enterprise_project_backup_quote_identifier', $columns));
            $rows = $db->query('SELECT * FROM ' . $qid);
            $batch = [];
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = enterprise_project_backup_sql_value($db, $row[$column] ?? null);
                }
                $batch[] = '(' . implode(', ', $values) . ')';
                $stats['rows']++;
                if (count($batch) >= 100) {
                    fwrite($handle, 'INSERT INTO ' . $qid . ' (' . $columnSql . ") VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch !== []) {
                fwrite($handle, 'INSERT INTO ' . $qid . ' (' . $columnSql . ") VALUES\n" . implode(",\n", $batch) . ";\n");
            }
            fwrite($handle, "\n");
        }

        // Views are created after base tables/data so their dependencies exist.
        foreach ($views as $view) {
            $qid = enterprise_project_backup_quote_identifier($view);
            $create = $db->query('SHOW CREATE VIEW ' . $qid)->fetch(PDO::FETCH_ASSOC);
            $ddl = (string)($create['Create View'] ?? $create['Create view'] ?? '');
            if ($ddl === '') {
                continue;
            }
            fwrite($handle, "-- View {$view}\nDROP VIEW IF EXISTS {$qid};\n{$ddl};\n\n");
            $stats['views']++;
        }

        // Triggers are included as a separate DELIMITER block where available.
        try {
            $triggers = $db->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($triggers as $triggerRow) {
                $trigger = (string)($triggerRow['Trigger'] ?? '');
                if ($trigger === '') {
                    continue;
                }
                $create = $db->query('SHOW CREATE TRIGGER ' . enterprise_project_backup_quote_identifier($trigger))->fetch(PDO::FETCH_ASSOC);
                $ddl = (string)($create['SQL Original Statement'] ?? $create['Create Trigger'] ?? '');
                if ($ddl === '') {
                    continue;
                }
                fwrite($handle, "DELIMITER $$\nDROP TRIGGER IF EXISTS " . enterprise_project_backup_quote_identifier($trigger) . "$$\n{$ddl}$$\nDELIMITER ;\n\n");
                $stats['triggers']++;
            }
        } catch (Throwable) {
            // Some restricted DB users cannot inspect triggers. Tables and data remain restorable.
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    } finally {
        fclose($handle);
    }

    return $stats;
}

/**
 * @return array{path:string,filename:string,sha256:string,size:int,token:string,expires_at:int,stats:array{tables:int,views:int,rows:int,triggers:int}}
 */
function enterprise_project_backup_create(array $env, array $project, int $userId): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('Für die Projektsicherung wird die PHP-Erweiterung ext-zip / ZipArchive benötigt. Es wurde nichts gelöscht.');
    }

    $database = trim((string)($project['database_name'] ?? ''));
    if (preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/', $database) !== 1) {
        throw new RuntimeException('Der hinterlegte Projektdatenbankname ist ungültig. Die Sicherung wurde nicht erstellt.');
    }
    if (strtolower((string)($project['database_driver'] ?? 'mysql')) !== 'mysql') {
        throw new RuntimeException('Die automatische Projektsicherung ist derzeit nur für MySQL/MariaDB-Projektdatenbanken freigegeben.');
    }

    enterprise_project_backup_cleanup();
    $root = enterprise_project_backup_ensure_root();
    $server = enterprise_project_backup_server($env);
    if (!enterprise_project_backup_database_exists($server, $database)) {
        throw new RuntimeException('Die Projektdatenbank `' . $database . '` wurde nicht gefunden. Eine vollständige Sicherung kann deshalb nicht erstellt werden; es wurde nichts gelöscht.');
    }

    $db = enterprise_project_backup_database_pdo($env, $database);
    $tmpDir = $root . '/tmp-' . bin2hex(random_bytes(10));
    if (!mkdir($tmpDir, 0770, true) && !is_dir($tmpDir)) {
        throw new RuntimeException('Das temporäre Sicherungsverzeichnis konnte nicht angelegt werden.');
    }

    $timestamp = gmdate('Ymd_His');
    $baseName = enterprise_project_backup_slug((string)($project['name'] ?? 'projekt')) . '-backup-' . $timestamp;
    $filename = $baseName . '.zip';
    $path = $root . '/' . $filename;
    if (is_file($path)) {
        $filename = $baseName . '-' . bin2hex(random_bytes(3)) . '.zip';
        $path = $root . '/' . $filename;
    }

    try {
        $sqlFile = $tmpDir . '/database.sql';
        $snapshotStarted = false;
        try {
            $db->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $db->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
            $snapshotStarted = true;
            $stats = enterprise_project_backup_dump_database($db, $database, $sqlFile);
            $db->exec('COMMIT');
            $snapshotStarted = false;
        } catch (Throwable $dumpError) {
            if ($snapshotStarted) {
                try { $db->exec('ROLLBACK'); } catch (Throwable) {}
            }
            throw $dumpError;
        }

        $projectMeta = $project;
        $projectMeta['backup_created_at'] = gmdate('c');
        $projectMeta['backup_created_by_user_id'] = $userId;
        file_put_contents($tmpDir . '/project.json', json_encode($projectMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $manifest = [
            'format' => 'easyit-project-backup',
            'format_version' => 1,
            'created_at' => gmdate('c'),
            'project' => [
                'id' => (int)($project['id'] ?? 0),
                'name' => (string)($project['name'] ?? ''),
                'slug' => (string)($project['slug'] ?? ''),
                'product_type' => (string)($project['product_type'] ?? ''),
                'database_driver' => (string)($project['database_driver'] ?? 'mysql'),
                'database_name' => $database,
            ],
            'database' => $stats,
            'files' => [
                'project.json',
                'database.sql',
                'README_RESTORE.txt',
            ],
        ];

        $readme = "easyIT Enterprise – Projektsicherung\n"
            . "======================================\n\n"
            . 'Projekt: ' . (string)($project['name'] ?? '') . "\n"
            . 'Datenbank: ' . $database . "\n"
            . 'Erstellt: ' . gmdate('c') . "\n\n"
            . "Inhalt:\n"
            . "- project.json: Enterprise-Projektmetadaten\n"
            . "- database.sql: physisches Projektdatenbankschema und Datensätze\n"
            . "- manifest.json: Sicherungsmanifest und Statistik\n\n"
            . "Wiederherstellung:\n"
            . "1. Im Enterprise Manager „Projekte → Projektsicherung wiederherstellen“ öffnen.\n"
            . "2. Dieses ZIP hochladen und prüfen.\n"
            . "3. Projektname, Slug und Datenbankstrategie bestätigen und den Restore starten.\n"
            . "Alternativ kann database.sql weiterhin manuell eingespielt und project.json zur Registrierung verwendet werden.\n\n"
            . "Hinweis: Das globale easyIT-Programm selbst ist nicht Bestandteil dieser Projektsicherung.\n";
        file_put_contents($tmpDir . '/README_RESTORE.txt', $readme);
        file_put_contents($tmpDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Das ZIP-Sicherungsarchiv konnte nicht erzeugt werden.');
        }
        foreach (['manifest.json', 'project.json', 'database.sql', 'README_RESTORE.txt'] as $file) {
            if (!$zip->addFile($tmpDir . '/' . $file, $file)) {
                $zip->close();
                throw new RuntimeException('Die Datei `' . $file . '` konnte nicht in das Sicherungsarchiv aufgenommen werden.');
            }
        }
        if (!$zip->close()) {
            throw new RuntimeException('Das ZIP-Sicherungsarchiv konnte nicht abgeschlossen werden.');
        }
        if (!is_file($path) || (int)filesize($path) <= 0) {
            throw new RuntimeException('Das erzeugte Sicherungsarchiv ist leer oder nicht vorhanden.');
        }

        $sha256 = hash_file('sha256', $path);
        if (!is_string($sha256) || strlen($sha256) !== 64) {
            throw new RuntimeException('Die Integritätsprüfung des Sicherungsarchivs ist fehlgeschlagen.');
        }

        $token = bin2hex(random_bytes(24));
        $expiresAt = time() + 86400;
        $_SESSION['project_backup_downloads'][$token] = [
            'path' => $path,
            'filename' => $filename,
            'project_id' => (int)($project['id'] ?? 0),
            'created_at' => time(),
            'expires_at' => $expiresAt,
            'sha256' => $sha256,
        ];

        return [
            'path' => $path,
            'filename' => $filename,
            'sha256' => $sha256,
            'size' => (int)filesize($path),
            'token' => $token,
            'expires_at' => $expiresAt,
            'stats' => $stats,
        ];
    } catch (Throwable $e) {
        if (is_file($path)) {
            @unlink($path);
        }
        throw $e;
    } finally {
        foreach (glob($tmpDir . '/*') ?: [] as $tmpFile) {
            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        }
        @rmdir($tmpDir);
    }
}

/** @return array{path:string,filename:string,project_id:int,created_at:int,expires_at:int,sha256:string} */
function enterprise_project_backup_resolve_download(string $token): array
{
    if ($token === '' || preg_match('/^[a-f0-9]{48}$/', $token) !== 1) {
        throw new RuntimeException('Ungültiger Sicherungslink.');
    }
    $entry = $_SESSION['project_backup_downloads'][$token] ?? null;
    if (!is_array($entry)) {
        throw new RuntimeException('Der Sicherungslink ist nicht mehr verfügbar.');
    }
    if ((int)($entry['expires_at'] ?? 0) < time()) {
        unset($_SESSION['project_backup_downloads'][$token]);
        throw new RuntimeException('Der Sicherungslink ist abgelaufen.');
    }

    $root = realpath(enterprise_project_backup_ensure_root());
    $path = realpath((string)($entry['path'] ?? ''));
    if ($root === false || $path === false || !is_file($path) || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
        unset($_SESSION['project_backup_downloads'][$token]);
        throw new RuntimeException('Die Sicherungsdatei wurde nicht gefunden.');
    }

    return [
        'path' => $path,
        'filename' => basename((string)($entry['filename'] ?? basename($path))),
        'project_id' => (int)($entry['project_id'] ?? 0),
        'created_at' => (int)($entry['created_at'] ?? 0),
        'expires_at' => (int)($entry['expires_at'] ?? 0),
        'sha256' => (string)($entry['sha256'] ?? ''),
    ];
}
