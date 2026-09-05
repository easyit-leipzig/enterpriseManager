<?php
declare(strict_types=1);
use DataForm5\Context\Contracts\ContextManagerInterface;
use DataForm5\Context\Core\ProjectContext;
use DataForm5\Context\Exceptions\ContextException;
$root=dirname(__DIR__);$kernel=require $root.'/bootstrap/app.php';
$manager=$kernel->container()->get(ContextManagerInterface::class);
assert($manager->current()===null);
$a=new ProjectContext('tenant-a','project-1','Demo',['database_connection'=>'project']);
$manager->activate($a);assert($manager->requireCurrent()->key()==='tenant-a:project-1');assert($manager->requireCurrent()->databaseConnection()==='project');
assert($manager->scopedKey('cache')==='tenant-a:project-1:cache');assert($manager->scopedPath('uploads/x.txt')==='tenant-a/project-1/uploads/x.txt');
$b=new ProjectContext('tenant-b','project-2');
$result=$manager->run($b,fn(ProjectContext $c)=>$c->key());assert($result==='tenant-b:project-2');assert($manager->requireCurrent()->key()==='tenant-a:project-1');
$blocked=false;try{$manager->assertMatches('tenant-b','project-2');}catch(ContextException){$blocked=true;}assert($blocked);
$manager->clear();$missing=false;try{$manager->requireCurrent();}catch(ContextException){$missing=true;}assert($missing);
echo "PASS: Tenant and Project Context Layer\n";
