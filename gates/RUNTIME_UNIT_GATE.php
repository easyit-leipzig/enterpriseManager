<?php
declare(strict_types=1);
$root=dirname(__DIR__);require $root.'/easyit-license-server/bootstrap.php';
use EasyIT\LicenseServer\ProtectedCore\DefinitionValidator;
use EasyIT\LicenseServer\ProtectedCore\DataFormCompiler;
use EasyIT\LicenseServer\ProtectedCore\ActionResolver;
$checks=[];$c=function(string $name,bool $ok,string $detail='')use(&$checks):void{$checks[]=[$name,$ok,$detail];};
$v=new DefinitionValidator();
$good=['view'=>['mode'=>'form','page_size'=>20,'fulltext_search'=>true,'filter'=>true,'addClass'=>'x','addJavaScript'=>'custom.js'],'capabilities'=>['dataset.read','dataset.create','dataset.update','dataset.delete','relation.createChild'],'fields'=>[['name'=>'id','type'=>'integer','readonly'=>true],['name'=>'name','type'=>'text','readonly'=>false]],'events'=>['dataform'=>['open','close'],'dataset'=>['before_save','after_save']],'relations'=>[['name'=>'child','type'=>'1:n','child_field'=>'to_parent_id','binding'=>['child_field'=>'to_parent_id','value'=>'471','readonly'=>true]]]];
$vr=$v->validate($good);$c('valid definition accepted',($vr['valid']??false)===true);
$bad=$good;$bad['events']['dataform'][]='after_save';$br=$v->validate($bad);$c('dataset event rejected in dataform scope',($br['valid']??true)===false);
$binding=['installation_id'=>'INST-test','project_id'=>'1','dataform_id'=>'27','definition_id'=>'DEF-1','license_generation'=>1,'installation_generation'=>1];
$m=(new DataFormCompiler())->compile($good,$binding,time(),time()+3600,time()+86400,'RT-test');
$c('compiler keeps addClass',($m['view']['addClass']??'')==='x');$c('compiler keeps addJavaScript',($m['view']['addJavaScript']??'')==='custom.js');
$r=new ActionResolver();$u=$r->resolve($m,'dataset.update',['record_id'=>'481'],['fields'=>['id','name']]);$c('readonly field excluded',($u['allowed_fields']??[])===['name']);
try{$r->resolve($m,'dataset.delete',[],[]);$c('delete without resource rejected',false);}catch(RuntimeException $e){$c('delete without resource rejected',$e->getMessage()==='ACTION_RESOURCE_INVALID',$e->getMessage());}
$bd=$r->resolve($m,'dataset.delete',['record_ids'=>[1,2,2]],[]);$c('bulk ids bound',($bd['resource']['record_ids']??[])===['1','2']);
$rel=$r->resolve($m,'relation.createChild',['relation'=>'child'],[]);$c('relation binding resolved',($rel['binding']['child_field']??'')==='to_parent_id');
$fail=0;foreach($checks as[$n,$ok,$d]){echo str_pad($n,48).($ok?'PASS':'FAIL').($d!==''?' '.$d:'').PHP_EOL;if(!$ok)$fail++;}echo 'FINAL STATUS: '.($fail?'FAILED':'PASSED').PHP_EOL;exit($fail?1:0);
