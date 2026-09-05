<?php
declare(strict_types=1);

$root=dirname(__DIR__);
$checks=[];
$add=function(string $name,bool $ok,string $detail='')use(&$checks):void{
    $checks[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL','detail'=>$detail];
};

$bench=function(callable $fn,int $iterations):array{
    $times=[];
    for($i=0;$i<$iterations;$i++){
        $start=hrtime(true);
        $fn();
        $times[]=(hrtime(true)-$start)/1e6;
    }
    sort($times,SORT_NUMERIC);
    $count=count($times);
    $p95=$times[max(0,(int)ceil($count*0.95)-1)]??0.0;
    return [
        'count'=>$count,
        'avg_ms'=>$count?array_sum($times)/$count:0.0,
        'p95_ms'=>$p95,
        'max_ms'=>$times?$times[$count-1]:0.0,
    ];
};

$manifestBench=$bench(function()use($root):void{
    $data=json_decode((string)file_get_contents($root.'/RELEASE_MANIFEST.json'),true);
    if(!is_array($data))throw new RuntimeException('Manifest ungültig.');
},100);
$add('release manifest parse p95 < 10ms',$manifestBench['p95_ms']<10.0,sprintf('p95=%.3fms avg=%.3fms',$manifestBench['p95_ms'],$manifestBench['avg_ms']));

$providerBench=$bench(function()use($root):void{
    $providers=require $root.'/DataForm5-Core/config/providers.php';
    if(count($providers)<40)throw new RuntimeException('Providerliste unvollständig.');
},100);
$add('provider registry load p95 < 10ms',$providerBench['p95_ms']<10.0,sprintf('p95=%.3fms avg=%.3fms',$providerBench['p95_ms'],$providerBench['avg_ms']));

$sdkBench=$bench(function()use($root):void{
    $txt=(string)file_get_contents($root.'/docs/SDK/README.md');
    if($txt==='')throw new RuntimeException('SDK-Dokumentation leer.');
},100);
$add('sdk documentation read p95 < 10ms',$sdkBench['p95_ms']<10.0,sprintf('p95=%.3fms avg=%.3fms',$sdkBench['p95_ms'],$sdkBench['avg_ms']));

$memoryPeak=memory_get_peak_usage(true);
$add('audit memory peak < 64MB',$memoryPeak<64*1024*1024,round($memoryPeak/1048576,2).' MB');

$failed=array_values(array_filter($checks,static fn(array $r):bool=>$r['status']==='FAIL'));
$result=[
    'release'=>'RC1.8',
    'phase'=>'6.7',
    'status'=>$failed===[]?'PASS':'FAIL',
    'benchmarks'=>[
        'manifest'=>$manifestBench,
        'providers'=>$providerBench,
        'sdk_docs'=>$sdkBench,
        'memory_peak_bytes'=>$memoryPeak,
    ],
    'checks'=>$checks,
    'summary'=>['checks'=>count($checks),'failed'=>count($failed)],
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===[]?0:1);
