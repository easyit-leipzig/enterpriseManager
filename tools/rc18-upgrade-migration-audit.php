<?php
declare(strict_types=1);

$root=dirname(__DIR__);
$manifestFile=$root.'/installer/MIGRATION_MANIFEST.json';
$checks=[];
$add=function(string $group,string $name,bool $ok,string $detail='')use(&$checks):void{
    $checks[]=['group'=>$group,'check'=>$name,'status'=>$ok?'PASS':'FAIL','detail'=>$detail];
};

$add('manifest','exists',is_file($manifestFile));
$manifest=is_file($manifestFile)?json_decode((string)file_get_contents($manifestFile),true):null;
$add('manifest','valid JSON',is_array($manifest));
if(is_array($manifest)){
    foreach(['admin','project'] as $group){
        $entries=$manifest['groups'][$group]??[];
        $files=glob($root.'/installer/schema/'.$group.'/*.php')?:[];
        sort($files,SORT_NATURAL);
        $expected=array_column(is_array($entries)?$entries:[],'file');
        $actual=array_map('basename',$files);
        $add($group,'ordered file list',$expected===$actual,implode(', ',$actual));
        foreach($files as $file){
            $name=basename($file);
            $entry=null;
            foreach($entries as $candidate)if(($candidate['file']??'')===$name){$entry=$candidate;break;}
            $current=hash_file('sha256',$file);
            $add($group,$name.' checksum',is_array($entry)&&hash_equals((string)($entry['sha256']??''),(string)$current),(string)$current);
        }
    }
}

$installer=(string)file_get_contents($root.'/installer/database.php');
foreach([
    'migrationRecord',
    'registerMigration',
    'hash_equals',
    'SKIP ',
    'APPLY '
] as $needle)$add('runner',$needle,str_contains($installer,$needle));
$add('runner','no PDO transaction wrapper around MySQL/MariaDB DDL',
    !str_contains($installer,'$server->beginTransaction()')
    && !str_contains($installer,'$server->commit()')
    && !str_contains($installer,'$server->rollBack()'),
    'MySQL/MariaDB DDL performs implicit commits; registration occurs only after successful schema callable execution');

$changed=$installer;
$add('policy','changed migration protection',str_contains($changed,'wurde nach ihrer Ausführung verändert'));
$add('policy','idempotent skip',str_contains($changed,'bereits ausgeführt, Checksum OK'));

$failed=array_values(array_filter($checks,static fn(array $r):bool=>$r['status']==='FAIL'));
$result=[
    'release'=>'RC1.8',
    'phase'=>'6.4',
    'status'=>$failed===[]?'PASS':'FAIL',
    'mode'=>'static migration/upgrade acceptance; database execution remains environment-specific',
    'checks'=>$checks,
    'summary'=>['checks'=>count($checks),'failed'=>count($failed)]
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===[]?0:1);
