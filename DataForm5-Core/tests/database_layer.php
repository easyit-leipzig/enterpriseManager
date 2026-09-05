<?php
declare(strict_types=1);

use DataForm5\Core\Kernel;
use DataForm\Database\Core\DatabaseManager;
use DataForm\Database\Core\RelationManager;
use DataForm\Database\Core\SchemaBuilder;

$databaseName = 'phase4_test_' . getmypid();
putenv('CSV_DATABASE=' . $databaseName);
$databaseDirectory = dirname(__DIR__) . '/storage/projects/demo/csv/' . $databaseName;

$removeDirectory = static function (string $directory) use (&$removeDirectory): void {
    if (!is_dir($directory)) {
        return;
    }
    foreach (scandir($directory) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $directory . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? $removeDirectory($path) : unlink($path);
    }
    rmdir($directory);
};

try {
    /** @var Kernel $kernel */
    $kernel = require __DIR__ . '/../bootstrap/app.php';
    $container = $kernel->container();

    /** @var DatabaseManager $manager */
    $manager = $container->get(DatabaseManager::class);
    assert($manager->defaultConnectionName() === 'default');
    assert($manager->has('default'));
    assert($manager->has('admin'));
    assert(in_array('project', $manager->configuredConnections(), true));

    $db = $manager->connection();
    assert($db->driver() === 'csv');

    $tableA = 'authors';
    $tableB = 'books';
    $pivot = 'author_book';
    $db->createTable($tableA, ['name']);
    $db->createTable($tableB, ['title']);

    $authorId = $db->insert($tableA, ['name' => 'Ada']);
    $bookId = $db->insert($tableB, ['title' => 'Core Architecture']);

    /** @var RelationManager $relations */
    $relations = $container->get(RelationManager::class);
    $relations->createManyToMany($tableA, $tableB, $pivot, 'author_id', 'book_id');
    $relations->attach($tableA, $authorId, $tableB, $bookId);
    $related = $relations->related($tableA, $authorId, $tableB);
    assert(count($related) === 1);
    assert($related[0]['title'] === 'Core Architecture');

    /** @var SchemaBuilder $schema */
    $schema = $container->get(SchemaBuilder::class);
    assert($schema->hasTable($tableA));

    $db->beginTransaction();
    $tempId = $db->insert($tableA, ['name' => 'Rollback']);
    assert($db->find($tableA, $tempId) !== null);
    $db->rollBack();
    assert($db->find($tableA, $tempId) === null);

    $manager->disconnectAll();
    echo "PASS: Database Layer\n";
} finally {
    $removeDirectory($databaseDirectory);
    putenv('CSV_DATABASE');
}
