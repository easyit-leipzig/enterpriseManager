<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/system/assistant/state/AssistantStateSanitizer.php';
require_once $root . '/system/assistant/template/DataFormTemplateStore.php';
require_once $root . '/system/assistant/template/DataFormTemplateLibraryService.php';

use EasyIT\Assistant\Template\DataFormTemplateStore;
use EasyIT\Assistant\Template\DataFormTemplateLibraryService;

function ok(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    echo "PASS: $message\n";
}
function rrmdir(string $dir): void {
    if (!is_dir($dir)) { return; }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $file) { $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname()); }
    @rmdir($dir);
}

$tmp = sys_get_temp_dir() . '/easyit-phase16-' . bin2hex(random_bytes(4));
@mkdir($tmp . '/projects/p1/config', 0770, true);
@mkdir($tmp . '/projects/p2/config', 0770, true);
try {
    $store = new DataFormTemplateStore($tmp);
    $library = new DataFormTemplateLibraryService($store, $tmp);
    $base = [
        'id'=>'base-template','name'=>'Basis','description'=>'Systemvorlage',
        'source'=>['projectId'=>'p1','dataFormId'=>'ed_ev'],
        'bundle'=>['dataform.create'=>['identity'=>['dataFormName'=>'ed_ev'],'source'=>['profile'=>'main','name'=>'ed_ev']]],
    ];
    $system = $store->save($base, 'system', null);
    ok(($system['library']['visibility'] ?? '') === 'system', 'bestehende/systemweite Vorlage wird als systemweit gespeichert');
    ok(count($library->list(null)) === 1, 'globale Bibliothek zeigt systemweite Vorlage');

    $copy = $library->copy('base-template', 'Projektkopie', 'system', null, 'project', 'p1');
    ok(($copy['library']['visibility'] ?? '') === 'project' && ($copy['library']['ownerProject'] ?? '') === 'p1', 'Kopie kann projektbezogen freigegeben werden');
    ok(count($library->list(null)) === 1, 'projektbezogene Vorlage ist ohne Projektkontext nicht sichtbar');
    ok(count($library->list('p1')) === 2, 'Projektkontext sieht systemweite und eigene Projektvorlage');
    ok(count($library->list('p2')) === 1, 'anderes Projekt sieht fremde Projektvorlage nicht');

    $copyId = (string)$copy['id'];
    $renamed = $library->rename($copyId, 'Projektkopie Neu', 'Neue Beschreibung', 'project', 'p1');
    ok(($renamed['name'] ?? '') === 'Projektkopie Neu', 'Vorlage kann umbenannt werden');
    $history = $library->history($copyId, 'project', 'p1');
    ok(count($history) >= 2, 'Umbenennen erzeugt versionierten Verlauf');

    $moved = $library->changeVisibility($copyId, 'project', 'p1', 'system', null);
    ok(($moved['library']['visibility'] ?? '') === 'system', 'Projektvorlage kann systemweit freigegeben werden');
    ok($store->getInScope($copyId, 'project', 'p1') === [], 'alte projektbezogene Datei wird nach Freigabewechsel entfernt');
    ok($store->getInScope($copyId, 'system', null) !== [], 'Vorlage liegt nach Freigabewechsel systemweit vor');
    ok(count($library->history($copyId, 'system', null)) >= count($history), 'Versionsverlauf bleibt beim Freigabewechsel erhalten');

    $export = $library->exportJson($copyId, null);
    $exportPayload = json_decode($export, true);
    ok(is_array($exportPayload) && ($exportPayload['schema'] ?? '') === 'easyit.assistant.dataform-template.v1', 'Export liefert gültiges Vorlagen-JSON');

    $importPayload = $exportPayload;
    $importPayload['id'] = 'unsafe-import-id';
    $importPayload['name'] = 'Import';
    $importPayload['secret'] = 'NICHT-SPEICHERN';
    $importPayload['bundle']['dataform.create']['password'] = 'GEHEIM';
    $imported = $library->importJson((string)json_encode($importPayload), 'project', 'p2', 'Import P2');
    ok(($imported['library']['ownerProject'] ?? '') === 'p2', 'Import kann projektbezogen erfolgen');
    ok(!array_key_exists('secret', $imported), 'Secret wird beim Import entfernt');
    ok(!array_key_exists('password', $imported['bundle']['dataform.create'] ?? []), 'Passwort wird beim Import rekursiv entfernt');

    $failed = false;
    try { $library->softDelete((string)$imported['id'], 'project', 'p2', 'falscher Name'); } catch (Throwable) { $failed = true; }
    ok($failed, 'kontrolliertes Löschen verlangt den exakten Vorlagennamen');
    $deleted = $library->softDelete((string)$imported['id'], 'project', 'p2', 'Import P2');
    ok(!empty($deleted['deleted']), 'bestätigte Vorlage wird in den Papierkorb verschoben');
    ok($store->getInScope((string)$imported['id'], 'project', 'p2') === [], 'gelöschte Vorlage ist nicht mehr in der aktiven Bibliothek');
    ok(is_dir($tmp . '/projects/p2/config/assistant/templates/.trash'), 'projektbezogener Vorlagen-Papierkorb wurde erzeugt');

    // Bootstrap-Registry muss Phase 16 enthalten.
    $manager = require $root . '/system/assistant/bootstrap.php';
    ok($manager->getRegistry()->has('dataform.template-library'), 'Phase-16-Assistent ist zentral registriert');
    echo "PHASE16_SMOKE_PASS\n";
} finally {
    rrmdir($tmp);
}
