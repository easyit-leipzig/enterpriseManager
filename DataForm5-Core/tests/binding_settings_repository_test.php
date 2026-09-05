<?php
declare(strict_types=1);

use EasyIT\DataForm5\Binding\BindingSettingsRepository;

require_once dirname(__DIR__)
    . '/system/dataform/binding/BindingSettingsRepository.php';

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    echo "SKIP: PDO SQLite nicht verfügbar.\n";
    exit(0);
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

$pdo->exec(
    file_get_contents(
        dirname(__DIR__)
        . '/migrations/admin/sqlite/005_bound_form_binding_settings.sql'
    )
);

$repo = new BindingSettingsRepository($pdo);

$defaults = $repo->get('rel1');

if (
    $defaults['inheritParentValue'] !== true
    || $defaults['boundFieldReadonly'] !== true
) {
    throw new RuntimeException(
        'Defaultwerte sind falsch.'
    );
}

$repo->save('rel1', false);

$settings = $repo->get('rel1');

if (
    $settings['inheritParentValue'] !== true
    || $settings['boundFieldReadonly'] !== false
) {
    throw new RuntimeException(
        'Gespeicherte Werte sind falsch.'
    );
}

echo "PASS: BindingSettingsRepository\n";
