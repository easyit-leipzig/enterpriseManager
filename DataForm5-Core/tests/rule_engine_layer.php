<?php
declare(strict_types=1);
use DataForm5\Rules\Core\{FactContext,RuleEngine,RuleOutcome};
use DataForm5\Rules\Exceptions\RuleException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$rules=$kernel->container()->get(RuleEngine::class);
$rules->rule('discount','vip',fn(FactContext $f)=>$f->get('customer.vip')?RuleOutcome::match(20,'VIP-Rabatt'):RuleOutcome::noMatch('Kein VIP'),100)
      ->rule('discount','volume',fn(FactContext $f)=>$f->get('order.total',0)>=1000?RuleOutcome::match(10,'Mengenrabatt'):RuleOutcome::noMatch(),50);
$d=$rules->evaluate('discount',['customer'=>['vip'=>true],'order'=>['total'=>1500]]);
assert($d->matched&&$d->value===20&&$d->reasons()===['VIP-Rabatt']);
$all=$rules->evaluate('discount',['customer'=>['vip'=>true],'order'=>['total'=>1500]],'all');
assert($all->value===[20,10]&&count($all->outcomes)===2);
$none=$rules->evaluate('discount',['customer'=>['vip'=>false],'order'=>['total'=>100]]);
assert(!$none->matched&&$none->value===null);
$failed=false;try{$rules->evaluate('missing',[]);}catch(RuleException){$failed=true;}assert($failed);
echo "PASS: Business Rule Engine Layer\n";
