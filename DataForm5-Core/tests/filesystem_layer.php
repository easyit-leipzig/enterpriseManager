<?php
declare(strict_types=1);

use DataForm5\Core\Filesystem\Filesystem;
use DataForm5\Core\Filesystem\FilesystemException;
use DataForm5\Core\Filesystem\Storage;
use DataForm5\Core\Filesystem\BackupManager;

$kernel = require dirname(__DIR__) . '/bootstrap/app.php';
$container = $kernel->container();
$storage = $container->get(Storage::class);
$filesystem = $container->get(Filesystem::class);
$backup = $container->get(BackupManager::class);

$storage->delete('phase3-test');
$storage->write('phase3-test/a.txt', 'alpha');
$storage->append('phase3-test/a.txt', '-beta');
assert($storage->read('phase3-test/a.txt') === 'alpha-beta');
$storage->copy('phase3-test/a.txt', 'phase3-test/b.txt');
$storage->move('phase3-test/b.txt', 'phase3-test/c.txt');
assert($storage->exists('phase3-test/c.txt'));
assert(count($storage->files('phase3-test', true)) === 2);
assert(strlen($filesystem->checksum($storage->path('phase3-test/a.txt'))) === 64);

$blocked = false;
try { $storage->write('../escape.txt', 'no'); } catch (FilesystemException) { $blocked = true; }
assert($blocked === true);

$backupPath = $backup->create($storage->path('phase3-test'), 'phase3-test', false);
assert(is_dir($backupPath));
assert(is_file($backupPath . '/a.txt'));
$filesystem->delete($backupPath);
$storage->delete('phase3-test');

echo "PASS: Filesystem Layer\n";
