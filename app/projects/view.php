<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require dirname(__DIR__, 2) . '/system/ui/layout.php';

$user = enterprise_require_auth('../../');
enterprise_require_capability($user,'projects.view');
$id = (int)($_GET['id'] ?? 0);
$error = '';
$project = null;
$flash = '';
$flashType = 'success';

try {
    $pdo = enterprise_pdo();
    enterprise_upgrade($pdo);
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) {
        throw new RuntimeException('Projekt nicht gefunden.');
    }
    $_SESSION['active_project_id'] = $id;
    if (isset($_SESSION['project_flash']) && is_array($_SESSION['project_flash'])) {
        $flash = trim((string)($_SESSION['project_flash']['message'] ?? ''));
        $flashType = (string)($_SESSION['project_flash']['type'] ?? 'success');
        unset($_SESSION['project_flash']);
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$productUrl = '';
if ($project && $project['product_type'] === 'dataform') {
    $productUrl = '../../products/dataform/runtime.php?project=' . (int)$project['id'];
}

ob_start();
?>
<?php render_breadcrumbs([['label'=>'Enterprise','href'=>'../dashboard.php'],['label'=>'Projekte','href'=>'index.php'],['label'=>(string)($project['name'] ?? 'Projekt'),'href'=>'']]); ?>
<?php if ($flash !== ''): ?>
    <div class="notice <?= $flashType === 'error' ? 'error' : 'success' ?>" role="status"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="notice error" role="alert"><?= e($error) ?></div>
<?php else: ?>
    <section class="hero">
        <span class="badge"><?= e($project['product_type']) ?></span>
        <h1><?= e($project['name']) ?></h1>
        <p>Projekt-Dashboard und sicherer Übergang in die Produktoberfläche.</p>
        <div class="actions">
            <?php if ($productUrl !== ''): ?>
                <a class="button" href="<?= e($productUrl) ?>">DataForm öffnen</a>
            <?php else: ?>
                <span class="button secondary" aria-disabled="true">Produkt noch nicht verfügbar</span>
            <?php endif; ?>
            <form method="post" action="export.php" class="inline-form" style="display:inline"><input type="hidden" name="csrf" value="<?= e(enterprise_csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$project['id'] ?>"><button class="button" <?= easyit_button_attributes('exportieren','project_package') ?> data-button-fixed="1" type="submit">HTML5-App exportieren</button></form>
            <a class="button" data-crud="edit" href="edit.php?id=<?= (int)$project['id'] ?>">Bearbeiten</a>
            <a class="button" data-crud="delete" href="delete.php?id=<?= (int)$project['id'] ?>">Löschen</a>
            <a class="button secondary" href="index.php">Zur Projektliste</a>
        </div>
    </section>

    <div class="metric-grid">
        <div class="metric"><strong><?= e($project['status']) ?></strong><span>Status</span></div>
        <div class="metric"><strong><?= e($project['product_type']) ?></strong><span>Produkt</span></div>
        <div class="metric"><strong><?= e($project['database_driver']) ?></strong><span>Treiber</span></div>
    </div>

    <section class="card">
        <h2>Projektverbindung</h2>
        <dl class="status-list">
            <div><dt>Datenbank</dt><dd><code><?= e($project['database_name']) ?></code></dd></div>
            <div><dt>Interner Projektname</dt><dd><code><?= e($project['slug']) ?></code></dd></div>
            <div><dt>Erstellt</dt><dd><?= e($project['created_at']) ?></dd></div>
        </dl>
    </section>
<?php endif; ?>
<?php
$content = ob_get_clean();

render_page([
    'title' => $project['name'] ?? 'Projekt',
    'active' => 'projects',
    'base' => '../../',
    'content' => $content,
    'app_nav' => true,
    'user' => $user,
    'help' => [
        'title' => 'Projekt-Dashboard',
        'location' => 'Enterprise → Projekte → Projekt',
        'short' => 'Zentrale Übersicht des ausgewählten Projekts.',
        'goal' => 'Das zugeordnete Produkt öffnen oder Projektinformationen prüfen.',
        'next' => 'Öffnen Sie DataForm über die Produktoberfläche.',
        'tips' => [
            'Der Core wird niemals direkt als Benutzeroberfläche geöffnet.',
            'Zur Projektwahl gelangen Sie über „Zur Projektliste“.',
        ],
    ],
]);
