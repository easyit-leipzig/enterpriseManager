<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';

use EasyIT\Assistant\AssistantContext;
use EasyIT\Assistant\Integration\AssistantIntegrationRegistry;
use EasyIT\Assistant\Integration\AssistantLauncherRenderer;
use EasyIT\Assistant\Integration\AssistantSurfaceResolver;

function pass11(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    echo "PASS: $message\n";
}

$registry = new AssistantIntegrationRegistry($manager->getRegistry());
pass11($registry->hasSurface('project.management'), 'Projektverwaltung ist als Oberfläche registriert.');
pass11($registry->hasSurface('dataform'), 'DataForm ist als Oberfläche registriert.');
pass11(AssistantSurfaceResolver::resolve('/admin/projects/index.php') === 'project.management', 'Projektverwaltung wird aus Route erkannt.');
pass11(AssistantSurfaceResolver::resolve('/admin/dataform/relations.php') === 'dataform.relations', 'Beziehungsoberfläche wird aus Route erkannt.');
pass11(AssistantSurfaceResolver::resolve('/admin/dataform/fields.php') === 'dataform.fields', 'Feldoberfläche wird aus Route erkannt.');

$context = new AssistantContext('muster-csv', 'ed_ev', '42', '/admin/dataform/index.php');
$entries = $registry->entries('dataform', $context);
$ids = array_column($entries, 'id');
foreach (['dataform.create','dataform.fields','dataform.relations','dataform.events','dataform.actions','dataform.diagnostics'] as $id) {
    pass11(in_array($id, $ids, true), "$id ist auf der DataForm-Oberfläche verfügbar.");
}
$field = array_values(array_filter($entries, static fn(array $e): bool => $e['id'] === 'dataform.fields'))[0];
pass11($field['available'] === true, 'Feld-Assistent ist mit DataForm-Kontext direkt startbar.');
pass11(str_contains($field['url'], 'project_id=muster-csv'), 'Projektkontext wird in URL übernommen.');
pass11(str_contains($field['url'], 'dataform_id=ed_ev'), 'DataForm-Kontext wird in URL übernommen.');
pass11(str_contains($field['url'], 'record_id=42'), 'Datensatzkontext wird in URL übernommen.');
pass11(str_contains($field['url'], 'surface=dataform'), 'Ursprungsoberfläche bleibt im Wizard erhalten.');

$noForm = new AssistantContext('muster-csv', null, null, '/admin/dataform/index.php');
$noFormEntries = $registry->entries('dataform', $noForm);
$fieldsNoForm = array_values(array_filter($noFormEntries, static fn(array $e): bool => $e['id'] === 'dataform.fields'))[0];
pass11($fieldsNoForm['available'] === false && in_array('DataForm', $fieldsNoForm['missing'], true), 'Fehlender DataForm-Kontext deaktiviert DataForm-spezifischen Starter.');

$html = (new AssistantLauncherRenderer($registry))->render('dataform', $context);
pass11(str_contains($html, 'data-eit-assistant-surface="dataform"'), 'Renderer kennzeichnet die Oberfläche.');
pass11(str_contains($html, 'dataform_id=ed_ev'), 'Renderer enthält DataForm-Kontext.');
pass11(!str_contains(strtolower($html), 'background:'), 'Launcher erzeugt keine lokalen farbigen Button-Hintergründe.');

echo "PHASE11_SMOKE_PASS\n";
