<?php
declare(strict_types=1);
use DataForm5\Performance\Contracts\ProfilerInterface;use DataForm5\Performance\Core\PerformanceReportWriter;use DataForm5\Performance\Exceptions\PerformanceException;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';$profiler=$kernel->container()->get(ProfilerInterface::class);$profiler->reset();
$token=$profiler->start('database.query',['driver'=>'csv']);usleep(1000);$result=$profiler->stop($token,['rows'=>3]);assert($result['duration_ms']>=0);assert($result['name']==='database.query');assert($result['tags']['driver']==='csv');assert($result['metadata']['rows']===3);
$value=$profiler->measure('view.render',fn()=>42);assert($value===42);$profiler->mark('request.ready');$report=$profiler->report();assert($report['measurement_count']===2);assert(isset($report['summary']['database.query']['average_ms']));assert(count($report['marks'])===1);
$thrown=false;try{$profiler->stop('missing');}catch(PerformanceException){$thrown=true;}assert($thrown);
$file=dirname(__DIR__).'/storage/performance/test.json';$kernel->container()->get(PerformanceReportWriter::class)->write($file);assert(is_file($file));unlink($file);echo "PASS: Performance Profiler and Optimization Layer\n";
