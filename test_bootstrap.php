<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "CORE_BOOTSTRAP_TEST_START\n";

$app = require __DIR__ . '/DataForm5-Core/bootstrap/app.php';

echo 'RETURN_TYPE=' . get_debug_type($app) . PHP_EOL;

if (!is_object($app)) {
    echo "CORE_BOOTSTRAP_FAIL: Bootstrap returned no object.\n";
    exit(1);
}

echo 'CORE_BOOTSTRAP_OK: ' . get_class($app) . PHP_EOL;
exit(0);