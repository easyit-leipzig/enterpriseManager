<?php
declare(strict_types=1);
$root=dirname(__DIR__);$mf=$root.'/RELEASE_MANIFEST.json';$checks=[];
$add=function($n,$ok,$d='')use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL','detail'=>$d];};
$add('release manifest exists',is_file($mf));$m=is_file($mf)?json_decode((string)file_get_contents($mf),true):null;$add('release manifest valid',is_array($m));
if(is_array($m)){
 $add('manifest format',($m['format']??'')==='easyit-enterprise-release-manifest/1');
 $add('version matches',($m['version']??'')===trim((string)file_get_contents($root.'/VERSION')));
 $excluded=(array)($m['excluded_runtime_prefixes']??[]);
 $skip=function(string $rel)use($excluded):bool{
  if(in_array($rel,['RELEASE_MANIFEST.json','FILE_MANIFEST_SHA256.txt'],true))return true;
  foreach($excluded as $prefix)if(str_starts_with($rel,(string)$prefix))return true;
  return false;
 };
 $actual=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
 foreach($it as $f){if(!$f->isFile())continue;$path=$f->getPathname();$rel=str_replace('\\','/',substr($path,strlen($root)+1));if($skip($rel))continue;$actual[$rel]=['sha256'=>hash_file('sha256',$path),'size'=>(int)(filesize($path)?:0)];}
 ksort($actual,SORT_STRING);$expected=(array)($m['files']??[]);
 $add('file set identical',array_keys($expected)===array_keys($actual),count($actual).' Dateien');
 $bad=[];foreach($actual as $rel=>$meta)if(isset($expected[$rel])&&(($expected[$rel]['sha256']??'')!==$meta['sha256']||(int)($expected[$rel]['size']??-1)!==$meta['size']))$bad[]=$rel;
 $add('file checksums identical',$bad===[],$bad===[]?'':implode(', ',$bad));
 $keys=array_keys($expected);$sorted=$keys;sort($sorted,SORT_STRING);$add('manifest order deterministic',$keys===$sorted);
 $volatile=[];foreach($expected as $rel=>$_)foreach($excluded as $prefix)if(str_starts_with($rel,(string)$prefix))$volatile[]=$rel;
 $add('runtime files excluded',$volatile===[],$volatile===[]?'':implode(', ',$volatile));
}
$temp=[];foreach(['*.tmp','*.bak','*.orig'] as $pattern)foreach(glob($root.'/'.$pattern)?:[] as $f)$temp[]=basename($f);
$add('no root temp artifacts',$temp===[],$temp===[]?'':implode(', ',$temp));
$failed=array_filter($checks,fn($x)=>$x['status']==='FAIL');
$r=['release'=>'RC1.8','phase'=>'HF76-FINAL','status'=>$failed===[]?'PASS':'FAIL','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>count($failed)]];
echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($failed===[]?0:1);
