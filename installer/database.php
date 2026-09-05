<?php
declare(strict_types=1);

session_start();
require dirname(__DIR__) . '/system/ui/layout.php';

$rootPath = dirname(__DIR__);
$envPath = $rootPath . '/DataForm5-Core/.env';
$messages = [];
$results = [];
$diagnostics = [];

if (!isset($_SESSION['easyit_csrf'])) {
    $_SESSION['easyit_csrf'] = bin2hex(random_bytes(32));
}

function db_e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function validDatabaseName(string $name): bool { return preg_match('/^[A-Za-z][A-Za-z0-9_]{1,62}$/', $name) === 1; }
function quoteIdentifier(string $name): string { return '`' . str_replace('`', '``', $name) . '`'; }
function pdoServer(string $host, int $port, string $user, string $password): PDO
{
    return new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 8,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
function envValues(string $path): array
{
    $values = [];
    if (!is_file($path)) return $values;
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $values[trim($key)] = trim(trim($value), "\"'");
    }
    return $values;
}
function updateEnv(string $path, array $updates): void
{
    if (!is_file($path)) {
        $example = dirname($path) . '/.env.example';
        if (!is_file($example) || !is_readable($example)) {
            throw new RuntimeException('DataForm5-Core/.env fehlt und .env.example ist nicht verfügbar.');
        }
        $dir = dirname($path);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException('DataForm5-Core ist nicht beschreibbar; .env kann nicht neu erzeugt werden.');
        }
        if (!copy($example, $path)) {
            throw new RuntimeException('DataForm5-Core/.env konnte aus .env.example nicht neu erzeugt werden.');
        }
        @chmod($path, 0600);
    }
    if (!is_readable($path) || !is_writable($path)) {
        throw new RuntimeException('DataForm5-Core/.env ist nicht lesbar oder nicht beschreibbar.');
    }
    $content = (string)file_get_contents($path);
    foreach ($updates as $key => $value) {
        $safe = str_replace(["\r", "\n"], '', (string)$value);
        $line = $key . '=' . $safe;
        if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $content)) {
            $content = preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $line, $content) ?? $content;
        } else {
            $content .= PHP_EOL . $line;
        }
    }
    if (file_put_contents($path, $content, LOCK_EX) === false) {
        throw new RuntimeException('Die .env-Datei konnte nicht aktualisiert werden.');
    }
}
function ensureDataFormSecretKey(string $envPath): array
{
    $values = envValues($envPath);

    if (trim((string)($values['DATAFORM_APP_KEY'] ?? '')) !== '') {
        return ['created'=>false,'source'=>'DATAFORM_APP_KEY'];
    }

    if (trim((string)($values['APP_KEY'] ?? '')) !== '') {
        return ['created'=>false,'source'=>'APP_KEY'];
    }

    $key = 'dfk1_' . rtrim(
        strtr(base64_encode(random_bytes(32)), '+/', '-_'),
        '='
    );
    updateEnv($envPath, ['DATAFORM_APP_KEY'=>$key]);

    $verify = envValues($envPath);
    if (!hash_equals($key, (string)($verify['DATAFORM_APP_KEY'] ?? ''))) {
        throw new RuntimeException('DATAFORM_APP_KEY konnte nicht verifiziert werden.');
    }

    return ['created'=>true,'source'=>'DATAFORM_APP_KEY'];
}

function databaseExists(PDO $pdo, string $database): bool
{
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$database]);
    return $stmt->fetchColumn() !== false;
}
function createDatabase(PDO $pdo, string $database): void
{
    $quoted = quoteIdentifier($database);
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$quoted} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        // Manche MariaDB-Installationen kennen diese Kollation nicht oder verwenden abweichende Defaults.
        if (str_contains(strtolower($e->getMessage()), 'collation')) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS {$quoted} CHARACTER SET utf8mb4");
        } else {
            throw $e;
        }
    }
    if (!databaseExists($pdo, $database)) {
        throw new RuntimeException("Die Datenbank {$database} wurde nach CREATE DATABASE nicht gefunden. Prüfen Sie CREATE-Rechte und den verwendeten MariaDB-Server.");
    }
}
function migrationTableExists(PDO $pdo): bool
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'migrations'");
    return (int)$stmt->fetchColumn() > 0;
}
function migrationRecord(PDO $pdo, string $migration): ?array
{
    if (!migrationTableExists($pdo)) return null;
    $stmt = $pdo->prepare('SELECT migration, checksum, executed_at FROM migrations WHERE migration = ? LIMIT 1');
    $stmt->execute([$migration]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}
function registerMigration(PDO $pdo, string $migration, string $checksum): void
{
    if (!migrationTableExists($pdo)) {
        throw new RuntimeException("Migrationstabelle fehlt nach Ausführung von {$migration}.");
    }
    $stmt = $pdo->prepare('INSERT INTO migrations (migration, checksum) VALUES (?, ?) ON DUPLICATE KEY UPDATE checksum = checksum');
    $stmt->execute([$migration, $checksum]);
}
function installSchema(PDO $server, string $database, string $directory): array
{
    if (!databaseExists($server, $database)) {
        throw new RuntimeException("Schema-Installation abgebrochen: Datenbank {$database} ist nicht vorhanden.");
    }
    $server->exec('USE ' . quoteIdentifier($database));
    $files = glob($directory . '/*.php') ?: [];
    sort($files, SORT_NATURAL);
    if ($files === []) throw new RuntimeException('Keine Schema-Dateien gefunden: ' . $directory);

    $done = [];
    foreach ($files as $file) {
        $name = basename($file);
        $checksum = hash_file('sha256', $file);
        if (!is_string($checksum) || strlen($checksum) !== 64) {
            throw new RuntimeException("SHA-256 konnte für {$name} nicht ermittelt werden.");
        }

        $existing = migrationRecord($server, $name);
        if ($existing !== null) {
            if (!hash_equals((string)$existing['checksum'], $checksum)) {
                throw new RuntimeException("Migration {$name} wurde nach ihrer Ausführung verändert. Erwartet: {$existing['checksum']}; aktuell: {$checksum}");
            }
            $done[] = 'SKIP ' . $name . ' (bereits ausgeführt, Checksum OK)';
            continue;
        }

        $installer = require $file;
        if (!is_callable($installer)) throw new RuntimeException('Ungültige Schema-Datei: ' . $name);

        // MySQL/MariaDB performs implicit commits for DDL statements such as
        // CREATE TABLE. Wrapping schema migrations in a PDO transaction therefore
        // causes a later commit() to fail with "There is no active transaction".
        // Execute the schema callable directly and register it only after success.
        $installer($server);
        registerMigration($server, $name, $checksum);
        $done[] = 'APPLY ' . $name;
    }
    return $done;
}
function serverDiagnostics(PDO $pdo): array
{
    $row = $pdo->query("SELECT VERSION() AS version, @@hostname AS hostname, @@port AS port, @@datadir AS datadir, CURRENT_USER() AS authenticated_user")->fetch() ?: [];
    return [
        'Serverversion' => (string)($row['version'] ?? 'unbekannt'),
        'Servername' => (string)($row['hostname'] ?? 'unbekannt'),
        'Serverport' => (string)($row['port'] ?? 'unbekannt'),
        'Datenverzeichnis' => (string)($row['datadir'] ?? 'unbekannt'),
        'Angemeldeter DB-Benutzer' => (string)($row['authenticated_user'] ?? 'unbekannt'),
    ];
}

$env = envValues($envPath);
$form = [
    'host' => (string)($env['ADMIN_DB_HOST'] ?? '127.0.0.1'),
    'port' => (string)($env['ADMIN_DB_PORT'] ?? '3306'),
    'username' => (string)($env['ADMIN_DB_USERNAME'] ?? 'root'),
    'admin_db' => (string)($env['ADMIN_DB_DATABASE'] ?? 'easyit_admin'),
    'project_name' => (string)($env['CONTEXT_PROJECT_NAME'] ?? 'Demo'),
    'project_db' => (string)($env['PROJECT_DB_DATABASE'] ?? 'easyit_project_demo'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($form) as $key) $form[$key] = trim((string)($_POST[$key] ?? $form[$key]));
    $password = (string)($_POST['password'] ?? '');
    $action = (string)($_POST['action'] ?? 'test');
    $token = (string)($_POST['csrf_token'] ?? '');

    try {
        if (!hash_equals((string)$_SESSION['easyit_csrf'], $token)) throw new RuntimeException('Die Sicherheitsprüfung ist fehlgeschlagen. Laden Sie die Seite neu.');
        if (!extension_loaded('pdo_mysql')) throw new RuntimeException('Die PHP-Erweiterung pdo_mysql ist nicht aktiv. Aktivieren Sie sie in php.ini und starten Sie Apache neu.');
        $port = filter_var($form['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if ($port === false) throw new RuntimeException('Der Port muss zwischen 1 und 65535 liegen.');
        if (!validDatabaseName($form['admin_db']) || !validDatabaseName($form['project_db'])) throw new RuntimeException('Datenbanknamen dürfen nur Buchstaben, Zahlen und Unterstriche enthalten und müssen mit einem Buchstaben beginnen.');
        if ($form['admin_db'] === $form['project_db']) throw new RuntimeException('Administrations- und Projektdatenbank müssen getrennt sein.');

        $pdo = pdoServer($form['host'], (int)$port, $form['username'], $password);
        $diagnostics = serverDiagnostics($pdo);
        $results[] = '✔ Verbindung zum MariaDB-/MySQL-Server erfolgreich';

        if (in_array($action, ['create', 'install'], true)) {
            createDatabase($pdo, $form['admin_db']);
            $results[] = '✔ Administrationsdatenbank `' . $form['admin_db'] . '` wurde angelegt und verifiziert';
            createDatabase($pdo, $form['project_db']);
            $results[] = '✔ Projektdatenbank `' . $form['project_db'] . '` wurde angelegt und verifiziert';
        }

        if ($action === 'install') {
            foreach (installSchema($pdo, $form['admin_db'], __DIR__ . '/schema/admin') as $file) $results[] = '✔ Admin-Schema: ' . $file;
            foreach (installSchema($pdo, $form['project_db'], __DIR__ . '/schema/project') as $file) $results[] = '✔ Projekt-Schema: ' . $file;
            updateEnv($envPath, [
                'ADMIN_DB_DRIVER' => 'mysql', 'ADMIN_DB_HOST' => $form['host'], 'ADMIN_DB_PORT' => $form['port'],
                'ADMIN_DB_DATABASE' => $form['admin_db'], 'ADMIN_DB_USERNAME' => $form['username'], 'ADMIN_DB_PASSWORD' => $password,
                'PROJECT_DB_DRIVER' => 'mysql', 'PROJECT_DB_HOST' => $form['host'], 'PROJECT_DB_PORT' => $form['port'],
                'PROJECT_DB_DATABASE' => $form['project_db'], 'PROJECT_DB_USERNAME' => $form['username'], 'PROJECT_DB_PASSWORD' => $password,
                'CONTEXT_PROJECT_NAME' => $form['project_name'],
            ]);
            $secretState = ensureDataFormSecretKey($envPath);
            $results[] = $secretState['created']
                ? '✔ DATAFORM_APP_KEY wurde automatisch erzeugt'
                : '✔ DataForm-Secret-Schlüssel ist vorhanden (' . $secretState['source'] . ')';
            $results[] = '✔ DataForm5-Core/.env wurde aktualisiert';
            $_SESSION['easyit_db_setup_complete'] = true;
        }
    } catch (Throwable $e) {
        $messages[] = $e->getMessage();
        if ($e instanceof PDOException && isset($e->errorInfo[1])) {
            $messages[] = 'MariaDB-Fehlernummer: ' . (string)$e->errorInfo[1];
        }
    }
}

ob_start();
?>
<section class="hero"><span class="badge">Setup · Schritt 6</span><h1>Datenbanken vorbereiten</h1><p>Dieser Assistent legt die beiden Datenbanken wirklich an und prüft anschließend über INFORMATION_SCHEMA, ob sie auf genau diesem Server vorhanden sind.</p></section>
<?php foreach ($messages as $message): ?><div class="notice error" role="alert"><?= db_e($message) ?></div><?php endforeach; ?>
<?php if ($results): ?><section class="card"><h2>Ergebnis</h2><ul class="result-list"><?php foreach ($results as $result): ?><li><?= db_e($result) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
<?php if ($diagnostics): ?><section class="card"><h2>Verbundener Datenbankserver</h2><dl class="status-list"><?php foreach ($diagnostics as $label => $value): ?><div><dt><?= db_e($label) ?></dt><dd><code><?= db_e($value) ?></code></dd></div><?php endforeach; ?></dl><p><strong>Wichtig:</strong> Kontrollieren Sie phpMyAdmin auf demselben Host und Port. Mehrere MariaDB-Installationen auf einem Rechner können sonst zu Verwechslungen führen.</p></section><?php endif; ?>
<section class="card">
<h2>Server- und Datenbankdaten</h2>
<form method="post" class="form-grid" autocomplete="off">
<input type="hidden" name="csrf_token" value="<?= db_e((string)$_SESSION['easyit_csrf']) ?>">
<label>Datenbanktyp<select name="driver"><option selected>MariaDB / MySQL</option><option disabled>SQLite (ab RC1.1)</option><option disabled>CSV (ab RC1.1)</option><option disabled>Oracle (ab RC1.2)</option></select></label>
<label>Host<input name="host" value="<?= db_e($form['host']) ?>" required></label>
<label>Port<input name="port" value="<?= db_e($form['port']) ?>" inputmode="numeric" required></label>
<label>Benutzer<input name="username" value="<?= db_e($form['username']) ?>" required></label>
<label>Passwort<input type="password" name="password" value="" autocomplete="new-password"><small>Bei XAMPP lokal häufig leer.</small></label>
<h2 class="form-span">Administrationsdatenbank</h2>
<label class="form-span">Datenbankname<input name="admin_db" value="<?= db_e($form['admin_db']) ?>" required pattern="[A-Za-z][A-Za-z0-9_]{1,62}"></label>
<h2 class="form-span">Erstes Projekt</h2>
<label>Projektname<input name="project_name" value="<?= db_e($form['project_name']) ?>" required></label>
<label>Datenbankname<input name="project_db" value="<?= db_e($form['project_db']) ?>" required pattern="[A-Za-z][A-Za-z0-9_]{1,62}"></label>
<div class="form-span button-row">
<button class="button secondary" name="action" value="test" type="submit">1. Verbindung testen</button>
<button class="button secondary" name="action" value="create" type="submit">2. Datenbanken anlegen und prüfen</button>
<button class="button" name="action" value="install" type="submit">3. Schemas installieren und .env speichern</button>
</div>
</form>
<div class="notice"><strong>Hinweis:</strong> Geben Sie das Kennwort bei jedem Klick erneut ein. HTML-Passwortfelder werden absichtlich nicht vorausgefüllt.</div>
</section>
<nav class="page-actions"><a class="button secondary" <?= easyit_button_attributes('zurueck') ?> href="local-config.php">← Zurück zu Schritt 5</a><a class="button" <?= easyit_button_attributes('weiter') ?> href="admin.php">Administrator anlegen →</a></nav>
<?php
$content = ob_get_clean();
render_page(['title'=>'Datenbank-Assistent','active'=>'setup','base'=>'../','content'=>$content,'help'=>[
'title'=>'Datenbank-Assistent','location'=>'Setup → Schritt 6 → Datenbanken','short'=>'Der Assistent verbindet sich mit MariaDB, erzeugt zwei Datenbanken und verifiziert ihre Existenz.','goal'=>'Administrations- und Projektdatenbank nachweisbar auf dem ausgewählten Server anlegen.','next'=>'Führen Sie die drei Schaltflächen von links nach rechts aus.','steps'=>['MariaDB in XAMPP starten.','Verbindung testen und Serverdaten kontrollieren.','Datenbanken anlegen und Verifizierung abwarten.','Schemas installieren und .env speichern.','phpMyAdmin auf demselben Server/Port kontrollieren.'],'examples'=>['Host: 127.0.0.1 · Port: 3306','easyit_admin','easyit_project_demo'],'tips'=>['Das Passwort muss bei jedem Klick erneut eingegeben werden.','Prüfen Sie das angezeigte MariaDB-Datenverzeichnis.','Bei mehreren MariaDB-Installationen kann phpMyAdmin mit einem anderen Server verbunden sein.','CREATE DATABASE muss für den DB-Benutzer erlaubt sein.'],'duration'=>'ca. 2–5 Minuten']]);
