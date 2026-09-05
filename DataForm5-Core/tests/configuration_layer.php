<?php
declare(strict_types=1);

use DataForm5\Core\Config;
use DataForm5\Core\Configuration\Env;
use DataForm5\Core\Configuration\Environment;
use DataForm5\Core\Kernel;
use DataForm5\Core\Support\Path;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$root = dirname(__DIR__);
$testEnv = $root . '/.env.test-runtime';
file_put_contents($testEnv, "TEST_BOOL=true\nTEST_INT=42\nTEST_FLOAT=3.5\nTEST_TEXT=DataForm\n", LOCK_EX);
try {
    $environment = (new Environment($root))->load('.env.test-runtime', true);
    if ($environment->get('TEST_TEXT') !== 'DataForm') throw new RuntimeException('.env text failed.');
    if (Env::get('TEST_BOOL') !== true) throw new RuntimeException('.env bool cast failed.');
    if (Env::get('TEST_INT') !== 42) throw new RuntimeException('.env int cast failed.');
    if (Env::get('TEST_FLOAT') !== 3.5) throw new RuntimeException('.env float cast failed.');

    $config = Config::loadDirectory($root . '/config');
    if (!$config->has('app.name')) throw new RuntimeException('Config has failed.');
    $config->set('runtime.enabled', true);
    if ($config->require('runtime.enabled') !== true) throw new RuntimeException('Config set/require failed.');

    $cache = $root . '/storage/framework/cache/test-config.php';
    $config->cache($cache);
    $cached = Config::fromCache($cache);
    if ($cached->get('runtime.enabled') !== true) throw new RuntimeException('Config cache failed.');
    unlink($cache);

    $kernel = (new Kernel($root))->boot();
    $container = $kernel->container();
    if (!$container->get(Config::class) instanceof Config) throw new RuntimeException('Config binding failed.');
    if (!$container->get(Environment::class) instanceof Environment) throw new RuntimeException('Environment binding failed.');
    if (!$container->get(Path::class) instanceof Path) throw new RuntimeException('Path binding failed.');
    if (date_default_timezone_get() !== (string)$container->get(Config::class)->get('app.timezone')) throw new RuntimeException('Timezone bootstrap failed.');
} finally {
    @unlink($testEnv);
    foreach (['TEST_BOOL', 'TEST_INT', 'TEST_FLOAT', 'TEST_TEXT'] as $key) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}

echo "PASS: Configuration Layer\n";
