<?php
declare(strict_types=1);
use DataForm5\Testing\Core\{Assert,CallbackTest,TestRunner,TestSuite,LegacyScriptRunner,QualityGate};
use DataForm5\Testing\Exceptions\AssertionFailed;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$suite=(new TestSuite('self'))->add(new CallbackTest('same',fn()=>Assert::same(2,1+1)))->add(new CallbackTest('throws',fn()=>Assert::throws(fn()=>throw new RuntimeException('x'),RuntimeException::class)));
$runner=$kernel->container()->get(TestRunner::class);$results=$runner->run($suite);Assert::same(2,count($results));Assert::true($results[0]->passed());Assert::throws(fn()=>Assert::same(1,2),AssertionFailed::class);
$legacy=$kernel->container()->get(LegacyScriptRunner::class);$single=$legacy->runFile(__DIR__.'/core_foundation.php');Assert::true($single->passed(),$single->message??'Legacy-Test fehlgeschlagen');
$gate=$kernel->container()->get(QualityGate::class);$report=$gate->report($results);Assert::same('build0045',$report['build']);Assert::same(0,$report['failed']);
echo "PASS: Test, Quality and Acceptance Layer\n";
