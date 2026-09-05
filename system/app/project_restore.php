<?php
declare(strict_types=1);

/**
 * Project backup restore helper (HF73).
 *
 * Restores ZIP archives created by enterprise_project_backup_create(). The
 * archive is never extracted into the web root; only the known manifest,
 * project metadata and SQL dump are read through ZipArchive.
 */

function enterprise_project_restore_root(): string
{
    return dirname(__DIR__, 2) . '/storage/project-restore-previews';
}

function enterprise_project_restore_ensure_root(): string
{
    $root = enterprise_project_restore_root();
    if (!is_dir($root) && !mkdir($root, 0770, true) && !is_dir($root)) {
        throw new RuntimeException('Das temporäre Restore-Verzeichnis konnte nicht angelegt werden.');
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

function enterprise_project_restore_cleanup(int $maxAgeSeconds = 7200): void
{
    $root = enterprise_project_restore_ensure_root();
    $cutoff = time() - max(1800, $maxAgeSeconds);
    foreach (glob($root . '/*.zip') ?: [] as $file) {
        if (is_file($file) && (int)@filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }

    foreach ((array)($_SESSION['project_restore_previews'] ?? []) as $token => $entry) {
        if (!is_array($entry) || (int)($entry['expires_at'] ?? 0) < time()) {
            unset($_SESSION['project_restore_previews'][$token]);
        }
    }
}

function enterprise_project_restore_valid_database_name(string $name): bool
{
    return preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/', $name) === 1;
}

function enterprise_project_restore_quote_identifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function enterprise_project_restore_server(array $env): PDO
{
    foreach (['PROJECT_DB_HOST', 'PROJECT_DB_PORT', 'PROJECT_DB_USERNAME'] as $key) {
        if (trim((string)($env[$key] ?? '')) === '') {
            throw new RuntimeException("Projekt-DB-Konfiguration {$key} fehlt. Eine Wiederherstellung ist nicht möglich.");
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

function enterprise_project_restore_database_exists(PDO $server, string $database): bool
{
    $stmt = $server->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$database]);
    return $stmt->fetchColumn() !== false;
}

function enterprise_project_restore_assert_target_safe(array $env, string $database): void
{
    if (!enterprise_project_restore_valid_database_name($database)) {
        throw new RuntimeException('Der Ziel-Datenbankname ist ungültig.');
    }

    $protected = ['mysql', 'information_schema', 'performance_schema', 'sys'];
    $adminDatabase = strtolower(trim((string)($env['ADMIN_DB_DATABASE'] ?? '')));
    if ($adminDatabase !== '') {
        $protected[] = $adminDatabase;
    }
    if (in_array(strtolower($database), array_unique($protected), true)) {
        throw new RuntimeException('Die Datenbank `' . $database . '` ist eine geschützte System-/Enterprise-Datenbank und darf nicht als Restore-Ziel verwendet werden.');
    }
}

/** @return array{manifest:array<string,mixed>,project:array<string,mixed>,database_sql:string,sha256:string,size:int,filename:string} */
function enterprise_project_restore_inspect_archive(string $path, string $filename = ''): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('Für den Projekt-Restore wird die PHP-Erweiterung ext-zip / ZipArchive benötigt.');
    }
    if (!is_file($path) || (int)@filesize($path) <= 0) {
        throw new RuntimeException('Die Sicherungsdatei wurde nicht gefunden oder ist leer.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('Die ausgewählte Datei ist kein lesbares ZIP-Sicherungsarchiv.');
    }

    try {
        // Reject suspicious archive paths even though we never extract them.
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string)$zip->getNameIndex($i);
            if ($name === '' || str_contains($name, "\0") || str_starts_with($name, '/') || preg_match('~(^|/)\.\.(/|$)~', str_replace('\\', '/', $name)) === 1) {
                throw new RuntimeException('Das Sicherungsarchiv enthält einen unzulässigen Dateipfad.');
            }
        }

        foreach (['manifest.json', 'project.json', 'database.sql'] as $required) {
            if ($zip->locateName($required, ZipArchive::FL_NOCASE) === false) {
                throw new RuntimeException('Die Projektsicherung ist unvollständig: `' . $required . '` fehlt.');
            }
        }

        $manifestRaw = $zip->getFromName('manifest.json');
        $projectRaw = $zip->getFromName('project.json');
        $databaseSql = $zip->getFromName('database.sql');
        if (!is_string($manifestRaw) || !is_string($projectRaw) || !is_string($databaseSql)) {
            throw new RuntimeException('Die Projektsicherung konnte nicht vollständig gelesen werden.');
        }

        $manifest = json_decode($manifestRaw, true, 64, JSON_THROW_ON_ERROR);
        $project = json_decode($projectRaw, true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || !is_array($project)) {
            throw new RuntimeException('Manifest oder Projektmetadaten sind ungültig.');
        }
        if ((string)($manifest['format'] ?? '') !== 'easyit-project-backup') {
            throw new RuntimeException('Die ZIP-Datei ist keine easyIT-Projektsicherung.');
        }
        if ((int)($manifest['format_version'] ?? 0) !== 1) {
            throw new RuntimeException('Diese Version der Projektsicherung wird nicht unterstützt.');
        }

        $manifestProject = is_array($manifest['project'] ?? null) ? $manifest['project'] : [];
        $database = trim((string)($manifestProject['database_name'] ?? $project['database_name'] ?? ''));
        if (!enterprise_project_restore_valid_database_name($database)) {
            throw new RuntimeException('Die Sicherung enthält keinen gültigen Projektdatenbanknamen.');
        }
        if (strtolower((string)($manifestProject['database_driver'] ?? $project['database_driver'] ?? 'mysql')) !== 'mysql') {
            throw new RuntimeException('Der automatische Restore ist nur für MySQL/MariaDB-Projektsicherungen freigegeben.');
        }
        if (trim($databaseSql) === '') {
            throw new RuntimeException('Der SQL-Dump in der Projektsicherung ist leer.');
        }

        $sha256 = hash_file('sha256', $path);
        if (!is_string($sha256) || strlen($sha256) !== 64) {
            throw new RuntimeException('Die SHA-256-Prüfung der Projektsicherung ist fehlgeschlagen.');
        }

        return [
            'manifest' => $manifest,
            'project' => $project,
            'database_sql' => $databaseSql,
            'sha256' => $sha256,
            'size' => (int)filesize($path),
            'filename' => $filename !== '' ? basename($filename) : basename($path),
        ];
    } catch (JsonException $e) {
        throw new RuntimeException('Manifest oder Projektmetadaten enthalten ungültiges JSON.', 0, $e);
    } finally {
        $zip->close();
    }
}

/** @return array{token:string,path:string,filename:string,expires_at:int,preview:array<string,mixed>} */
function enterprise_project_restore_stage_upload(array $file): array
{
    enterprise_project_restore_cleanup();

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Die Sicherungsdatei überschreitet upload_max_filesize.',
            UPLOAD_ERR_FORM_SIZE => 'Die Sicherungsdatei überschreitet die zulässige Formulargröße.',
            UPLOAD_ERR_PARTIAL => 'Die Sicherungsdatei wurde nur teilweise hochgeladen.',
            UPLOAD_ERR_NO_FILE => 'Bitte wählen Sie eine Projekt-ZIP-Sicherung aus.',
        ];
        throw new RuntimeException($messages[$error] ?? 'Die Sicherungsdatei konnte nicht hochgeladen werden.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Die hochgeladene Sicherungsdatei konnte nicht verifiziert werden.');
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > 1024 * 1024 * 1024) {
        throw new RuntimeException('Die Sicherungsdatei ist leer oder größer als 1 GiB.');
    }

    $root = enterprise_project_restore_ensure_root();
    $token = bin2hex(random_bytes(24));
    $path = $root . '/' . $token . '.zip';
    if (!move_uploaded_file($tmp, $path)) {
        throw new RuntimeException('Die Sicherungsdatei konnte nicht in den geschützten Restore-Bereich übernommen werden.');
    }
    @chmod($path, 0660);

    try {
        $preview = enterprise_project_restore_inspect_archive($path, (string)($file['name'] ?? 'projekt-backup.zip'));
    } catch (Throwable $e) {
        @unlink($path);
        throw $e;
    }

    $expiresAt = time() + 7200;
    $_SESSION['project_restore_previews'][$token] = [
        'path' => $path,
        'filename' => $preview['filename'],
        'created_at' => time(),
        'expires_at' => $expiresAt,
        'sha256' => $preview['sha256'],
    ];

    return [
        'token' => $token,
        'path' => $path,
        'filename' => $preview['filename'],
        'expires_at' => $expiresAt,
        'preview' => $preview,
    ];
}

/** @return array{path:string,filename:string,expires_at:int,preview:array<string,mixed>} */
function enterprise_project_restore_resolve_preview(string $token): array
{
    enterprise_project_restore_cleanup();
    if ($token === '' || preg_match('/^[a-f0-9]{48}$/', $token) !== 1) {
        throw new RuntimeException('Ungültige Restore-Vorschau.');
    }

    $entry = $_SESSION['project_restore_previews'][$token] ?? null;
    if (!is_array($entry) || (int)($entry['expires_at'] ?? 0) < time()) {
        unset($_SESSION['project_restore_previews'][$token]);
        throw new RuntimeException('Die Restore-Vorschau ist abgelaufen. Bitte laden Sie die Sicherung erneut hoch.');
    }

    $root = realpath(enterprise_project_restore_ensure_root());
    $path = realpath((string)($entry['path'] ?? ''));
    if ($root === false || $path === false || !is_file($path) || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
        unset($_SESSION['project_restore_previews'][$token]);
        throw new RuntimeException('Die temporäre Restore-Datei wurde nicht gefunden. Bitte laden Sie die Sicherung erneut hoch.');
    }

    $preview = enterprise_project_restore_inspect_archive($path, (string)($entry['filename'] ?? basename($path)));
    if (!hash_equals((string)($entry['sha256'] ?? ''), (string)$preview['sha256'])) {
        unset($_SESSION['project_restore_previews'][$token]);
        throw new RuntimeException('Die temporäre Restore-Datei hat sich seit der Prüfung verändert.');
    }

    return [
        'path' => $path,
        'filename' => (string)$preview['filename'],
        'expires_at' => (int)$entry['expires_at'],
        'preview' => $preview,
    ];
}

function enterprise_project_restore_rewrite_database_sql(string $sql, string $sourceDatabase, string $targetDatabase): string
{
    if (!enterprise_project_restore_valid_database_name($sourceDatabase) || !enterprise_project_restore_valid_database_name($targetDatabase)) {
        throw new RuntimeException('Quell- oder Ziel-Datenbankname ist für den Restore ungültig.');
    }
    if ($sourceDatabase === $targetDatabase) {
        return $sql;
    }

    // The HF72 dump writes database references as quoted identifiers. Replacing
    // the exact quoted identifier also updates qualified view/trigger references
    // while leaving ordinary data values untouched.
    return str_replace(
        enterprise_project_restore_quote_identifier($sourceDatabase),
        enterprise_project_restore_quote_identifier($targetDatabase),
        $sql
    );
}

/**
 * Execute an SQL dump with support for MySQL client DELIMITER directives.
 * The parser only splits delimiters outside strings, quoted identifiers and comments.
 */
function enterprise_project_restore_execute_sql(PDO $server, string $sql): int
{
    $delimiter = ';';
    $buffer = '';
    $statementCount = 0;
    $length = strlen($sql);
    $i = 0;
    $quote = null;
    $lineComment = false;
    $blockComment = false;
    $lineStart = true;

    $flush = static function (string $statement) use ($server, &$statementCount): void {
        $statement = trim($statement);
        if ($statement === '') {
            return;
        }
        $server->exec($statement);
        $statementCount++;
    };

    while ($i < $length) {
        // DELIMITER is a mysql-client directive and appears on a standalone line
        // in easyIT backups. Recognize it only at a clean statement boundary.
        if ($lineStart && $quote === null && !$blockComment && trim($buffer) === '') {
            $lineEnd = strpos($sql, "\n", $i);
            if ($lineEnd === false) {
                $lineEnd = $length;
            }
            $line = substr($sql, $i, $lineEnd - $i);
            if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $m) === 1) {
                $delimiter = (string)$m[1];
                if ($delimiter === '') {
                    throw new RuntimeException('Ungültige DELIMITER-Anweisung im SQL-Backup.');
                }
                $i = $lineEnd < $length ? $lineEnd + 1 : $lineEnd;
                $lineStart = true;
                continue;
            }
        }

        $ch = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if ($lineComment) {
            $buffer .= $ch;
            if ($ch === "\n") {
                $lineComment = false;
                $lineStart = true;
            } else {
                $lineStart = false;
            }
            $i++;
            continue;
        }

        if ($blockComment) {
            $buffer .= $ch;
            if ($ch === '*' && $next === '/') {
                $buffer .= '/';
                $i += 2;
                $blockComment = false;
                $lineStart = false;
                continue;
            }
            $lineStart = $ch === "\n";
            $i++;
            continue;
        }

        if ($quote !== null) {
            $buffer .= $ch;
            if ($ch === '\\' && ($quote === "'" || $quote === '"') && $i + 1 < $length) {
                $buffer .= $sql[$i + 1];
                $i += 2;
                $lineStart = false;
                continue;
            }
            if ($ch === $quote) {
                // SQL permits doubled quote/backtick escaping.
                if ($next === $quote) {
                    $buffer .= $next;
                    $i += 2;
                    $lineStart = false;
                    continue;
                }
                $quote = null;
            }
            $lineStart = $ch === "\n";
            $i++;
            continue;
        }

        if (($ch === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) || $ch === '#') {
            $lineComment = true;
            $buffer .= $ch;
            if ($ch === '-') {
                $buffer .= $next;
                $i += 2;
            } else {
                $i++;
            }
            $lineStart = false;
            continue;
        }
        if ($ch === '/' && $next === '*') {
            $blockComment = true;
            $buffer .= '/*';
            $i += 2;
            $lineStart = false;
            continue;
        }
        if ($ch === "'" || $ch === '"' || $ch === '`') {
            $quote = $ch;
            $buffer .= $ch;
            $lineStart = false;
            $i++;
            continue;
        }

        if ($delimiter !== '' && substr($sql, $i, strlen($delimiter)) === $delimiter) {
            $flush($buffer);
            $buffer = '';
            $i += strlen($delimiter);
            $lineStart = false;
            continue;
        }

        $buffer .= $ch;
        $lineStart = $ch === "\n";
        $i++;
    }

    if ($quote !== null || $blockComment) {
        throw new RuntimeException('Der SQL-Dump endet innerhalb eines nicht abgeschlossenen Strings oder Kommentars.');
    }
    $flush($buffer);

    return $statementCount;
}

/**
 * @return array{database_existed_before:bool,database_restored:bool,database_reused:bool,statements:int,strategy:string}
 */
function enterprise_project_restore_database(array $env, string $sourceDatabase, string $targetDatabase, string $databaseSql, string $strategy, string $replaceConfirmation = ''): array
{
    enterprise_project_restore_assert_target_safe($env, $targetDatabase);
    $server = enterprise_project_restore_server($env);
    $exists = enterprise_project_restore_database_exists($server, $targetDatabase);
    $strategy = strtolower(trim($strategy));
    if (!in_array($strategy, ['auto', 'reuse', 'restore', 'replace'], true)) {
        throw new RuntimeException('Ungültige Restore-Strategie.');
    }

    if ($strategy === 'auto') {
        if ($exists && $targetDatabase !== $sourceDatabase) {
            throw new RuntimeException('Die geänderte Ziel-Datenbank `' . $targetDatabase . '` existiert bereits. Wählen Sie ausdrücklich „Vorhandene Datenbank verwenden“ oder „ersetzen“.');
        }
        $strategy = $exists ? 'reuse' : 'restore';
    }

    if ($strategy === 'reuse') {
        if (!$exists) {
            throw new RuntimeException('Die Ziel-Datenbank `' . $targetDatabase . '` existiert nicht und kann deshalb nicht weiterverwendet werden.');
        }
        return [
            'database_existed_before' => true,
            'database_restored' => false,
            'database_reused' => true,
            'statements' => 0,
            'strategy' => 'reuse',
        ];
    }

    if ($strategy === 'restore' && $exists) {
        throw new RuntimeException('Die Ziel-Datenbank `' . $targetDatabase . '` existiert bereits. Wählen Sie „Vorhandene Datenbank verwenden“ oder – nach Prüfung – „ersetzen“.');
    }

    if ($strategy === 'replace') {
        if (!$exists) {
            $strategy = 'restore';
        } else {
            if (!hash_equals($targetDatabase, trim($replaceConfirmation))) {
                throw new RuntimeException('Zum Ersetzen einer vorhandenen Datenbank muss deren Name exakt bestätigt werden.');
            }
            $server->exec('DROP DATABASE ' . enterprise_project_restore_quote_identifier($targetDatabase));
            if (enterprise_project_restore_database_exists($server, $targetDatabase)) {
                throw new RuntimeException('Die vorhandene Ziel-Datenbank konnte nicht entfernt werden.');
            }
        }
    }

    $rewrittenSql = enterprise_project_restore_rewrite_database_sql($databaseSql, $sourceDatabase, $targetDatabase);
    try {
        @set_time_limit(0);
        $statements = enterprise_project_restore_execute_sql($server, $rewrittenSql);
        if (!enterprise_project_restore_database_exists($server, $targetDatabase)) {
            throw new RuntimeException('Die Ziel-Datenbank wurde nach dem SQL-Restore nicht gefunden.');
        }
    } catch (Throwable $e) {
        // If this was a brand-new target, remove the partial database. For a
        // replace operation the previous database cannot be reconstructed here;
        // keep any partial restored state for diagnosis rather than deleting it.
        if (!$exists) {
            try {
                $server->exec('DROP DATABASE IF EXISTS ' . enterprise_project_restore_quote_identifier($targetDatabase));
            } catch (Throwable) {
            }
        }
        throw new RuntimeException('Die Projektdatenbank konnte nicht aus der Sicherung wiederhergestellt werden: ' . $e->getMessage(), 0, $e);
    }

    return [
        'database_existed_before' => $exists,
        'database_restored' => true,
        'database_reused' => false,
        'statements' => $statements,
        'strategy' => $strategy === 'replace' ? 'replace' : 'restore',
    ];
}
