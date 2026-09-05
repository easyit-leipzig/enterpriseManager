<?php
declare(strict_types=1);
$r=__DIR__;$checks=[];$c=function(string $n,bool $ok)use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$d=(string)file_get_contents($r.'/app/dashboard.php');
$l=(string)file_get_contents($r.'/system/ui/layout.php');
$s=(string)file_get_contents($r.'/setup.php');
$db=(string)file_get_contents($r.'/installer/database.php');

$c('dashboard permanent setup link',str_contains($d,'../setup.php')&&str_contains($d,'Installation / Setup'));
$c('dashboard permanent db assistant link',str_contains($d,'../installer/database.php')&&str_contains($d,'Datenbank-Assistent'));
$c('dashboard actionable configuration error',str_contains($d,'Administrationskonfiguration unvollständig')&&str_contains($d,'Datenbank-Assistent öffnen'));
$c('dashboard distinguishes new and existing projects',str_contains($d,'Neues Projekt anlegen')&&str_contains($d,'Vorhandenes Projekt registrieren'));
$c('authenticated nav has setup',str_contains($l,"'setup' => ['Setup'"));
$c('authenticated nav has database assistant',str_contains($l,"'database-setup' => ['DB-Assistent'"));
$c('installed setup exposes maintenance mode',str_contains($s,'Wartungsmodus verfügbar')&&str_contains($s,'Datenbanken / .env prüfen und reparieren'));
$c('setup links recovery',str_contains($s,'recovery.php'));
$c('db assistant can recreate env from example',str_contains($db,"dirname(\$path) . '/.env.example'")&&str_contains($db,'copy($example, $path)'));
$c('db assistant preserves checksum skip behavior',str_contains($db,'SKIP ')&&str_contains($db,'Checksum OK'));
$f=count(array_filter($checks,fn($x)=>$x['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF12','status'=>$f?'FAIL':'PASS','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>$f]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($f?1:0);
