<?php
declare(strict_types=1);

$root=dirname(__DIR__);
$commands=[
    ['name'=>'production-security','cmd'=>[PHP_BINARY,$root.'/tools/rc18-production-security-audit.php']],
    ['name'=>'reproducibility','cmd'=>[PHP_BINARY,$root.'/tools/rc18-reproducibility-audit.php']],
    ['name'=>'upgrade-migrations','cmd'=>[PHP_BINARY,$root.'/tools/rc18-upgrade-migration-audit.php']],
    ['name'=>'fresh-install','cmd'=>[PHP_BINARY,$root.'/tools/rc18-fresh-install-audit.php']],
    ['name'=>'cross-component','cmd'=>[PHP_BINARY,$root.'/tools/rc18-cross-component-audit.php']],
    ['name'=>'integration','cmd'=>[PHP_BINARY,$root.'/tools/rc18-integration-acceptance.php']],
    ['name'=>'performance','cmd'=>[PHP_BINARY,$root.'/tools/rc18-performance-audit.php']],
    ['name'=>'architecture','cmd'=>[PHP_BINARY,$root.'/tools/architecture-audit.php']],
];

$results=[];
foreach($commands as $entry){
    $cmd=implode(' ',array_map('escapeshellarg',$entry['cmd'])).' 2>&1';
    $out=[];$code=0;$start=hrtime(true);
    exec($cmd,$out,$code);
    $results[]=[
        'name'=>$entry['name'],
        'status'=>$code===0?'PASS':'FAIL',
        'exit_code'=>$code,
        'duration_ms'=>round((hrtime(true)-$start)/1e6,3),
        'output'=>trim(implode(PHP_EOL,$out)),
    ];
}

$qualityCmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.'/easyit').' quality:center --json 2>&1';
$out=[];$code=0;$start=hrtime(true);exec($qualityCmd,$out,$code);
$results[]=[
    'name'=>'quality-center',
    'status'=>$code===0?'PASS':'FAIL',
    'exit_code'=>$code,
    'duration_ms'=>round((hrtime(true)-$start)/1e6,3),
    'output'=>trim(implode(PHP_EOL,$out)),
];

$failed=array_values(array_filter($results,static fn(array $r):bool=>$r['status']==='FAIL'));
$result=[
    'release'=>'RC1.8',
    'phase'=>'6.7',
    'status'=>$failed===[]?'PASS':'FAIL',
    'gates'=>$results,
    'summary'=>['gates'=>count($results),'failed'=>count($failed)],
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===[]?0:1);
