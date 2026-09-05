<?php
declare(strict_types=1);
use DataForm5\Audit\Core\{AuditManager,AuditQuery};
$root=dirname(__DIR__);$file=$root.'/storage/logs/audit.jsonl';@unlink($file);
$kernel=require $root.'/bootstrap/app.php';$audit=$kernel->container()->get(AuditManager::class);
$entry=$audit->record('project.created',['name'=>'Demo'],'user','7','project','42','127.0.0.1','test');
assert($entry->event==='project.created');assert($entry->hash!=='');
$audit->record('project.updated',['field'=>'name'],'user','7','project','42');
$rows=$audit->search(new AuditQuery(actorId:'7',subjectType:'project',subjectId:'42'));assert(count($rows)===2);assert($audit->verify());
file_put_contents($file,str_replace('Demo','Manipuliert',(string)file_get_contents($file)));assert(!$audit->verify());@unlink($file);
echo "PASS: Audit and Change Log Layer\n";
