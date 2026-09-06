<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';
$registry = $manager->getRegistry();
$integration = new \EasyIT\Assistant\Integration\AssistantIntegrationRegistry($registry);
$catalog = $integration->getCatalog();
$actions = new \EasyIT\Assistant\Integration\AssistantUiActionRegistry();

$failures = [];
$ok = static function (bool $condition, string $message) use (&$failures): void {
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
    if (!$condition) { $failures[] = $message; }
};

$allIds = array_keys($registry->all());
$groupIds = [];
foreach ($catalog->grouped() as $group) {
    foreach ($group['assistants'] as $entry) { $groupIds[] = $entry['id']; }
}
$ok(count($allIds) === 27, '27 Assistenten registriert.');
$ok(count($groupIds) === count(array_unique($groupIds)), 'Zentraler Katalog enthält keine doppelten Assistenten.');
sort($allIds); sort($groupIds);
$ok($allIds === $groupIds, 'Jeder registrierte Assistent ist genau einmal im zentralen Katalog enthalten.');

$centerIds = $catalog->assistantIdsForSurface('assistant.center'); sort($centerIds);
$ok($centerIds === $allIds, 'Assistentenzentrale erreicht alle registrierten Assistenten.');

foreach ($catalog->surfaceIds() as $surface) {
    $ids = $catalog->assistantIdsForSurface($surface);
    $ok($ids !== [], 'Surface ' . $surface . ' besitzt mindestens einen Assistenten.');
    foreach ($ids as $id) { $ok($registry->has($id), 'Surface ' . $surface . ' referenziert registrierten Assistenten ' . $id . '.'); }
}

$context = new \EasyIT\Assistant\AssistantContext('proj31', 'df31', '42', '/admin/dataforms/df31', ['surface' => 'dataform', 'workflow' => 'standard']);
$entries = $integration->entries('dataform', $context, 'run.php');
$ok($entries !== [], 'DataForm-Surface liefert kontextbezogene Einträge.');
$first = $entries[0];
$ok(isset($first['buttonKey'], $first['linkTitle'], $first['ariaLabel']), 'Launcher-Metadaten stammen zentral aus der UI-Aktionsregistry.');
$ok(str_contains($first['url'], 'project_id=proj31') && str_contains($first['url'], 'dataform_id=df31') && str_contains($first['url'], 'record_id=42'), 'Launcher-URL erhält Projekt-, DataForm- und Datensatzkontext.');

$renderer = new \EasyIT\Assistant\Integration\AssistantLauncherRenderer($integration);
$html = $renderer->render('dataform', $context, 'run.php');
$ok(str_contains($html, 'data-button-key="show"'), 'Launcher rendert zentrale Button-Key-Metadaten.');
$ok(str_contains($html, 'aria-label="Assistent öffnen:'), 'Launcher rendert zentrales aria-label.');

$nav = new \EasyIT\Assistant\Integration\AssistantPageNavigationRenderer($catalog, $actions);
$navHtml = $nav->render($context, 'dataform.fields', true, true);
$ok(str_contains($navHtml, 'Assistentenzentrale'), 'Einheitliche Toolbar enthält Assistentenzentrale.');
$ok(str_contains($navHtml, 'Verlauf / Undo / Redo'), 'Einheitliche Toolbar enthält Historie bei historisiertem Assistenten.');
$ok(str_contains($navHtml, 'Workflow-Fortschritt'), 'Einheitliche Toolbar enthält Workflow-Rückkehr.');

$resolve = \EasyIT\Assistant\Integration\AssistantSurfaceResolver::class;
$ok($resolve::resolve('/admin/projects') === 'project.management', 'SurfaceResolver erkennt Projektverwaltung.');
$ok($resolve::resolve('/admin/projects/demo/edit.php') === 'project.detail', 'SurfaceResolver erkennt Projektdetail.');
$ok($resolve::resolve('/admin/dataforms/fields.php') === 'dataform.fields', 'SurfaceResolver erkennt Feldseite.');
$ok($resolve::resolve('/admin/dataforms/relations.php') === 'dataform.relations', 'SurfaceResolver erkennt Beziehungsseite.');
$ok($resolve::resolve('/admin/dataforms/events.php') === 'dataform.events', 'SurfaceResolver erkennt Eventseite.');
$ok($resolve::resolve('/admin/dataforms/actions.php') === 'dataform.actions', 'SurfaceResolver erkennt Aktionsseite.');
$ok($resolve::resolve('/admin/dataforms/diagnostics.php') === 'dataform.diagnostics', 'SurfaceResolver erkennt Diagnoseseite.');

$ok($catalog->hasHistory('dataform.trust-membership'), 'History-Fähigkeit für späte Phasen ist zentral katalogisiert.');
$ok(!$catalog->hasHistory('core.status'), 'Nicht persistenter Core-Status bietet keine Historie an.');

if ($failures !== []) {
    fwrite(STDERR, 'Phase 31 FAIL: ' . implode(' | ', $failures) . PHP_EOL);
    exit(1);
}
echo "PHASE31_SMOKE_PASS\n";
