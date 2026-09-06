<?php
declare(strict_types=1);

session_start();
$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';
$store = new \EasyIT\Assistant\State\AssistantStateStore();

function ctx8(string $project, ?string $dataForm, array $input = [], string $method = 'POST'): \EasyIT\Assistant\AssistantContext
{
    return new \EasyIT\Assistant\AssistantContext($project, $dataForm, null, null, $input, ['requestMethod' => $method]);
}
function ok8(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    echo 'PASS: ' . $message . PHP_EOL;
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'easyit_phase8_' . bin2hex(random_bytes(4));
mkdir($tmp, 0777, true);
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'ed_ev.csv', "id|name\n42|Test\n");

try {
    $project = 'phase8-good';
    $df = 'ed_ev';
    $scope = $project . '|' . $df;
    $csvAdapter = new \EasyIT\Assistant\DataSource\Adapters\CsvDataSourceAdapter($root);
    $runtime = ['path' => $tmp, 'delimiter' => '|', 'header' => true];
    $test = $csvAdapter->test($runtime);
    $discovery = $csvAdapter->discover($runtime);

    $store->put('datasource.configure', [
        'profile' => ['name' => 'project-main', 'driver' => 'csv'],
        'connection' => ['path' => $tmp, 'delimiter' => '|', 'header' => true],
        'test' => ['ok' => $test->isOk(), 'message' => $test->getMessage(), 'details' => $test->getDetails(), 'warnings' => [], 'testedAt' => gmdate('c')],
        'discovery' => $discovery,
        'selection' => ['sourceName' => 'ed_ev', 'sourceType' => 'table'],
    ], $project);

    $fields = [
        ['name' => 'id', 'label' => 'ID', 'type' => 'integer', 'required' => true, 'readOnly' => true, 'default' => null],
        ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'readOnly' => false, 'default' => null],
    ];
    $store->put('dataform.create', [
        'source' => ['profile' => 'project-main', 'driver' => 'csv', 'connection' => 'profile:project-main', 'name' => 'ed_ev'],
        'identity' => ['dataFormName' => $df, 'primaryKey' => 'id'],
        'features' => ['fullTextSearch' => true, 'filter' => true, 'pagination' => ['enabled' => true, 'position' => 'below-records', 'pageSize' => 20, 'windowLeft' => 2, 'windowRight' => 2, 'showFirst' => true, 'showLast' => true]],
        'crud' => ['create' => true, 'show' => true, 'edit' => true, 'delete' => true, 'save' => true],
        'fields' => $fields,
    ], $project);
    $store->put('dataform.fields', [
        'context' => ['dataForm' => $df, 'sourceProfile' => 'project-main', 'sourceName' => 'ed_ev', 'primaryKey' => 'id'],
        'fields' => $fields,
        'lookups' => [],
        'derivedEnums' => [],
    ], $scope);
    $store->put('dataform.relations', [
        'relation' => ['name' => 'ev_info', 'type' => 'one_to_many'],
        'parent' => ['dataForm' => $df, 'source' => 'ed_ev', 'keyField' => 'id'],
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
        'dataForm' => ['name' => $df],
        'actions' => ['new' => true, 'show' => true, 'edit' => true, 'save' => true, 'delete' => true, 'first' => true, 'previous' => true, 'next' => true, 'last' => true],
        'ui' => ['buttonRegistry' => 'central', 'localTitlesAllowed' => false, 'localAriaLabelsAllowed' => false, 'cssBackgroundButtonsAllowed' => false],
    ], $scope);

    ok8($manager->getRegistry()->has('dataform.diagnostics'), 'Diagnose-Assistent ist zentral registriert.');
    $r = $manager->run('dataform.diagnostics', ctx8($project, $df, ['dataform_name' => $df, 'runtime_datasource' => '1', 'sample_parent_value' => '42']), 'context');
    ok8($r->isOk(), 'Diagnose-Kontext kann gespeichert werden.');
    $r = $manager->run('dataform.diagnostics', ctx8($project, $df, [], 'GET'), 'report');
    ok8($r->isOk(), 'Diagnosebericht wird erzeugt.');
    $report = $r->getData()['diagnosticReport'];
    $array = $report->jsonSerialize();
    ok8(($array['verdict'] ?? '') === 'PASS', 'Vollständig gültige Konfiguration erhält PASS.');
    ok8(($array['counts']['fail'] ?? -1) === 0, 'Gültige Konfiguration hat keine FAIL-Prüfung.');
    $checks = [];
    foreach ($array['checks'] as $check) { $checks[$check['id']] = $check; }
    ok8(($checks['datasource.runtime']['status'] ?? '') === 'pass', 'CSV-Datenquelle wird real erneut getestet.');
    ok8(($checks['relation.runtime']['details']['resolved']['values']['to_ev_id'] ?? null) === 42, 'Gebundener Elternwert id=42 wird als to_ev_id=42 geprüft.');
    ok8(($checks['events.context']['status'] ?? '') === 'pass', 'DataFormActionContext besteht Laufzeitprüfung.');
    ok8(($checks['buttons.registry']['status'] ?? '') === 'pass', 'Zentrale Button-Registry besteht Diagnose.');
    ok8(($checks['crud.crosscheck']['status'] ?? '') === 'pass', 'CRUD und Aktionsbuttons sind konsistent.');
    ok8(($checks['crud.workflow']['status'] ?? '') === 'pass', 'Nichtdestruktiver CRUD-Workflow-Test besteht.');

    // Deliberately broken project to verify concrete failures.
    $bad = 'phase8-bad';
    $badScope = $bad . '|badform';
    $store->put('dataform.create', [
        'source' => ['driver' => 'csv', 'name' => 'missing'],
        'identity' => ['dataFormName' => 'badform', 'primaryKey' => 'id'],
        'features' => ['pagination' => ['enabled' => false, 'position' => 'above-records', 'pageSize' => 20, 'windowLeft' => 1, 'windowRight' => 1, 'showFirst' => false, 'showLast' => false]],
        'crud' => ['create' => true, 'show' => true, 'edit' => true, 'delete' => true, 'save' => true],
        'fields' => [['name' => 'id', 'label' => 'ID', 'type' => 'integer']],
    ], $bad);
    $store->put('dataform.actions', [
        'dataForm' => ['name' => 'badform'],
        'actions' => ['new' => false, 'show' => true, 'edit' => true, 'save' => true, 'delete' => true],
        'ui' => ['buttonRegistry' => 'central', 'localTitlesAllowed' => false, 'localAriaLabelsAllowed' => false, 'cssBackgroundButtonsAllowed' => false],
    ], $badScope);
    $manager->run('dataform.diagnostics', ctx8($bad, 'badform', ['dataform_name' => 'badform', 'sample_parent_value' => '99']), 'context');
    $badResult = $manager->run('dataform.diagnostics', ctx8($bad, 'badform', [], 'GET'), 'report');
    $badReport = $badResult->getData()['diagnosticReport']->jsonSerialize();
    ok8(($badReport['verdict'] ?? '') === 'FAIL', 'Defekte Konfiguration erhält FAIL.');
    $badChecks = [];
    foreach ($badReport['checks'] as $check) { $badChecks[$check['id']] = $check; }
    ok8(($badChecks['dataform.pagination']['status'] ?? '') === 'fail', 'Falsche Pagination wird konkret als FAIL gemeldet.');
    ok8(($badChecks['crud.crosscheck']['status'] ?? '') === 'fail', 'CRUD/Button-Widerspruch wird konkret als FAIL gemeldet.');
    ok8(($badChecks['datasource.state']['status'] ?? '') === 'fail', 'Fehlendes Datenquellenprofil wird konkret als FAIL gemeldet.');

    echo "PHASE8_SMOKE=PASS\n";
} finally {
    foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: [] as $file) { @unlink($file); }
    @rmdir($tmp);
}
