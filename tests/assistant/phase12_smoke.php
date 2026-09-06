<?php
declare(strict_types=1);

session_start();
$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';
$store = new \EasyIT\Assistant\State\AssistantStateStore();

function ok12(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    echo 'PASS: ' . $message . PHP_EOL;
}
function ctx12(?string $project, ?string $dataForm, array $input = [], string $method = 'GET'): \EasyIT\Assistant\AssistantContext
{
    return new \EasyIT\Assistant\AssistantContext($project, $dataForm, null, null, $input, ['requestMethod' => $method]);
}

$project = 'phase12-good';
$dataForm = 'ed_ev';
$scope = $project . '|' . $dataForm;
$projectDir = $root . '/projects/' . $project;
$csvDir = sys_get_temp_dir() . '/easyit_phase12_' . bin2hex(random_bytes(4));
@mkdir($projectDir . '/config', 0777, true);
@mkdir($csvDir, 0777, true);
file_put_contents($projectDir . '/config/project.json', json_encode(['project' => ['id' => $project]], JSON_PRETTY_PRINT));
file_put_contents($csvDir . '/ed_ev.csv', "id|name\n42|Test\n");

try {
    ok12($manager->getRegistry()->has('workflow.standard'), 'Workflow-Assistent ist zentral registriert.');

    $initial = $manager->run('workflow.standard', ctx12(null, null));
    ok12($initial->isOk(), 'Workflow kann ohne Kontext gestartet werden.');
    $initialFlow = $initial->getData()['workflow'];
    ok12(($initialFlow['nextStep'] ?? '') === 'project', 'Ohne Kontext ist Projekt der erste offene Schritt.');

    $store->put('datasource.configure', [
        'profile' => ['name' => 'project-main', 'driver' => 'csv'],
        'connection' => ['path' => $csvDir, 'delimiter' => '|', 'header' => true],
        'test' => ['ok' => true, 'message' => 'OK', 'details' => [], 'warnings' => [], 'testedAt' => gmdate('c')],
        'discovery' => [['name' => 'ed_ev', 'label' => 'ed_ev', 'type' => 'table', 'meta' => ['valid' => true]]],
        'selection' => ['sourceName' => 'ed_ev', 'sourceType' => 'table'],
    ], $project);

    $fields = [
        ['name' => 'id', 'label' => 'ID', 'type' => 'integer', 'required' => true, 'readOnly' => true, 'default' => null],
        ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'readOnly' => false, 'default' => null],
    ];
    $store->put('dataform.create', [
        'source' => ['profile' => 'project-main', 'driver' => 'csv', 'connection' => 'profile:project-main', 'name' => 'ed_ev'],
        'identity' => ['dataFormName' => $dataForm, 'primaryKey' => 'id'],
        'features' => ['fullTextSearch' => true, 'filter' => true, 'pagination' => ['enabled' => true, 'position' => 'below-records', 'pageSize' => 20, 'windowLeft' => 2, 'windowRight' => 2, 'showFirst' => true, 'showLast' => true]],
        'crud' => ['create' => true, 'show' => true, 'edit' => true, 'delete' => true, 'save' => true],
        'fields' => $fields,
    ], $project);
    $store->put('dataform.fields', [
        'context' => ['dataForm' => $dataForm, 'sourceProfile' => 'project-main', 'sourceName' => 'ed_ev', 'primaryKey' => 'id'],
        'fields' => $fields,
        'lookups' => [],
        'derivedEnums' => [],
    ], $scope);
    $store->put('dataform.relations', [
        'relation' => ['name' => 'ev_info', 'type' => 'one_to_many'],
        'parent' => ['dataForm' => $dataForm, 'source' => 'ed_ev', 'keyField' => 'id'],
        'child' => ['dataForm' => 'ed_ev_info', 'source' => 'ed_ev_info', 'foreignKeyField' => 'to_ev_id'],
        'binding' => ['enabled' => true, 'valueSource' => 'parent.currentRecord', 'parentValueField' => 'id', 'childTargetField' => 'to_ev_id', 'fillOnNewRecord' => true, 'readOnly' => true],
        'display' => ['paginationPosition' => 'below-records'],
    ], $scope);
    $store->put('dataform.events', [
        'context' => ['objectName' => 'dataFormContext', 'schema' => 'easyit.dataform.action-context.v1'],
        'events' => [
            'beforeSave' => ['enabled' => true, 'handler' => 'beforeSave', 'blocking' => true],
            'afterSave' => ['enabled' => true, 'handler' => 'afterSave', 'blocking' => false],
            'beforeDelete' => ['enabled' => true, 'handler' => 'beforeDelete', 'blocking' => true],
            'afterDelete' => ['enabled' => true, 'handler' => 'afterDelete', 'blocking' => false],
        ],
        'runtime' => ['handlerResolution' => 'global-path', 'allowEval' => false, 'beforeEventFalseCancelsAction' => true, 'captureHandlerErrors' => true],
    ], $scope);
    $store->put('dataform.actions', [
        'dataForm' => ['name' => $dataForm],
        'actions' => ['new' => true, 'show' => true, 'edit' => true, 'save' => true, 'delete' => true, 'first' => true, 'previous' => true, 'next' => true, 'last' => true],
        'ui' => ['buttonRegistry' => 'central', 'localTitlesAllowed' => false, 'localAriaLabelsAllowed' => false, 'cssBackgroundButtonsAllowed' => false],
    ], $scope);
    $store->put('dataform.diagnostics', [
        'dataForm' => $dataForm,
        'runtimeDataSource' => false,
        'sampleParentValue' => 42,
    ], $project);

    $full = $manager->run('workflow.standard', ctx12($project, $dataForm));
    ok12($full->isOk(), 'Workflow lässt sich mit vollständigem Kontext auswerten.');
    $flow = $full->getData()['workflow'];
    ok12(($flow['projectId'] ?? '') === $project, 'Projektkontext wird im Workflow gespeichert.');
    ok12(($flow['dataFormId'] ?? '') === $dataForm, 'DataForm-Kontext wird im Workflow gespeichert.');
    ok12(($flow['allComplete'] ?? false) === true, 'Vollständig gültige Konfiguration schließt den Workflow ab.');
    ok12(($flow['percent'] ?? 0) === 100, 'Abgeschlossener Workflow zeigt 100 Prozent.');
    ok12(array_key_exists('nextStep', $flow) && $flow['nextStep'] === null, 'Abgeschlossener Workflow hat keine offene Station.');
    foreach ($flow['steps'] as $step) {
        ok12(($step['status'] ?? '') === 'complete', 'Workflow-Schritt ' . ($step['id'] ?? '?') . ' ist abgeschlossen.');
    }

    // Remove optional configurations and explicitly skip them.
    $store->clear('dataform.relations', $scope);
    $store->clear('dataform.events', $scope);
    $skip = $manager->run('workflow.standard', ctx12($project, $dataForm, ['workflow_action' => 'skip_relations'], 'POST'));
    ok12($skip->isOk(), 'Beziehungen können ausdrücklich als nicht benötigt bestätigt werden.');
    $skip = $manager->run('workflow.standard', ctx12($project, $dataForm, ['workflow_action' => 'skip_events'], 'POST'));
    ok12($skip->isOk(), 'Events können ausdrücklich als nicht benötigt bestätigt werden.');
    $skipFlow = $skip->getData()['workflow'];
    $byId = [];
    foreach ($skipFlow['steps'] as $step) { $byId[$step['id']] = $step; }
    ok12(($byId['relations']['status'] ?? '') === 'complete' && !empty($byId['relations']['details']['skipped']), 'Relations-Skip wird nachvollziehbar als abgeschlossen markiert.');
    ok12(($byId['events']['status'] ?? '') === 'complete' && !empty($byId['events']['details']['skipped']), 'Events-Skip wird nachvollziehbar als abgeschlossen markiert.');

    echo "PHASE12_SMOKE=PASS\n";
} finally {
    @unlink($projectDir . '/config/project.json');
    @rmdir($projectDir . '/config');
    @rmdir($projectDir);
    @rmdir($root . '/projects');
    @unlink($csvDir . '/ed_ev.csv');
    @rmdir($csvDir);
}
