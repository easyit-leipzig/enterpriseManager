<?php
declare(strict_types=1);
use DataForm5\Observability\Contracts\MetricsInterface;
use DataForm5\Observability\Core\MetricsManager;
use DataForm5\Observability\Stores\InMemoryMetricStore;
use DataForm5\Observability\Exceptions\ObservabilityException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$metrics=new MetricsManager(new InMemoryMetricStore(),['app'=>'test']);
$metrics->increment('http.requests',1,['method'=>'GET']);$metrics->increment('http.requests',2,['method'=>'GET']);
$metrics->gauge('queue.depth',7);$metrics->gauge('queue.depth',3);
$metrics->observe('db.query_ms',10);$metrics->observe('db.query_ms',20);
$value=$metrics->measure('task.duration_ms',fn()=>42,['task'=>'demo']);assert($value===42);
$snapshot=$metrics->snapshot();assert($snapshot['count']===4);
$by=[];foreach($snapshot['metrics'] as $m)$by[$m['name'].'|'.($m['tags']['method']??$m['tags']['task']??'')]=$m;
assert($by['http.requests|GET']['value']===3.0);assert($by['queue.depth|']['value']===3.0);assert($by['db.query_ms|']['value']===15.0);assert($by['task.duration_ms|demo']['count']===1);
$invalid=false;try{$metrics->increment('invalid metric');}catch(ObservabilityException){$invalid=true;}assert($invalid);
$kernel=require dirname(__DIR__).'/bootstrap/app.php';assert($kernel->container()->get(MetricsInterface::class) instanceof MetricsManager);
echo "PASS: Metrics, Monitoring and Observability Layer\n";
