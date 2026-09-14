<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require dirname(__DIR__, 2) . '/system/ui/layout.php';

$user = enterprise_require_auth('../../');
enterprise_require_capability($user,'projects.view');
$error = '';
$projects = [];
$flash = '';
$flashType = 'success';
$flashBackupToken = '';
$flashBackupFilename = '';
$flashBackupSha256 = '';
$flashBackupExpiresAt = 0;

try {
    $pdo = enterprise_pdo();
    enterprise_upgrade($pdo);
    $projects = $pdo->query('SELECT * FROM projects ORDER BY name')->fetchAll();

    if (isset($_SESSION['project_flash']) && is_array($_SESSION['project_flash'])) {
        $flash = trim((string)($_SESSION['project_flash']['message'] ?? ''));
        $flashType = (string)($_SESSION['project_flash']['type'] ?? 'success');
        $flashBackupToken = trim((string)($_SESSION['project_flash']['backup_token'] ?? ''));
        $flashBackupFilename = trim((string)($_SESSION['project_flash']['backup_filename'] ?? ''));
        $flashBackupSha256 = trim((string)($_SESSION['project_flash']['backup_sha256'] ?? ''));
        $flashBackupExpiresAt = (int)($_SESSION['project_flash']['backup_expires_at'] ?? 0);
        unset($_SESSION['project_flash']);
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

ob_start();
render_breadcrumbs([
    ['label' => 'Enterprise', 'href' => '../dashboard.php'],
    ['label' => 'Projekte', 'href' => ''],
]);
?>
<section class="hero">
    <span class="badge"><?= enterprise_is_superadmin($user) ? 'Superadmin · Projektverwaltung' : 'Projektverwaltung' ?></span>
    <h1>Projekte</h1>
    <p>Alle easyIT-Projekte werden zentral verwaltet. <?= enterprise_is_superadmin($user) ? 'Sie sind als Superadministrator angemeldet und verfügen hier über die vollständige Projekt-CRUD-Verwaltung.' : 'Öffnen, bearbeiten und entfernen Sie Projekte entsprechend Ihrer Berechtigungen.' ?></p>
    <div class="actions">
        <a class="button" <?= easyit_button_attributes('neu','project') ?> data-button-fixed="1" data-crud="create" href="create.php">Neues Projekt anlegen</a>
        <a class="button secondary" <?= easyit_button_attributes('projekt_registrieren') ?> data-button-fixed="1" data-crud="create" href="register.php">Vorhandenes Projekt registrieren</a>
        <a class="button secondary" <?= easyit_button_attributes('restore') ?> data-button-fixed="1" data-crud="create" href="restore.php">Projektsicherung wiederherstellen</a>
    </div>
</section>

<?php if ($flash !== ''): ?>
    <div class="notice <?= $flashType === 'error' ? 'error' : 'success' ?>" role="status">
        <div><?= e($flash) ?></div>
        <?php if ($flashBackupToken !== ''): ?>
            <div class="button-row" style="margin-top:.75rem">
                <a class="button secondary" data-crud="read" href="backup-download.php?token=<?= e($flashBackupToken) ?>">Projektsicherung herunterladen</a>
            </div>
            <small>
                Datei: <code><?= e($flashBackupFilename) ?></code>
                <?php if ($flashBackupSha256 !== ''): ?> · SHA-256: <code><?= e($flashBackupSha256) ?></code><?php endif; ?>
                <?php if ($flashBackupExpiresAt > 0): ?> · Downloadlink gültig bis <?= e(date('d.m.Y H:i', $flashBackupExpiresAt)) ?><?php endif; ?>
            </small>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="notice error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$projects): ?>
    <div class="empty-state">
        <h2>Noch keine Projekte</h2>
        <a class="button" <?= easyit_button_attributes('neu','project') ?> data-button-fixed="1" data-crud="create" href="create.php">Erstes Projekt vollständig anlegen</a>
        <a class="button secondary" <?= easyit_button_attributes('projekt_registrieren') ?> data-button-fixed="1" data-crud="create" href="register.php">Vorhandenes Projekt registrieren</a>
        <a class="button secondary" <?= easyit_button_attributes('restore') ?> data-button-fixed="1" data-crud="create" href="restore.php">Projektsicherung wiederherstellen</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="project-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Produkt</th>
                <th>Datenspeicher</th>
                <th>Status</th>
                <th>Geändert</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td><strong><?= e((string)$p['name']) ?></strong></td>
                    <td><?= e((string)$p['product_type']) ?></td>
                    <td><strong><?= e(strtoupper((string)($p['database_driver'] ?? 'mysql'))) ?></strong><br><code><?= e((string)$p['database_name']) ?></code></td>
                    <td><?= e((string)$p['status']) ?></td>
                    <td><?= e((string)$p['updated_at']) ?></td>
                    <td>
                        <div class="project-crud-actions" aria-label="Aktionen für <?= e((string)$p['name']) ?>">
                            <a class="button" <?= easyit_button_attributes('anzeigen','project') ?> data-button-fixed="1" data-crud="read" href="view.php?id=<?= (int)$p['id'] ?>">Öffnen</a>
                            <a class="button" <?= easyit_button_attributes('bearbeiten','project') ?> data-button-fixed="1" data-crud="edit" href="edit.php?id=<?= (int)$p['id'] ?>">Bearbeiten</a>
                            <form method="post" action="export.php" class="inline-form" style="display:inline" onsubmit="return confirm('Schlanke HTML5-DataForm-Anwendung für dieses Projekt erzeugen?');">
                                <input type="hidden" name="csrf" value="<?= e(enterprise_csrf()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="button" <?= easyit_button_attributes('exportieren','project_package') ?> data-button-fixed="1" type="submit">HTML5-App exportieren</button>
                            </form>
                            <a class="button" <?= easyit_button_attributes('loeschen','project') ?> data-button-fixed="1" data-crud="delete" href="delete.php?id=<?= (int)$p['id'] ?>">Löschen</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_page([
    'title' => 'Projekte',
    'active' => 'projects',
    'base' => '../../',
    'content' => $content,
    'app_nav' => true,
    'user' => $user,
    'help' => [
        'title' => 'Projektverwaltung',
        'location' => 'Enterprise → Projekte',
        'short' => 'Hier verwalten Sie alle registrierten easyIT-Projekte mit vollständigem CRUD.',
        'goal' => 'Projekt anlegen, öffnen, bearbeiten, als lauffähiges HTML5-App exportieren oder aus der Enterprise-Verwaltung entfernen.',
        'next' => 'Projekt öffnen oder eine CRUD-Aktion in der Liste wählen.',
        'tips' => [
            'Beim Löschen können Sie vorab eine vollständige Projektsicherung als Download erzeugen und entscheiden anschließend ausdrücklich, ob die Projektdatenbank erhalten bleibt oder zusätzlich endgültig gelöscht wird.',
            'Eine heruntergeladene Projektsicherung kann über „Projektsicherung wiederherstellen“ inklusive Datenbank und Projektregistrierung zurückgespielt werden.',
            'Über den Export-Button wird eine eigenständige HTML5-DataForm-Anwendung erzeugt: je DataForm eine CRUD-Seite, dazu nur Runtime, DB-Anbindung, notwendige Assets, Projektmedien und SQL-Snapshot.',
            'Name, technischer Slug, Status und Beschreibung können über „Bearbeiten“ geändert werden.',
        ],
    ],
]);
