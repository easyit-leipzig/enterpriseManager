<?php
declare(strict_types=1);

use DataForm5\Core\Kernel;
use DataForm\Database\Core\DatabaseManager;
use DataForm\Database\Core\RelationManager;
use DataForm5\ORM\Core\Model;
use DataForm5\ORM\Core\OrmManager;
use DataForm5\ORM\Relations\BelongsToMany;

require_once __DIR__ . '/../bootstrap/autoload.php';

final class OrmAuthor extends Model
{
    protected static string $table = 'orm_authors';
    protected array $fillable = ['name', 'active', 'meta'];
    protected array $casts = ['active' => 'boolean', 'meta' => 'array'];
}

final class OrmBook extends Model
{
    protected static string $table = 'orm_books';
    protected array $fillable = ['title', 'author_id'];
    protected array $casts = ['author_id' => 'integer'];
}

$databaseName = 'phase13_test_' . getmypid();
putenv('CSV_DATABASE=' . $databaseName);
$databaseDirectory = dirname(__DIR__) . '/storage/projects/demo/csv/' . $databaseName;
$removeDirectory = static function (string $directory) use (&$removeDirectory): void {
    if (!is_dir($directory)) return;
    foreach (scandir($directory) ?: [] as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $directory . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? $removeDirectory($path) : unlink($path);
    }
    rmdir($directory);
};

try {
    /** @var Kernel $kernel */
    $kernel = require __DIR__ . '/../bootstrap/app.php';
    $container = $kernel->container();
    /** @var DatabaseManager $dbm */
    $dbm = $container->get(DatabaseManager::class);
    $db = $dbm->connection();
    $db->createTable('orm_authors', ['name', 'active', 'meta']);
    $db->createTable('orm_books', ['title', 'author_id']);

    /** @var OrmManager $orm */
    $orm = $container->get(OrmManager::class);
    $authors = $orm->repository(OrmAuthor::class);
    $books = $orm->repository(OrmBook::class);

    $author = $authors->create(['name' => 'Ada', 'active' => true, 'meta' => ['field' => 'math']]);
    assert($author->exists());
    assert($author->active === true);
    assert($author->meta['field'] === 'math');

    $book = $books->create(['title' => 'Core', 'author_id' => $author->getKey()]);
    assert($authors->belongsTo($book, 'author_id')?->name === 'Ada');
    assert(count($books->hasMany($author, 'author_id')) === 1);

    $author->name = 'Ada Lovelace';
    $author = $authors->save($author);
    assert($author->name === 'Ada Lovelace');

    /** @var RelationManager $relations */
    $relations = $container->get(RelationManager::class);
    $relations->createManyToMany('orm_authors', 'orm_books', 'orm_author_book', 'author_id', 'book_id');
    $many = new BelongsToMany($relations, $books, $author, 'orm_authors', 'orm_books');
    $many->attach($book);
    assert(count($many->get()) === 1);
    $many->detach($book);
    assert(count($many->get()) === 0);

    assert($authors->delete($author));
    echo "PASS: ORM and Model Layer\n";
} finally {
    $removeDirectory($databaseDirectory);
    putenv('CSV_DATABASE');
}
