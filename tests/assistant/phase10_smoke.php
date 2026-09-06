<?php
declare(strict_types=1);

require_once __DIR__ . '/../../system/assistant/recovery/ArchiveOperationResult.php';
require_once __DIR__ . '/../../system/assistant/recovery/RecoveryCheck.php';
require_once __DIR__ . '/../../system/assistant/recovery/RecoveryReport.php';
require_once __DIR__ . '/../../system/assistant/recovery/ProjectRecoveryDiagnosticService.php';
require_once __DIR__ . '/../../system/assistant/recovery/ProjectArchiveService.php';

use EasyIT\Assistant\Recovery\ProjectArchiveService;

function fail10(string $message): never { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
function ok10(bool $condition, string $message): void { if (!$condition) { fail10($message); } echo "PASS: $message\n"; }
function rrmdir10(string $dir): void {
    if (!is_dir($dir)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $i) { $i->isDir() ? @rmdir($i->getPathname()) : @unlink($i->getPathname()); }
    @rmdir($dir);
}

$root = sys_get_temp_dir() . '/easyit_phase10_' . bin2hex(random_bytes(4));
$project = $root . '/projects/muster-csv';
foreach (['config','data/csv/muster-csv','storage','logs','backups'] as $dir) { @mkdir($project . '/' . $dir, 0775, true); }
file_put_contents($project . '/config/project.json', json_encode([
    'schema' => 'easyit.project.assistant.v1',
    'project' => ['name' => 'Muster CSV', 'id' => 'muster-csv', 'path' => 'projects/muster-csv'],
    'storage' => ['driver' => 'csv', 'mode' => 'local', 'localPath' => 'projects/muster-csv/data/csv/muster-csv'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($project . '/config/datasource.json', json_encode([
    'profile' => ['name' => 'main', 'driver' => 'csv'],
    'connection' => ['path' => 'projects/muster-csv/data/csv/muster-csv', 'delimiter' => '|', 'header' => true],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($project . '/data/csv/muster-csv/ed_ev.csv', "id|name\n1|Test\n");
file_put_contents($project . '/README.md', "# Muster CSV\n");
file_put_contents($project . '/backups/old.zip', 'old');

$svc = new ProjectArchiveService($root);
$backup = $svc->backup('muster-csv', true, false);
ok10($backup->isOk(), 'Backup mit lokaler Datenbank wird erzeugt.');
ok10(is_file((string) $backup->getPath()), 'Backup-ZIP existiert.');
ok10(is_file((string) $backup->getPath() . '.sha256'), 'Archiv-SHA-Datei existiert.');
$inspect = $svc->inspect((string) $backup->getPath());
ok10($inspect['ok'] === true, 'Backup-Manifest und Datei-Prüfsummen sind gültig.');
ok10(($inspect['manifest']['database']['included'] ?? false) === true, 'CSV-Projektdatenbank ist im Backup markiert.');
ok10(isset($inspect['manifest']['files']['project/data/csv/muster-csv/ed_ev.csv']), 'CSV-Datenbankdatei ist im Backup enthalten.');
ok10(!isset($inspect['manifest']['files']['project/backups/old.zip']), 'Alte interne Backups werden standardmäßig nicht rekursiv eingebettet.');

$conflict = $svc->restore((string) $backup->getPath());
ok10(!$conflict->isOk() && str_contains($conflict->getMessage(), 'Restore-Konflikt'), 'Restore überschreibt vorhandenes Projekt nicht.');

$restored = $svc->restore((string) $backup->getPath(), 'muster-restore');
ok10($restored->isOk(), 'Restore unter neuer Projekt-ID ist erfolgreich.');
ok10(is_file($root . '/projects/muster-restore/data/csv/muster-csv/ed_ev.csv'), 'Gesicherte CSV-Datei wurde wiederhergestellt.');
$pj = json_decode((string) file_get_contents($root . '/projects/muster-restore/config/project.json'), true);
$ds = json_decode((string) file_get_contents($root . '/projects/muster-restore/config/datasource.json'), true);
ok10(($pj['project']['id'] ?? '') === 'muster-restore', 'project.json wird auf neue Projekt-ID retargetet.');
ok10(($pj['project']['path'] ?? '') === 'projects/muster-restore', 'Projektpfad wird retargetet.');
ok10(($ds['connection']['path'] ?? '') === 'projects/muster-restore/data/csv/muster-csv', 'Datenquellenpfad wird auf neues Projekt retargetet.');
$report = $restored->getDetails()['diagnosticReport'] ?? [];
ok10(($report['verdict'] ?? '') === 'PASS', 'Recovery-Diagnose endet mit PASS.');

$backupNoDb = $svc->backup('muster-csv', false, false);
ok10($backupNoDb->isOk(), 'Backup ohne Projektdatenbank wird erzeugt.');
$inspectNoDb = $svc->inspect((string) $backupNoDb->getPath());
ok10(($inspectNoDb['manifest']['database']['included'] ?? true) === false, 'Datenbank ist bei deaktivierter Option nicht enthalten.');
ok10(!isset($inspectNoDb['manifest']['files']['project/data/csv/muster-csv/ed_ev.csv']), 'CSV-Datenbankdatei wird tatsächlich ausgelassen.');

$bad = $root . '/bad.zip';
$ph = new PharData($bad, 0, null, Phar::ZIP);
$payload = "x";
$ph->addFromString('project/good.txt', $payload);
$ph->addFromString('easyit-backup-manifest.json', json_encode([
    'schema' => 'easyit.project.backup.v1', 'projectId' => 'bad-project',
    'files' => ['project/../evil.txt' => ['sha256' => hash('sha256', $payload), 'size' => 1]],
]));
unset($ph);
$badInspect = $svc->inspect($bad);
ok10($badInspect['ok'] === false, 'Unsicherer ../-Pfad im Manifest wird abgelehnt.');

rrmdir10($root);
echo "PHASE10_SMOKE_PASS\n";
