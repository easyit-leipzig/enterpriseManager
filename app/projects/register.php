<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require dirname(__DIR__, 2) . '/system/ui/layout.php';

$user = enterprise_require_auth('../../');
$error = '';
$databaseStatus = [
    'configured' => false,
    'exists' => false,
    'schema' => false,
    'message' => '',
];

$env = enterprise_env(dirname(__DIR__, 2) . '/DataForm5-Core/.env');
$setupDatabase = trim((string)($env['PROJECT_DB_DATABASE'] ?? ''));
$form = [
    'name' => trim((string)($env['CONTEXT_PROJECT_NAME'] ?? '')),
    'slug' => '',
    'product_type' => 'dataform',
    'database_name' => $setupDatabase,
];

function project_database_status(array $env): array
{
    $database = trim((string)($env['PROJECT_DB_DATABASE'] ?? ''));
    if ($database === '') {
        return [
            'configured' => false,
            'exists' => false,
            'schema' => false,
            'message' => 'In DataForm5-Core/.env ist noch keine Projektdatenbank eingetragen. Führen Sie zuerst Setup-Schritt 6 aus.',
        ];
    }

    foreach (['PROJECT_DB_HOST', 'PROJECT_DB_PORT', 'PROJECT_DB_USERNAME'] as $key) {
        if (trim((string)($env[$key] ?? '')) === '') {
            return [
                'configured' => false,
                'exists' => false,
                'schema' => false,
                'message' => "Die Projekteinstellung {$key} fehlt in DataForm5-Core/.env.",
            ];
        }
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . $env['PROJECT_DB_HOST']
            . ';port=' . (int)$env['PROJECT_DB_PORT']
            . ';dbname=' . $database
            . ';charset=' . ($env['PROJECT_DB_CHARSET'] ?? 'utf8mb4'),
            (string)$env['PROJECT_DB_USERNAME'],
            (string)($env['PROJECT_DB_PASSWORD'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $schemaReady = in_array('dataforms', $tables, true) && in_array('dataform_fields', $tables, true);

        return [
            'configured' => true,
            'exists' => true,
            'schema' => $schemaReady,
            'message' => $schemaReady
                ? 'Die Projektdatenbank ist erreichbar und das DataForm-Basisschema wurde gefunden.'
                : 'Die Projektdatenbank ist erreichbar, aber das erwartete DataForm-Basisschema ist unvollständig.',
        ];
    } catch (Throwable $e) {
        return [
            'configured' => true,
            'exists' => false,
            'schema' => false,
            'message' => 'Die in der .env registrierte Projektdatenbank ist nicht erreichbar: ' . $e->getMessage(),
        ];
    }
}

try {
    $pdo = enterprise_pdo();
    enterprise_upgrade($pdo);
    $databaseStatus = project_database_status($env);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        enterprise_check_csrf((string)($_POST['csrf_token'] ?? ''));

        $form['name'] = trim((string)($_POST['name'] ?? ''));
        $form['slug'] = trim((string)($_POST['slug'] ?? ''));
        $form['product_type'] = trim((string)($_POST['product_type'] ?? 'dataform'));
        $form['database_name'] = $setupDatabase;

        if ($form['name'] === '' || !preg_match('/^[a-z0-9][a-z0-9-]{1,158}$/', $form['slug'])) {
            throw new RuntimeException('Projektname und ein gültiger technischer Slug sind erforderlich.');
        }
        if ($form['product_type'] !== 'dataform') {
            throw new RuntimeException('In RC1.1.1-dev ist nur DataForm aktiv.');
        }
        if ($setupDatabase === '') {
            throw new RuntimeException('Es ist keine Projektdatenbank aus dem Setup registriert. Führen Sie zuerst Schritt 6 aus.');
        }
        if (!$databaseStatus['exists']) {
            throw new RuntimeException('Die Projektdatenbank aus dem Setup ist nicht erreichbar. Es wird keine neue Datenbank angelegt.');
        }
        if (!$databaseStatus['schema']) {
            throw new RuntimeException('Das Basisschema der Projektdatenbank ist unvollständig. Führen Sie Setup-Schritt 6 erneut bis zum Schema-Schritt aus.');
        }

        $duplicate = $pdo->prepare('SELECT id, name FROM projects WHERE database_name = ? LIMIT 1');
        $duplicate->execute([$setupDatabase]);
        $existing = $duplicate->fetch();
        if ($existing) {
            throw new RuntimeException('Die Projektdatenbank ist bereits dem Projekt „' . (string)$existing['name'] . '“ zugeordnet.');
        }

        $stmt = $pdo->prepare(
            "INSERT INTO projects(name, slug, product_type, database_driver, database_name, status)
             VALUES (?, ?, ?, 'mysql', ?, 'active')"
        );
        $stmt->execute([$form['name'], $form['slug'], $form['product_type'], $setupDatabase]);
        $id = (int)$pdo->lastInsertId();

        enterprise_audit($pdo, (int)$user['id'], 'project.register', 'project', (string)$id, [
            'name' => $form['name'],
            'slug' => $form['slug'],
            'product_type' => $form['product_type'],
            'database_name' => $setupDatabase,
            'database_source' => 'setup_env',
            'database_created' => false,
        ]);
        enterprise_event_dispatch('project.registered', [
            'project_id'=>$id,
            'name'=>$form['name'],
            'slug'=>$form['slug'],
            'product_type'=>$form['product_type'],
            'database_name'=>$setupDatabase,
        ], ['user_id'=>(int)$user['id']]);

        header('Location: view.php?id=' . $id);
        exit;
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

ob_start();
?>
<?php render_breadcrumbs([['label'=>'Enterprise','href'=>'../dashboard.php'],['label'=>'Projekte','href'=>'index.php'],['label'=>'Vorhandenes Projekt registrieren','href'=>'']]); ?>
<section class="hero">
    <span class="badge">Projekt-Assistent</span>
    <h1>Vorhandenes Projekt registrieren</h1>
    <p>Ordnen Sie die bereits in Setup-Schritt 6 angelegte Projektdatenbank einem Enterprise-Projekt zu. Es wird keine neue Datenbank erzeugt.</p>
</section>

<?php if ($error): ?>
    <div class="notice error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<section class="card">
    <h2>Projektdatenbank aus dem Setup</h2>
    <dl class="status-list">
        <div><dt>Konfiguration</dt><dd><?= $databaseStatus['configured'] ? '✔ vorhanden' : '✘ fehlt' ?></dd></div>
        <div><dt>Datenbank</dt><dd><code><?= e($setupDatabase !== '' ? $setupDatabase : 'nicht eingetragen') ?></code></dd></div>
        <div><dt>Verbindung</dt><dd><?= $databaseStatus['exists'] ? '✔ geprüft' : '✘ nicht erreichbar' ?></dd></div>
        <div><dt>Basisschema</dt><dd><?= $databaseStatus['schema'] ? '✔ vorhanden' : '✘ unvollständig' ?></dd></div>
    </dl>
    <p><?= e($databaseStatus['message']) ?></p>
</section>

<section class="card">
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(enterprise_csrf()) ?>">

        <label>
            Projektname
            <input name="name" value="<?= e($form['name']) ?>" required placeholder="Demo DataForm">
        </label>

        <label>
            Technischer Slug
            <input name="slug" value="<?= e($form['slug']) ?>" required pattern="[a-z0-9][a-z0-9-]{1,158}" placeholder="demo-dataform">
            <small>Kleinbuchstaben, Ziffern und Bindestriche.</small>
        </label>

        <label>
            Produkt
            <select name="product_type">
                <option value="dataform" selected>DataForm</option>
                <option disabled>Dialog – geplant</option>
                <option disabled>Nachhilfe – geplant</option>
                <option disabled>CSV-Engine – geplant</option>
            </select>
        </label>

        <label>
            Projektdatenbank aus Schritt 6
            <input value="<?= e($setupDatabase) ?>" readonly aria-readonly="true">
            <small>Dieses Feld stammt aus <code>PROJECT_DB_DATABASE</code> und kann hier nicht geändert werden.</small>
        </label>

        <div class="form-span notice">
            <strong>Wichtig:</strong> Beim Registrieren wird ausschließlich ein Projekteinsatz in der Administrationsdatenbank angelegt. Es wird kein <code>CREATE DATABASE</code> ausgeführt und das vorhandene Projektschema wird nicht neu initialisiert.
        </div>

        <div class="form-span button-row">
            <button class="button" type="submit" <?= (!$databaseStatus['exists'] || !$databaseStatus['schema']) ? 'disabled' : '' ?>>Projekt registrieren</button>
            <a class="button secondary" href="index.php">Abbrechen</a>
        </div>
    </form>
</section>
<?php
$content = ob_get_clean();

render_page([
    'title' => 'Vorhandenes Projekt registrieren',
    'active' => 'projects',
    'base' => '../../',
    'content' => $content,
    'app_nav' => true,
    'user' => $user,
    'help' => [
        'title' => 'Projekt-Assistent',
        'location' => 'Enterprise → Projekte → Registrieren',
        'short' => 'Die im Setup erzeugte Projektdatenbank wird einem Enterprise-Projekt zugeordnet.',
        'goal' => 'Projektname und DataForm-Produkt mit der vorhandenen Projektdatenbank verbinden.',
        'steps' => [
            'Status der Projektdatenbank kontrollieren.',
            'Aussagekräftigen Projektnamen vergeben.',
            'Technischen Slug mit Bindestrichen festlegen.',
            'Projekt registrieren.',
        ],
        'examples' => [
            'Projektname: Demo DataForm',
            'Slug: demo-dataform',
            'Datenbank: ' . ($setupDatabase !== '' ? $setupDatabase : 'aus Setup-Schritt 6'),
        ],
        'tips' => [
            'Hier wird keine neue Datenbank angelegt.',
            'Der Datenbankname wird aus DataForm5-Core/.env gelesen.',
            'Eine Projektdatenbank kann nur einem Enterprise-Projekt zugeordnet werden.',
        ],
    ],
]);
