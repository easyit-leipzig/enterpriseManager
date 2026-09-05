<?php
declare(strict_types=1);
use DataForm5\Messaging\Core\{Command,EventBus,IntegrationEvent,MessageBus,Query};
use DataForm5\Messaging\Exceptions\MessagingException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$kernel=require dirname(__DIR__).'/bootstrap/app.php';$bus=$kernel->container()->get(MessageBus::class);$trace=[];
$bus->middleware(function($m,$next) use (&$trace){$trace[]='before';$r=$next($m);$trace[]='after';return $r;});
$bus->register('sum',fn($m)=>array_sum($m->payload()));assert($bus->dispatch(new Command('sum',[2,3]))===5);assert($trace===['before','after']);
$bus->register('project.find',fn($m)=>['id'=>$m->payload()['id']]);assert($bus->dispatch(new Query('project.find',['id'=>42]))['id']===42);
$failed=false;try{$bus->dispatch(new Command('missing'));}catch(MessagingException){$failed=true;}assert($failed);
$events=$kernel->container()->get(EventBus::class);$events->subscribe('project.created',fn($e)=>$e->payload()['id']);$events->subscribe('project.created',fn($e)=>'logged-'.$e->payload()['id']);$result=$events->publish(new IntegrationEvent('project.created',['id'=>7]));assert($result===[7,'logged-7']);
echo "PASS: Messaging, Event Bus and Integration Layer\n";
