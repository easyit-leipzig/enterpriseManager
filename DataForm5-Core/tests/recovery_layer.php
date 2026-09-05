<?php
declare(strict_types=1);
use DataForm5\Recovery\Core\RecoveryManager;
$root=dirname(__DIR__);$kernel=require $root.'/bootstrap/app.php';$manager=$kernel->container()->get(RecoveryManager::class);
$base=$root.'/storage/framework/tests/recovery_'.bin2hex(random_bytes(3));$source=$base.'/source';$target=$base.'/target';mkdir($source.'/nested',0775,true);file_put_contents($source.'/a.txt','alpha');file_put_contents($source.'/nested/b.txt','beta');
$manifest=$manager->backup($source,'phase21-test',['build'=>'0021']);assert(isset($manifest['id'],$manifest['integrity_hash']));assert($manager->verify($manifest['id'])===true);
mkdir($target,0775,true);file_put_contents($target.'/old.txt','old');$manager->restore($manifest['id'],$target);assert(file_get_contents($target.'/a.txt')==='alpha');assert(file_get_contents($target.'/nested/b.txt')==='beta');assert(!file_exists($target.'/old.txt'));
$manifestFile=$root.'/storage/recovery/'.$manifest['id'].'/manifest.json';$raw=file_get_contents($manifestFile);file_put_contents($manifestFile,str_replace('phase21-test','phase21-tampered',$raw));assert($manager->verify($manifest['id'])===false);file_put_contents($manifestFile,$raw);
echo "PASS: Backup, Restore and Recovery Layer\n";
