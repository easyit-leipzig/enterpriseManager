<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/system/assistant/AssistantInterface.php';
require_once $root . '/system/assistant/AssistantContext.php';
require_once $root . '/system/assistant/AssistantStep.php';
require_once $root . '/system/assistant/AssistantResult.php';
require_once $root . '/system/assistant/state/AssistantStateStore.php';
require_once $root . '/system/assistant/project/ProjectDraft.php';
require_once $root . '/system/assistant/project/ProjectDraftValidator.php';
require_once $root . '/system/assistant/project/ProjectConfigCompiler.php';
require_once $root . '/system/assistant/project/ProjectProvisioningResult.php';
require_once $root . '/system/assistant/project/ProjectProvisioner.php';
require_once $root . '/system/assistant/assistants/ProjectAssistant.php';

use EasyIT\Assistant\AssistantContext;
use EasyIT\Assistant\Assistants\ProjectAssistant;
use EasyIT\Assistant\Project\ProjectConfigCompiler;
use EasyIT\Assistant\Project\ProjectDraftValidator;
use EasyIT\Assistant\Project\ProjectProvisioner;
use EasyIT\Assistant\State\AssistantStateStore;

function ctx(array $input, string $method = 'POST'): AssistantContext {
    return new AssistantContext(null, null, null, '/admin/assistants/run.php', $input, ['requestMethod' => $method]);
}
function assertTrue(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
}
function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) { $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); }
    rmdir($dir);
}

$tmp = sys_get_temp_dir() . '/easyit_phase9_' . bin2hex(random_bytes(4));
mkdir($tmp, 0775, true);
$store = new AssistantStateStore('phase9_test');
$assistant = new ProjectAssistant($store, new ProjectDraftValidator(), new ProjectConfigCompiler(), new ProjectProvisioner($tmp));

try {
    $r = $assistant->run(ctx(['project_name' => 'Muster CSV', 'project_slug' => '', 'description' => 'Phase 9']), 'identity');
    assertTrue($r->isOk(), 'identity accepted');
    assertTrue(($r->getData()['draft']->toArray()['identity']['slug'] ?? '') === 'muster-csv', 'slug derived');

    $r = $assistant->run(ctx(['driver' => 'csv']), 'storage');
    assertTrue($r->isOk(), 'csv storage accepted');
    $draft = $r->getData()['draft']->toArray();
    assertTrue($draft['storage']['projectPath'] === 'projects/muster-csv', 'project path derived');
    assertTrue($draft['dataSource']['localPath'] === 'projects/muster-csv/data/csv/muster-csv', 'csv path derived');

    $r = $assistant->run(ctx(['profile_name' => 'main', 'database_name' => '']), 'datasource');
    assertTrue($r->isOk(), 'datasource profile accepted');
    $r = $assistant->run(ctx([], 'GET'), 'structure');
    assertTrue($r->isOk(), 'structure visible');

    $r = $assistant->run(ctx(['confirm_create' => '1']), 'provision');
    assertTrue($r->isOk(), 'project provisioned');
    $draft = $r->getData()['draft']->toArray();
    assertTrue($draft['provision']['created'] === true, 'created flag');
    $projectPath = $tmp . '/projects/muster-csv';
    assertTrue(is_dir($projectPath . '/config'), 'config dir created');
    assertTrue(is_dir($projectPath . '/data/csv/muster-csv'), 'csv data dir created');
    assertTrue(is_file($projectPath . '/config/project.json'), 'project json created');
    $projectJson = json_decode((string) file_get_contents($projectPath . '/config/project.json'), true, 512, JSON_THROW_ON_ERROR);
    assertTrue(($projectJson['provisioning']['created'] ?? false) === true, 'project json marks provisioning created');
    assertTrue(is_file($projectPath . '/config/datasource.json'), 'datasource json created');
    $json = file_get_contents($projectPath . '/config/datasource.json');
    assertTrue(is_string($json) && !str_contains(strtolower($json), 'password"'), 'no plaintext password field');
    assertTrue(is_string($json) && str_contains($json, 'passwordRef'), 'password reference policy retained');

    $seed = $store->get('datasource.configure', 'muster-csv');
    assertTrue(($seed['profile']['driver'] ?? '') === 'csv', 'datasource assistant seeded');
    assertTrue(($seed['connection']['path'] ?? '') === 'projects/muster-csv/data/csv/muster-csv', 'seed path correct');

    $r = $assistant->run(ctx([], 'GET'), 'review');
    assertTrue($r->isOk(), 'review passes after provision');
    assertTrue(($r->getData()['configurationReady'] ?? false) === true, 'configuration ready');
    assertTrue(str_contains((string) ($r->getData()['handoffUrl'] ?? ''), 'datasource.configure'), 'handoff datasource');
    $compiled = $r->getData()['compiledConfig'];
    assertTrue(($compiled['assistantFlow']['then'] ?? '') === 'dataform.create', 'flow to dataform declared');

    // Existing project must never be overwritten.
    $r = $assistant->run(ctx(['confirm_create' => '1']), 'provision');
    assertTrue(!$r->isOk(), 'second provision blocked');
    assertTrue(str_contains(implode(' ', $r->getErrors()), 'existiert bereits'), 'existing project message');

    // SQLite project gets a local database file seed.
    $assistant->run(ctx(['reset' => '1'], 'GET'), 'identity');
    $assistant->run(ctx(['project_name' => 'SQLite Demo', 'project_slug' => 'sqlite-demo', 'description' => '']), 'identity');
    $assistant->run(ctx(['driver' => 'sqlite']), 'storage');
    $assistant->run(ctx(['profile_name' => 'main', 'database_name' => '']), 'datasource');
    $r = $assistant->run(ctx(['confirm_create' => '1']), 'provision');
    assertTrue($r->isOk(), 'sqlite project provisioned');
    assertTrue(is_file($tmp . '/projects/sqlite-demo/data/sqlite-demo.sqlite'), 'sqlite file created');

    // Invalid traversal-like slug must fail.
    $assistant->run(ctx(['reset' => '1'], 'GET'), 'identity');
    $r = $assistant->run(ctx(['project_name' => 'Bad', 'project_slug' => '../bad', 'description' => '']), 'identity');
    assertTrue(!$r->isOk(), 'invalid slug rejected');

    echo "PHASE9_SMOKE=PASS\n";
    echo "PROJECT_PATH={$projectPath}\n";
    echo "HANDOFF=datasource.configure -> dataform.create\n";
} finally {
    rrmdir($tmp);
}
