<?php
declare(strict_types=1);
require dirname(__DIR__) . '/autoload.php';

use DataForm\Database\Contracts\RelationalDatabaseInterface;
use DataForm\Database\Core\DatabaseManager;
use DataForm\Database\Core\QueryBuilder;
use DataForm\Database\Core\RelationManager;
use DataForm\Database\Core\SchemaBuilder;

$root = dirname(__DIR__, 3);
$testStorage = $root . '/storage/test-runtime';
if (is_dir($testStorage)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testStorage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) { $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); }
    rmdir($testStorage);
}
$config = ['default' => ['driver' => 'csv', 'base_path' => $testStorage, 'database' => 'core_test']];
$manager = new DatabaseManager($config);
$db = $manager->connection();
$schema = new SchemaBuilder($db);
$schema->create('users', ['name']);
$schema->create('roles', ['name']);
$users = new QueryBuilder($db, 'users');
$roles = new QueryBuilder($db, 'roles');
$uid = $users->insert(['name' => 'Olaf']);
$rid = $roles->insert(['name' => 'Admin']);
if (!$db instanceof RelationalDatabaseInterface) throw new RuntimeException('CSV adapter must be relational.');
$relations = new RelationManager($db);
$relations->manyToMany('users', 'roles', 'user_roles', 'user_id', 'role_id');
$relations->attach('users', $uid, 'roles', $rid, 'user_roles');
$result = $relations->related('users', $uid, 'roles', 'user_roles');
if (count($result) !== 1 || ($result[0]['name'] ?? '') !== 'Admin') throw new RuntimeException('n:m relation test failed.');
$db->beginTransaction();
$users->insert(['name' => 'Rollback']);
$db->rollBack();
if (count($users->get()) !== 1) throw new RuntimeException('Rollback test failed.');
echo "PASS: DataForm 5 database foundation\n";
