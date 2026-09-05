<?php
declare(strict_types=1);$r=dirname(__DIR__);$c=[];$a=function($n,$ok,$d='')use(&$c){$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL','detail'=>$d];};
$v=trim((string)file_get_contents($r.'/VERSION'));$fc=trim((string)file_get_contents($r.'/RELEASE_CANDIDATE'));
$a('internal version',in_array($v,['RC1.8.6-dev-phase68','RC1.8-FC1-HF76'],true),$v);$a('candidate identity',$fc==='RC1.8-FC1',$fc);
foreach(['RELEASE_NOTES_RC1.8-FC1.md','docs/RC1.8_PHASE6.8_FINAL_CANDIDATE.md','DataForm5-Core/.env.production.example','RELEASE_MANIFEST.json','installer/MIGRATION_MANIFEST.json','tools/rc18-final-release-gate.php'] as $f)$a($f,is_file($r.'/'.$f));
$a('local env excluded',!is_file($r.'/DataForm5-Core/.env'));
$f=array_filter($c,fn($x)=>$x['status']==='FAIL');$o=['release'=>'RC1.8-FC1','phase'=>'6.8','status'=>$f===[]?'PASS':'FAIL','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>count($f)]];
echo json_encode($o,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($f===[]?0:1);
