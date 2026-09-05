<?php
declare(strict_types=1);
namespace DataForm5\DemoModule;
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Modules\Contracts\ModuleInterface;
use DataForm5\Modules\Core\HookDispatcher;
final class DemoModule implements ModuleInterface
{
    public function register(ServiceContainer $container): void { $container->instance('demo.module.registered', true); }
    public function boot(ServiceContainer $container): void { $container->get(HookDispatcher::class)->listen('core.health', static fn(): string=>'demo-module:ok'); }
}
