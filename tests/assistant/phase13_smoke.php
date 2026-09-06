<?php
declare(strict_types=1);

session_start();
$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';
$store = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);

function ok13(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    echo 'PASS: ' . $message . PHP_EOL;
}
function ctx13(?string $project, ?string $dataForm, array $input = [], string $method = 'GET'): \EasyIT\Assistant\AssistantContext
{
    return new \EasyIT\Assistant\AssistantContext($project, $dataForm, null, null, $input, ['requestMethod' => $method]);
}
function rm13(string $path): void
{
    if (!file_exists($path) && !is_link($path)) { return; }
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') { continue; }
        rm13($path . DIRECTORY_SEPARATOR . $entry);
    }
    @rmdir($path);
}

$project = 'phase13-good';
$restored = 'phase13-restored';
$dataForm = 'ed_ev';
$scope = $project . '|' . $dataForm;
$projectDir = $root . '/projects/' . $project;
$restoredDir = $root . '/projects/' . $restored;
$csvRel = 'projects/' . $project . '/data/csv/' . $project;
$csvDir = $root . '/' . $csvRel;
$activePointer = $root . '/storage/assistant/active-standard-workflow.json';

rm13($projectDir);
rm13($restoredDir);
@unlink($activePointer);
@mkdir($projectDir . '/config', 0777, true);
@mkdir($csvDir, 0777, true);
file_put_contents($projectDir . '/config/project.json', json_encode([
    'project' => ['id' => $project, 'path' => 'projects/' . $project],
    'storage' => ['driver' => 'csv', 'localPath' => $csvRel],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($projectDir . '/config/datasource.json', json_encode([
    'profile' => ['name' => 'project-main', 'driver' => 'csv'],
    'connection' => ['path' => $csvRel, 'delimiter' => '|', 'header' => true],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($csvDir . '/ed_ev.csv', "id|name\n42|Test\n");

try {
    // Persistent state + sanitizer.
    $store->put('datasource.configure', [
        'profile' => ['name' => 'project-main', 'driver' => 'csv'],
        'connection' => ['path' => $csvRel, 'password' => 'NEVER_WRITE_ME', 'passwordRef' => 'EASYIT_DB_PASSWORD'],
        'selection' => ['sourceName' => 'ed_ev', 'sourceType' => 'table'],
    ], $project);
    $info = $store->persistenceInfo('datasource.configure', $project);
    ok13(($info['enabled'] ?? false) === true && ($info['exists'] ?? false) === true, 'Projektzustand wird zusätzlich persistent gespeichert.');
    $stateFile = $root . '/' . $info['relativePath'];
    $raw = (string) file_get_contents($stateFile);
    ok13(!str_contains($raw, 'NEVER_WRITE_ME') && !str_contains($raw, '"password"'), 'Klartextkennwort wird nicht persistent geschrieben.');
    ok13(str_contains($raw, 'EASYIT_DB_PASSWORD'), 'passwordRef bleibt als sichere Referenz persistent erhalten.');

    unset($_SESSION['easyit_assistant']);
    $freshStore = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    $loaded = $freshStore->get('datasource.configure', $project);
    ok13(($loaded['selection']['sourceName'] ?? '') === 'ed_ev', 'Assistentenzustand wird nach Sessionverlust vom Projektzustand geladen.');
    ok13(($freshStore->persistenceInfo('datasource.configure', $project)['lastReadSource'] ?? '') === 'project', 'Ladequelle wird als Projektpersistenz erkannt.');

    // Separate DataForms do not collide.
    $freshStore->put('dataform.fields', ['context' => ['dataForm' => 'ed_ev'], 'fields' => [['name' => 'id']]], $project . '|ed_ev');
    $freshStore->put('dataform.fields', ['context' => ['dataForm' => 'ed_person'], 'fields' => [['name' => 'person_id']]], $project . '|ed_person');
    unset($_SESSION['easyit_assistant']);
    $reload = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    ok13(($reload->get('dataform.fields', $project . '|ed_ev')['fields'][0]['name'] ?? '') === 'id', 'Persistenz trennt DataForm-Zustände nach Scope.');
    ok13(($reload->get('dataform.fields', $project . '|ed_person')['fields'][0]['name'] ?? '') === 'person_id', 'Zweiter DataForm-Scope bleibt unabhängig erhalten.');

    // Build a complete persistent workflow state.
    $fields = [
        ['name' => 'id', 'label' => 'ID', 'type' => 'integer', 'required' => true, 'readOnly' => true, 'default' => null],
        ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'readOnly' => false, 'default' => null],
    ];
    $reload->put('datasource.configure', [
        'profile' => ['name' => 'project-main', 'driver' => 'csv'],
        'connection' => ['path' => $csvRel, 'delimiter' => '|', 'header' => true, 'passwordRef' => ''],
        'test' => ['ok' => true, 'message' => 'OK', 'details' => [], 'warnings' => [], 'testedAt' => gmdate('c')],
        'discovery' => [['name' => 'ed_ev', 'label' => 'ed_ev', 'type' => 'table', 'meta' => ['valid' => true]]],
        'selection' => ['sourceName' => 'ed_ev', 'sourceType' => 'table'],
    ], $project);
    $reload->put('dataform.create', [
        'source' => ['profile' => 'project-main', 'driver' => 'csv', 'connection' => 'profile:project-main', 'name' => 'ed_ev'],
        'identity' => ['dataFormName' => $dataForm, 'primaryKey' => 'id'],
        'features' => ['fullTextSearch' => true, 'filter' => true, 'pagination' => ['enabled' => true, 'position' => 'below-records', 'pageSize' => 20, 'windowLeft' => 2, 'windowRight' => 2, 'showFirst' => true, 'showLast' => true]],
        'crud' => ['create' => true, 'show' => true, 'edit' => true, 'delete' => true, 'save' => true],
        'fields' => $fields,
    ], $project);
    $reload->put('dataform.fields', [
        'context' => ['dataForm' => $dataForm, 'sourceProfile' => 'project-main', 'sourceName' => 'ed_ev', 'primaryKey' => 'id'],
        'fields' => $fields, 'lookups' => [], 'derivedEnums' => [],
    ], $scope);
    $reload->put('dataform.relations', [
        'relation' => ['name' => 'ev_info', 'type' => 'one_to_many'],
        'parent' => ['dataForm' => $dataForm, 'source' => 'ed_ev', 'keyField' => 'id'],
        'child' => ['dataForm' => 'ed_ev_info', 'source' => 'ed_ev_info', 'foreignKeyField' => 'to_ev_id'],
        'binding' => ['enabled' => true, 'valueSource' => 'parent.currentRecord', 'parentValueField' => 'id', 'childTargetField' => 'to_ev_id', 'fillOnNewRecord' => true, 'readOnly' => true],
        'display' => ['paginationPosition' => 'below-records'],
    ], $scope);
    $reload->put('dataform.events', [
        'context' => ['objectName' => 'dataFormContext', 'schema' => 'easyit.dataform.action-context.v1'],
        'events' => [
            'beforeSave' => ['enabled' => true, 'handler' => 'beforeSave', 'blocking' => true],
            'afterSave' => ['enabled' => true, 'handler' => 'afterSave', 'blocking' => false],
            'beforeDelete' => ['enabled' => true, 'handler' => 'beforeDelete', 'blocking' => true],
            'afterDelete' => ['enabled' => true, 'handler' => 'afterDelete', 'blocking' => false],
        ],
        'runtime' => ['handlerResolution' => 'global-path', 'allowEval' => false, 'beforeEventFalseCancelsAction' => true, 'captureHandlerErrors' => true],
    ], $scope);
    $reload->put('dataform.actions', [
        'dataForm' => ['name' => $dataForm],
        'actions' => ['new' => true, 'show' => true, 'edit' => true, 'save' => true, 'delete' => true, 'first' => true, 'previous' => true, 'next' => true, 'last' => true],
        'ui' => ['buttonRegistry' => 'central', 'localTitlesAllowed' => false, 'localAriaLabelsAllowed' => false, 'cssBackgroundButtonsAllowed' => false],
    ], $scope);
    $reload->put('dataform.diagnostics', ['dataForm' => $dataForm, 'runtimeDataSource' => false, 'sampleParentValue' => 42], $project);

    $workflow = $manager->run('workflow.standard', ctx13($project, $dataForm));
    $flow = $workflow->getData()['workflow'];
    ok13(($flow['allComplete'] ?? false) === true && ($flow['percent'] ?? 0) === 100, 'Vollständiger Workflow wird persistent mit 100 Prozent gespeichert.');
    ok13(($flow['persistence']['resumableAcrossSessions'] ?? false) === true, 'Workflow meldet sitzungsübergreifende Wiederaufnahmefähigkeit.');
    ok13(is_file($activePointer), 'Aktiver Workflow erhält einen minimalen globalen Wiederaufnahmezeiger.');
    $navPersistence = new \EasyIT\Assistant\Workflow\WorkflowPersistenceService(new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root), $root);
    $navPersistence->recordAssistantStep($project, $dataForm, 'dataform.fields', 'derived_enum');

    // Simulate a new browser session and a fresh bootstrap, then resume with no query context.
    unset($_SESSION['easyit_assistant']);
    /** @var \EasyIT\Assistant\AssistantManager $manager2 */
    $manager2 = require $root . '/system/assistant/bootstrap.php';
    $resumed = $manager2->run('workflow.standard', ctx13(null, null));
    $resumedFlow = $resumed->getData()['workflow'];
    ok13(($resumedFlow['projectId'] ?? '') === $project, 'Neue Sitzung nimmt das zuletzt aktive Projekt ohne Query-Parameter wieder auf.');
    ok13(($resumedFlow['dataFormId'] ?? '') === $dataForm, 'Neue Sitzung nimmt das zuletzt aktive DataForm wieder auf.');
    ok13(($resumedFlow['allComplete'] ?? false) === true, 'Workflow-Fortschritt bleibt nach vollständigem Sessionverlust erhalten.');
    $fieldRow = null;
    foreach (($resumedFlow['steps'] ?? []) as $row) { if (($row['id'] ?? '') === 'fields') { $fieldRow = $row; break; } }
    ok13(is_array($fieldRow) && str_contains((string) ($fieldRow['url'] ?? ''), 'step=derived_enum'), 'Workflow merkt sich auch den zuletzt geöffneten Teilschritt eines Fachassistenten.');

    // Backup/restore under a new project id must retarget persistent state metadata and scope hashes.
    $archives = new \EasyIT\Assistant\Recovery\ProjectArchiveService($root);
    $backup = $archives->backup($project, true, false);
    ok13($backup->isOk() && is_file((string) $backup->getPath()), 'Projektbackup enthält den persistenten Assistentenzustand.');
    $restore = $archives->restore((string) $backup->getPath(), $restored);
    ok13($restore->isOk(), 'Restore unter neuer Projekt-ID retargetet auch Assistentenzustände.');
    $restoredStore = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    unset($_SESSION['easyit_assistant']);
    $restoredDataForm = $restoredStore->get('dataform.create', $restored);
    ok13(($restoredDataForm['identity']['dataFormName'] ?? '') === $dataForm, 'Retargeteter persistenter DataForm-Zustand ist unter neuer Projekt-ID ladbar.');

    echo "PHASE13_SMOKE=PASS\n";
} finally {
    rm13($projectDir);
    rm13($restoredDir);
    @unlink($activePointer);
    $backupRoot = $root . '/storage/backups';
    if (is_dir($backupRoot)) {
        foreach (glob($backupRoot . '/*phase13-good*') ?: [] as $f) { @unlink($f); }
    }
}
