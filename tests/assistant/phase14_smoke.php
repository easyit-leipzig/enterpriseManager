<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/system/assistant/bootstrap.php';

function ok14(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    echo "PASS: $message\n";
}
function rm14(string $dir): void {
    if (!is_dir($dir)) { return; }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) { $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname()); }
    @rmdir($dir);
}

$project = 'phase14-good';
$restored = 'phase14-restored';
$projectDir = $root . '/projects/' . $project;
$restoredDir = $root . '/projects/' . $restored;
rm14($projectDir); rm14($restoredDir);
@mkdir($projectDir . '/config', 0770, true);
@mkdir($projectDir . '/data/csv/' . $project, 0770, true);
@mkdir($projectDir . '/storage', 0770, true);
@mkdir($projectDir . '/logs', 0770, true);
@mkdir($projectDir . '/backups', 0770, true);
file_put_contents($projectDir . '/config/project.json', json_encode([
    'schema' => 'easyit.project.v1',
    'id' => $project,
    'name' => 'Phase 14 Test',
    'storage' => ['driver' => 'csv', 'localPath' => 'projects/' . $project . '/data/csv/' . $project],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents($projectDir . '/config/datasource.json', json_encode([
    'schema' => 'easyit.datasource.v1',
    'profile' => ['name' => 'project-main', 'driver' => 'csv'],
    'connection' => ['path' => 'projects/' . $project . '/data/csv/' . $project],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents($projectDir . '/data/csv/' . $project . '/ed_ev.csv', "id|name\n42|Test\n");

try {
    $store = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    $scope = $project;
    $v1 = [
        'profile' => ['name' => 'project-main', 'driver' => 'csv'],
        'connection' => ['path' => 'projects/' . $project . '/data/csv/' . $project, 'password' => 'NEVER_IN_HISTORY', 'passwordRef' => 'ENV_DB_PASS'],
        'selection' => ['sourceName' => 'ed_ev', 'sourceType' => 'table'],
    ];
    $v2 = $v1; $v2['selection']['sourceName'] = 'ed_ev_info';
    $v3 = $v1; $v3['selection']['sourceName'] = 'ed_ev_branch';

    $store->put('datasource.configure', $v1, $scope);
    $history1 = $store->history('datasource.configure', $project, $scope);
    ok14(count($history1['activePath'] ?? []) === 2, 'Erste Änderung erzeugt Baseline plus erste Version.');
    ok14(($history1['canUndo'] ?? false) === true, 'Erste gespeicherte Änderung kann auf leere Baseline zurückgesetzt werden.');

    $store->put('datasource.configure', $v2, $scope);
    $history2 = $store->history('datasource.configure', $project, $scope);
    ok14(count($history2['activePath'] ?? []) === 3, 'Zweite Änderung erzeugt eine weitere aktive Version.');
    $v1Id = (string) ($history2['activePath'][1] ?? '');
    $v2Id = (string) ($history2['activePath'][2] ?? '');
    $diff = $store->compareHistory('datasource.configure', $project, $scope, $v1Id, $v2Id);
    ok14(($diff['ok'] ?? false) === true && ($diff['count'] ?? 0) >= 1, 'Vorher/Nachher-Vergleich erkennt Änderungen.');
    $paths = array_map(static fn(array $row): string => (string) ($row['path'] ?? ''), $diff['changes'] ?? []);
    ok14(in_array('$.selection.sourceName', $paths, true), 'Vergleich nennt den konkret geänderten JSON-Pfad.');

    $undo = $store->undo('datasource.configure', $project, $scope);
    ok14($undo['ok'] === true && ($undo['state']['selection']['sourceName'] ?? '') === 'ed_ev', 'Undo stellt den vorherigen Zustand her.');
    $reload = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    ok14(($reload->get('datasource.configure', $scope)['selection']['sourceName'] ?? '') === 'ed_ev', 'Undo ist persistent und nach neuem Store sichtbar.');

    $redo = $store->redo('datasource.configure', $project, $scope);
    ok14($redo['ok'] === true && ($redo['state']['selection']['sourceName'] ?? '') === 'ed_ev_info', 'Redo stellt den rückgängig gemachten Zustand wieder her.');

    $store->undo('datasource.configure', $project, $scope);
    $store->put('datasource.configure', $v3, $scope);
    $branched = $store->history('datasource.configure', $project, $scope);
    ok14(($branched['canRedo'] ?? true) === false, 'Neue Änderung nach Undo deaktiviert Redo in den alten Zweig.');
    $detachedIds = [];
    foreach (($branched['entries'] ?? []) as $entry) { if (!empty($entry['detached'])) { $detachedIds[] = (string) ($entry['id'] ?? ''); } }
    ok14(in_array($v2Id, $detachedIds, true), 'Alter Redo-Zweig bleibt als abgelöste Historie nachvollziehbar.');

    $restore = $store->restoreHistoryVersion('datasource.configure', $project, $scope, $v2Id);
    ok14($restore['ok'] === true && ($restore['state']['selection']['sourceName'] ?? '') === 'ed_ev_info', 'Beliebige frühere Version kann kontrolliert wiederhergestellt werden.');
    $afterRestore = $store->history('datasource.configure', $project, $scope);
    $currentMeta = null;
    foreach (($afterRestore['entries'] ?? []) as $entry) { if (!empty($entry['current'])) { $currentMeta = $entry; break; } }
    ok14(is_array($currentMeta) && ($currentMeta['action'] ?? '') === 'restore' && ($currentMeta['sourceVersionId'] ?? '') === $v2Id, 'Restore wird als neue Version mit Quellversion protokolliert.');

    $store->clear('datasource.configure', $scope);
    $afterClear = $store->history('datasource.configure', $project, $scope);
    ok14(($store->getForProject('datasource.configure', $project, $scope)) === [], 'Reset löscht den aktuellen persistenten Zustand.');
    ok14(($afterClear['canUndo'] ?? false) === true, 'Auch Reset/Clear ist rückgängig machbar.');
    $undoClear = $store->undo('datasource.configure', $project, $scope);
    ok14($undoClear['ok'] === true && ($undoClear['state']['selection']['sourceName'] ?? '') === 'ed_ev_info', 'Undo nach Reset stellt den letzten Zustand wieder her.');

    $historyDir = $projectDir . '/config/assistant/history';
    $historyRaw = '';
    foreach (glob($historyDir . '/*/versions/*.json') ?: [] as $file) { $historyRaw .= (string) file_get_contents($file); }
    ok14(!str_contains($historyRaw, 'NEVER_IN_HISTORY') && !str_contains($historyRaw, '"password"'), 'Klartextkennwort wird auch aus Historienversionen entfernt.');
    ok14(str_contains($historyRaw, 'ENV_DB_PASS'), 'Sichere passwordRef bleibt in Historienversionen erhalten.');

    // Backup/restore must carry and retarget history.
    $archives = new \EasyIT\Assistant\Recovery\ProjectArchiveService($root);
    $backup = $archives->backup($project, true, false);
    ok14($backup->isOk() && is_file((string) $backup->getPath()), 'Projektbackup enthält die Historie.');
    $rest = $archives->restore((string) $backup->getPath(), $restored);
    ok14($rest->isOk(), 'Projekt mit Historie kann unter neuer ID restauriert werden.');
    $restoredStore = new \EasyIT\Assistant\State\AssistantStateStore('easyit_assistant', $root);
    $restoredHistory = $restoredStore->history('datasource.configure', $restored, $restored);
    ok14(($restoredHistory['projectId'] ?? '') === $restored && ($restoredHistory['scope'] ?? '') === $restored, 'Restore retargetet Historienindex und Scope auf neue Projekt-ID.');
    ok14(count($restoredHistory['entries'] ?? []) >= 2, 'Historienversionen bleiben nach Restore verfügbar.');
    $restState = $restoredStore->get('datasource.configure', $restored);
    ok14(str_contains((string) ($restState['connection']['path'] ?? ''), 'projects/' . $restored . '/'), 'Aktueller Assistentenzustand wird beim Restore retargetet.');

    echo "PHASE14_SMOKE=PASS\n";
} finally {
    rm14($projectDir); rm14($restoredDir);
    $backupRoot = $root . '/backups/projects';
    if (is_dir($backupRoot)) { foreach (glob($backupRoot . '/phase14-good-*') ?: [] as $f) { @unlink($f); } }
}
