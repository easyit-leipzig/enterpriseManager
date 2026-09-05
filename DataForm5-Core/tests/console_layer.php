<?php
declare(strict_types=1);
use DataForm5\Console\Core\{AbstractCommand,CommandRegistry,ConsoleApplication,Input,Output};
$kernel=require dirname(__DIR__).'/bootstrap/app.php';$app=$kernel->container()->get(ConsoleApplication::class);$registry=$kernel->container()->get(CommandRegistry::class);
assert(isset($registry->all()['list'],$registry->all()['about'],$registry->all()['health']));
$stream=fopen('php://memory','w+');$output=new Output($stream);assert($app->run(['dataform','about'],$output)===0);assert(str_contains($output->contents(),'0023'));
$registry->add(new class extends AbstractCommand{public function name():string{return 'greet';}public function description():string{return 'Test';}public function arguments():array{return ['name'=>'Name'];}public function options():array{return ['upper'=>['description'=>'Groß','default'=>false,'requires_value'=>false]];}public function execute(Input $i,Output $o):int{$name=(string)$this->argument($i,'name');$o->line($this->option($i,'upper')?strtoupper($name):$name);return 0;}});
$o2=new Output(fopen('php://memory','w+'));assert($app->run(['dataform','greet','Olaf','--upper'],$o2)===0);assert(str_contains($o2->contents(),'OLAF'));assert($app->run(['dataform','missing'],new Output(fopen('php://memory','w+')))===2);
echo "PASS: CLI and Console Layer\n";
