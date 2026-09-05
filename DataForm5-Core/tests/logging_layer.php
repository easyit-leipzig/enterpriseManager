<?php
declare(strict_types=1);
use DataForm5\Logging\Contracts\LoggerInterface; use DataForm5\Logging\Core\FileLogger; use DataForm5\Logging\Core\LogManager;
$root=dirname(__DIR__); $file=$root.'/storage/logs/test.log'; foreach(glob($file.'*')?:[] as $f)@unlink($f);
$kernel=require $root.'/bootstrap/app.php'; $manager=$kernel->container()->get(LogManager::class); $logger=$kernel->container()->get(LoggerInterface::class); if($logger!==$manager->channel())throw new RuntimeException('Standardlogger nicht konsistent.');
$test=new FileLogger($file,'info',250,2,'test'); $test->debug('unsichtbar'); $test->info('Benutzer {id}', ['id'=>7,'data'=>['ok'=>true]]); if(!is_file($file))throw new RuntimeException('Logdatei fehlt.'); $content=file_get_contents($file); if(!str_contains($content,'Benutzer 7')||str_contains($content,'unsichtbar'))throw new RuntimeException('Level oder Interpolation fehlerhaft.');
for($i=0;$i<20;$i++)$test->warning(str_repeat('x',40),['i'=>$i]); if(!is_file($file.'.1'))throw new RuntimeException('Rotation fehlgeschlagen.');
$manager->channel('null')->error('ignoriert'); foreach(glob($file.'*')?:[] as $f)@unlink($f); echo "PASS: Logging Layer\n";
