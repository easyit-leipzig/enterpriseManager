<?php
declare(strict_types=1);
use DataForm5\Help\Core\{HelpRegistry,HelpRenderer,HelpResolver};
$root=dirname(__DIR__);$kernel=require $root.'/bootstrap/app.php';$c=$kernel->container();$registry=$c->get(HelpRegistry::class);assert(count($registry->all())>=2);$resolver=$c->get(HelpResolver::class);$topic=$resolver->resolve('/admin/projects/42');assert($topic!==null);assert($topic->id==='admin.projects');$renderer=$c->get(HelpRenderer::class);$short=$renderer->render($topic,'short');assert(is_string($short['content']));$steps=$renderer->render($topic,'steps');assert(count($steps['content'])===3);$expert=$renderer->render($topic,'expert');assert(str_contains($expert['content'],'Administrationsdaten'));echo "PASS: Context Help and Assistant Layer\n";
