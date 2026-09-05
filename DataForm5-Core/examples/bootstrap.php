<?php
declare(strict_types=1);
require dirname(__DIR__) . '/system/database/autoload.php';

use DataForm\Database\Core\DatabaseManager;
use DataForm\Database\Core\QueryBuilder;

$config = require dirname(__DIR__) . '/config/database.php';
$manager = new DatabaseManager($config);
$db = $manager->connection('default');
$query = new QueryBuilder($db, 'customers');
