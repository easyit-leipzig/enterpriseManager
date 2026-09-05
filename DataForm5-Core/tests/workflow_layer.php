<?php
declare(strict_types=1);
use DataForm5\Workflow\Core\{Transition,WorkflowDefinition,WorkflowManager};
use DataForm5\Workflow\Exceptions\WorkflowException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$manager=$kernel->container()->get(WorkflowManager::class);
$actions=[];
$definition=(new WorkflowDefinition('approval','draft',['draft','review','approved','rejected']))
    ->transition(new Transition('submit',['draft'],'review'))
    ->transition(new Transition('approve',['review'],'approved',[fn($instance,$context)=>($context['role']??'')==='admin'],[function($instance,$context) use (&$actions){$actions[]='approved';}]))
    ->transition(new Transition('reject',['review'],'rejected'));
$manager->register($definition);
$instance=$manager->start('approval','document-1',['title'=>'Test']);
assert($instance->state()==='draft');
assert($manager->available('document-1')===['submit']);
$result=$manager->apply('document-1','submit',['data'=>['submitted'=>true]]);
assert($result->from==='draft'&&$result->to==='review');
assert($instance->data()['submitted']===true);
assert(!$manager->can('document-1','approve',['role'=>'editor']));
$blocked=false;try{$manager->apply('document-1','approve',['role'=>'editor']);}catch(WorkflowException){$blocked=true;}assert($blocked);
assert($manager->can('document-1','approve',['role'=>'admin']));
$manager->apply('document-1','approve',['role'=>'admin']);
assert($instance->state()==='approved');assert($actions===['approved']);
$history=$manager->history('document-1');assert(count($history)===3);assert($history[2]['transition']==='approve');
echo "PASS: Workflow and State Machine Layer\n";
