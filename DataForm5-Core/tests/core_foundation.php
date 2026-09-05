<?php
declare(strict_types=1);

use DataForm5\Core\Config;
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Core\Kernel;
use DataForm5\Core\Support\Path;

require_once __DIR__ . '/../bootstrap/autoload.php';

final class FoundationDependency {}
final class FoundationService { public function __construct(public FoundationDependency $dependency) {} }

$kernel = (new Kernel(dirname(__DIR__)))->boot();
$container = $kernel->container();

assert($kernel->isBooted());
assert($container->get(Path::class)->base() === dirname(__DIR__));
assert($container->get(Config::class)->get('app.name') === 'DataForm5-Core');
assert($container->get(FoundationService::class) instanceof FoundationService);

$container->singleton('counter', static function (): object { return new stdClass(); });
assert($container->get('counter') === $container->get('counter'));
assert($container->get(ServiceContainer::class) === $container);

echo "PASS: Core Foundation\n";
