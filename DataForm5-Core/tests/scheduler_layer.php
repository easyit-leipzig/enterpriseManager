<?php
declare(strict_types=1);
use DataForm5\Scheduler\Core\CronExpression;
use DataForm5\Scheduler\Core\Scheduler;
require __DIR__.'/../bootstrap/autoload.php';
assert((new CronExpression('30 14 * * 1-5'))->isDue(new DateTimeImmutable('2026-08-05 14:30:00'))===true);
assert((new CronExpression('*/15 * * * *'))->isDue(new DateTimeImmutable('2026-08-05 14:45:00'))===true);
assert((new CronExpression('*/15 * * * *'))->isDue(new DateTimeImmutable('2026-08-05 14:46:00'))===false);
$kernel=require __DIR__.'/../bootstrap/app.php';$scheduler=$kernel->container()->get(Scheduler::class);
$file=__DIR__.'/../storage/test-runtime/scheduler/result.txt';@mkdir(dirname($file),0775,true);@unlink($file);
$scheduler->task('test.every-minute','* * * * *',function()use($file):void{file_put_contents($file,'X',FILE_APPEND|LOCK_EX);})->withoutOverlapping(60);
$scheduler->task('test.not-due','0 0 1 1 *',function()use($file):void{file_put_contents($file,'N',FILE_APPEND|LOCK_EX);});
$result=$scheduler->runDue(new DateTimeImmutable('2026-08-05 20:33:00'));
assert($result['test.every-minute']==='success');assert(!isset($result['test.not-due']));assert(file_get_contents($file)==='X');
$history=glob(__DIR__.'/../storage/framework/scheduler/history/*.log')?:[];assert(count($history)>=1);
echo "PASS: Scheduler and Task Layer\n";
