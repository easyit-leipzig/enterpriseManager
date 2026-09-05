<?php
declare(strict_types=1);

session_start();
require dirname(__DIR__) . '/system/ui/layout.php';

$rootPath = dirname(__DIR__);
$envPath = $rootPath . '/DataForm5-Core/.env';
$messages = [];
$results = [];
$status = [];

if (!isset($_SESSION['easyit_csrf'])) {
    $_SESSION['easyit_csrf'] = bin2hex(random_bytes(32));
}

function admin_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_env(string $path): array
{
    $values = [];
    if (!is_file($path) || !is_readable($path)) {
        return $values;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $values[trim($key)] = trim(trim($value), "\"'");
    }
    return $values;
}

function admin_pdo(array $env): PDO
{
    $required = ['ADMIN_DB_HOST', 'ADMIN_DB_PORT', 'ADMIN_DB_DATABASE', 'ADMIN_DB_USERNAME'];
    foreach ($required as $key) {
        if (!isset($env[$key]) || trim((string)$env[$key]) === '') {
            throw new RuntimeException("Die Einstellung {$key} fehlt. Führen Sie zuerst Schritt 6 vollständig aus.");
        }
    }

    $host = (string)$env['ADMIN_DB_HOST'];
    $port = (int)$env['ADMIN_DB_PORT'];
    $database = (string)$env['ADMIN_DB_DATABASE'];
    $username = (string)$env['ADMIN_DB_USERNAME'];
    $password = (string)($env['ADMIN_DB_PASSWORD'] ?? '');

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 8,
        ]
    );
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function adminCount(PDO $pdo): int
{
    $sql = "SELECT COUNT(DISTINCT u.id)
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE r.name = 'admin'";
    return (int)$pdo->query($sql)->fetchColumn();
}

$form = [
    'username' => '',
    'email' => '',
];

try {
    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException('Die PHP-Erweiterung pdo_mysql ist nicht aktiv.');
    }
    $env = admin_env($envPath);
    $pdo = admin_pdo($env);
    foreach (['users', 'roles', 'user_roles'] as $table) {
        if (!tableExists($pdo, $table)) {
            throw new RuntimeException("Die Tabelle {$table} fehlt. Installieren Sie in Schritt 6 zuerst das Administrationsschema.");
        }
    }
    $status = [
        'Datenbank' => (string)$env['ADMIN_DB_DATABASE'],
        'Benutzertabelle' => 'vorhanden',
        'Rollentabelle' => 'vorhanden',
        'Vorhandene Administratoren' => (string)adminCount($pdo),
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form['username'] = trim((string)($_POST['username'] ?? ''));
        $form['email'] = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $token = (string)($_POST['csrf_token'] ?? '');

        if (!hash_equals((string)$_SESSION['easyit_csrf'], $token)) {
            throw new RuntimeException('Die Sicherheitsprüfung ist fehlgeschlagen. Laden Sie die Seite neu.');
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,120}$/', $form['username'])) {
            throw new RuntimeException('Der Benutzername muss 3 bis 120 Zeichen lang sein und darf Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich enthalten.');
        }
        if ($form['email'] !== '' && filter_var($form['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Die E-Mail-Adresse ist ungültig.');
        }
        if (strlen($password) < 12) {
            throw new RuntimeException('Das Kennwort muss mindestens 12 Zeichen lang sein.');
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
            throw new RuntimeException('Das Kennwort muss mindestens einen Großbuchstaben, einen Kleinbuchstaben und eine Ziffer enthalten.');
        }
        if ($password !== $passwordConfirm) {
            throw new RuntimeException('Die Kennwörter stimmen nicht überein.');
        }

        $pdo->beginTransaction();
        try {
            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
            $roleStmt->execute();
            $roleId = $roleStmt->fetchColumn();
            if ($roleId === false) {
                $pdo->prepare("INSERT INTO roles (name, label) VALUES ('admin', 'Administrator')")->execute();
                $roleId = $pdo->lastInsertId();
            }

            $duplicate = $pdo->prepare('SELECT id FROM users WHERE username = ? OR (? <> \'\' AND email = ?) LIMIT 1');
            $duplicate->execute([$form['username'], $form['email'], $form['email']]);
            if ($duplicate->fetchColumn() !== false) {
                throw new RuntimeException('Benutzername oder E-Mail-Adresse ist bereits vergeben.');
            }

            $insertUser = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_active) VALUES (?, NULLIF(?, \'\'), ?, 1)');
            $insertUser->execute([
                $form['username'],
                $form['email'],
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int)$pdo->lastInsertId();

            $assignRole = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
            $assignRole->execute([$userId, (int)$roleId]);
            $pdo->commit();

            $_SESSION['easyit_admin_setup_complete'] = true;
            $results[] = '✔ Administratorkonto wurde erstellt';
            $results[] = '✔ Rolle „Administrator“ wurde zugewiesen';
            $results[] = '✔ Kennwort wurde ausschließlich als sicherer Hash gespeichert';
            $status['Vorhandene Administratoren'] = (string)adminCount($pdo);
            $form = ['username' => '', 'email' => ''];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
} catch (Throwable $e) {
    $messages[] = $e->getMessage();
    if ($e instanceof PDOException && isset($e->errorInfo[1])) {
        $messages[] = 'MariaDB-Fehlernummer: ' . (string)$e->errorInfo[1];
    }
}

ob_start();
?>
<section class="hero">
    <span class="badge">Setup · Schritt 7</span>
    <h1>Ersten Administrator anlegen</h1>
    <p>Erstellen Sie jetzt das erste lokale Administratorkonto. Das Kennwort wird nicht in der <code>.env</code>, sondern ausschließlich als sicherer Hash in der Administrationsdatenbank gespeichert.</p>
</section>

<?php foreach ($messages as $message): ?>
    <div class="notice error" role="alert"><?= admin_e($message) ?></div>
<?php endforeach; ?>

<?php if ($results): ?>
<section class="card">
    <h2>Ergebnis</h2>
    <ul class="result-list">
        <?php foreach ($results as $result): ?><li><?= admin_e($result) ?></li><?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php if ($status): ?>
<section class="card">
    <h2>Status der Administrationsdatenbank</h2>
    <dl class="status-list">
        <?php foreach ($status as $label => $value): ?>
            <div><dt><?= admin_e($label) ?></dt><dd><code><?= admin_e($value) ?></code></dd></div>
        <?php endforeach; ?>
    </dl>
</section>
<?php endif; ?>

<section class="card">
    <h2>Administratorkonto</h2>
    <form method="post" class="form-grid" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= admin_e((string)$_SESSION['easyit_csrf']) ?>">
        <label>Benutzername
            <input name="username" value="<?= admin_e($form['username']) ?>" minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]{3,120}" required autocomplete="username">
            <small>Beispiel: admin oder olaf.admin</small>
        </label>
        <label>E-Mail-Adresse
            <input type="email" name="email" value="<?= admin_e($form['email']) ?>" maxlength="190" autocomplete="email">
            <small>Optional, aber für spätere Kennwortfunktionen empfohlen.</small>
        </label>
        <label>Kennwort
            <input type="password" name="password" minlength="12" required autocomplete="new-password" data-password-field>
            <small>Mindestens 12 Zeichen, Groß- und Kleinbuchstaben sowie mindestens eine Ziffer.</small>
        </label>
        <label>Kennwort wiederholen
            <input type="password" name="password_confirm" minlength="12" required autocomplete="new-password" data-password-field>
        </label>
        <div class="form-span button-row">
            <button class="button" type="submit">Administrator sicher anlegen</button>
        </div>
    </form>
    <div class="notice"><strong>Sicherheit:</strong> Verwenden Sie nicht Ihr GitHub-, Windows- oder Datenbankkennwort. Das Kennwort wird niemals angezeigt, protokolliert oder in Git gespeichert.</div>
</section>

<script>
document.querySelectorAll('input[data-password-field]').forEach(function(input){
 var b=document.createElement('button'); b.type='button'; b.className='password-toggle'; b.textContent='👁';
 b.title='Kennwort anzeigen'; b.setAttribute('aria-label','Kennwort anzeigen');
 input.insertAdjacentElement('afterend',b);
 b.addEventListener('click',function(){var show=input.type==='password';input.type=show?'text':'password';b.title=show?'Kennwort verbergen':'Kennwort anzeigen';b.setAttribute('aria-label',b.title);});
});
</script>
<style>.password-toggle{margin-left:.35rem;border:1px solid #ccd5e2;border-radius:.4rem;background:#fff;padding:.35rem .55rem;cursor:pointer}</style>
<nav class="page-actions">
    <a class="button secondary" <?= easyit_button_attributes('zurueck') ?> href="database.php">← Zurück zu Schritt 6</a>
    <a class="button" <?= easyit_button_attributes('weiter') ?> href="../setup.php#step-8">Weiter zu Schritt 8 →</a>
</nav>
<?php
$content = ob_get_clean();
render_page([
    'title' => 'Administrator anlegen',
    'active' => 'setup',
    'base' => '../',
    'content' => $content,
    'help' => [
        'title' => 'Administrator-Assistent',
        'location' => 'Setup → Schritt 7 → Administrator',
        'short' => 'Hier erzeugen Sie das erste Konto für die Enterprise-Administration.',
        'goal' => 'Einen aktiven Benutzer mit der Rolle Administrator sicher in der Administrationsdatenbank speichern.',
        'next' => 'Benutzername, optionale E-Mail und ein starkes Kennwort eingeben und das Konto anlegen.',
        'steps' => [
            'Prüfen, ob Schritt 6 vollständig abgeschlossen ist.',
            'Eindeutigen Benutzernamen eingeben.',
            'Ein starkes Kennwort zweimal eingeben.',
            'Administrator anlegen.',
            'Die Erfolgsmeldungen und die Administratoranzahl kontrollieren.',
        ],
        'examples' => ['Benutzername: admin', 'E-Mail: admin@example.local', 'Kennwort: mindestens 12 Zeichen'],
        'tips' => [
            'Benutzername und E-Mail dürfen noch nicht vergeben sein.',
            'Das Kennwort wird nur als Hash gespeichert.',
            'Die Datenbanktabellen users, roles und user_roles müssen vorhanden sein.',
            'Legen Sie zunächst nur ein persönliches Administratorkonto an.',
        ],
        'duration' => 'ca. 2 Minuten',
    ],
]);
