<?php
declare(strict_types=1);
use DataForm5\Compatibility\Core\CompatibilityManager;use DataForm5\Compatibility\Core\DeprecationRegistry;use DataForm5\Compatibility\Exceptions\CompatibilityException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$deps=(new DeprecationRegistry())->add('legacy.api','0.43.0-dev','new.api','1.0.0');
$m=new CompatibilityManager('0.43.0-dev','0.1.0',[['version'=>'0.42.0-dev','name'=>'hardening'],['version'=>'0.43.0-dev','name'=>'compatibility']],$deps);
assert($m->supports('0.42.0-dev')===true);assert($m->supports('2.0.0')===false);$inspection=$m->inspect('0.42.0-dev');assert($inspection['compatible']===true);$plan=$m->plan('0.42.0-dev');assert(count($plan['steps'])===1);assert($plan['steps'][0]['name']==='compatibility');assert(count($plan['deprecations'])===1);
$thrown=false;try{$m->plan('2.0.0');}catch(CompatibilityException){$thrown=true;}assert($thrown);echo "PASS: Compatibility, Upgrade and LTS Layer\n";
