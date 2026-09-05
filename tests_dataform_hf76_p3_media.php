<?php
declare(strict_types=1);
require __DIR__.'/products/dataform/system/DataFormFieldTypes.php';

$passes=[];$failures=[];
function p3t(string $name,callable $fn):void{global $passes,$failures;try{$fn();$passes[]=$name;echo "[PASS] $name\n";}catch(Throwable $e){$failures[]=$name.': '.$e->getMessage();echo "[FAIL] $name: {$e->getMessage()}\n";}}
function p3ok(bool $v,string $m='Assertion failed'):void{if(!$v)throw new RuntimeException($m);}
function p3eq(mixed $a,mixed $b,string $m=''):void{if($a!==$b)throw new RuntimeException($m!==''?$m:var_export($a,true).' !== '.var_export($b,true));}
function p3rm(string $path):void{if(!file_exists($path)&&!is_link($path))return;if(is_file($path)||is_link($path)){@unlink($path);return;}foreach(scandir($path)?:[] as $n){if($n==='.'||$n==='..')continue;p3rm($path.DIRECTORY_SEPARATOR.$n);}@rmdir($path);}
function p3file(string $dir,string $name,string $bytes):array{if(!is_dir($dir))mkdir($dir,0770,true);$p=$dir.'/'.$name;file_put_contents($p,$bytes);return ['name'=>$name,'type'=>'','tmp_name'=>$p,'error'=>UPLOAD_ERR_OK,'size'=>strlen($bytes)];}
function p3throws(callable $fn):void{try{$fn();}catch(RuntimeException){return;}throw new RuntimeException('Expected RuntimeException was not thrown');}

$root=sys_get_temp_dir().'/easyit-hf76-p3-'.bin2hex(random_bytes(6));
$tmp=$root.'/tmp';mkdir($tmp,0770,true);
$mgr=new DataFormFieldStorageManager($root);
$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=',true);
if($png===false)throw new RuntimeException('PNG fixture invalid');

$fsImage='';$fsPath='';
p3t('filesystem-image-store',function()use($mgr,$tmp,$png,&$fsImage,&$fsPath,$root){
    $f=p3file($tmp,'Foto Übung.png',$png);
    $fsImage=$mgr->storeUpload($f,'image',['type_settings'=>['storage_driver'=>'filesystem','accept'=>'image/png','max_bytes'=>1048576]],['project_id'=>7,'dataform_id'=>4,'field_name'=>'bild']);
    $d=DataFormFieldStorageManager::descriptor($fsImage);p3ok($d!==null);p3eq($d['storage'],'filesystem');p3eq($d['mime'],'image/png');p3eq($d['width'],1);p3eq($d['height'],1);p3eq($d['version'],2);
    $fsPath=$root.'/'.(string)$d['path'];p3ok(is_file($fsPath),'stored file missing');p3ok(str_ends_with($fsPath,'.png'),'safe extension missing');
});
p3t('filesystem-protection-created',function()use($root){$h=$root.'/storage/dataform/uploads/.htaccess';p3ok(is_file($h),'.htaccess missing');$c=(string)file_get_contents($h);p3ok(str_contains($c,'Require all denied'));p3ok(str_contains($c,'-ExecCGI'));});
p3t('filesystem-payload-roundtrip',function()use($mgr,$fsImage,$png){$p=$mgr->payload($fsImage);p3ok($p!==null);p3eq($p['bytes'],$png);p3eq($p['size'],strlen($png));});
p3t('public-descriptor-hides-physical-data',function()use($fsImage){$d=DataFormFieldStorageManager::publicDescriptor($fsImage);p3ok($d!==null);p3ok(!array_key_exists('path',$d));p3ok(!array_key_exists('data_base64',$d));});
p3t('safe-inline-image-preview',function()use($fsImage){$d=DataFormFieldStorageManager::descriptor($fsImage);p3ok($d!==null);p3ok(DataFormFieldStorageManager::inlinePreviewAllowed('image',(string)$d['mime'],[]));p3ok(!DataFormFieldStorageManager::inlinePreviewAllowed('image',(string)$d['mime'],['type_settings'=>['image_preview'=>false]]));p3ok(!DataFormFieldStorageManager::inlinePreviewAllowed('file',(string)$d['mime'],[]));});

$dbFile='';
p3t('database-file-store',function()use($mgr,$tmp,&$dbFile){$bytes="Hallo HF76\n";$f=p3file($tmp,'notiz.txt',$bytes);$dbFile=$mgr->storeUpload($f,'file',['type_settings'=>['storage_driver'=>'database','accept'=>'text/plain','max_bytes'=>1048576]],[]);$d=DataFormFieldStorageManager::descriptor($dbFile);p3ok($d!==null);p3eq($d['storage'],'database');p3ok(isset($d['data_base64']));p3ok(!isset($d['path']));$p=$mgr->payload($dbFile);p3ok($p!==null);p3eq($p['bytes'],$bytes);});
p3t('database-checksum-tamper-detected',function()use($mgr,$dbFile){$d=json_decode($dbFile,true,512,JSON_THROW_ON_ERROR);$d['data_base64']=base64_encode('manipuliert');$bad=json_encode($d,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);p3eq($mgr->payload($bad),null);});
p3t('image-rejects-text-mime',function()use($mgr,$tmp){$f=p3file($tmp,'fake.png','not an image');p3throws(fn()=> $mgr->storeUpload($f,'image',['type_settings'=>['storage_driver'=>'database','accept'=>'image/*']],[]));});
p3t('image-rejects-svg-active-format',function()use($mgr,$tmp){$f=p3file($tmp,'vector.svg','<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');p3throws(fn()=> $mgr->storeUpload($f,'image',['type_settings'=>['storage_driver'=>'database','accept'=>'image/*']],[]));});
p3t('upload-size-limit-enforced',function()use($mgr,$tmp){$f=p3file($tmp,'big.txt',str_repeat('A',2048));p3throws(fn()=> $mgr->storeUpload($f,'file',['type_settings'=>['storage_driver'=>'database','accept'=>'text/plain','max_bytes'=>1024]],[]));});
p3t('unknown-mime-gets-bin-extension',function()use($mgr,$tmp,$root){$f=p3file($tmp,'payload.php',"\x00\x01\x02\x03\x04\x05");$v=$mgr->storeUpload($f,'file',['type_settings'=>['storage_driver'=>'filesystem','accept'=>'application/octet-stream','max_bytes'=>1048576]],['project_id'=>1,'dataform_id'=>1,'field_name'=>'file']);$d=DataFormFieldStorageManager::descriptor($v);p3ok($d!==null);p3ok(str_ends_with((string)$d['path'],'.bin'),'unexpected executable/client extension');$mgr->deleteManagedValue($v);});
p3t('descriptor-rejects-path-traversal',function(){$bad=json_encode(['version'=>2,'storage'=>'filesystem','name'=>'a.txt','mime'=>'text/plain','size'=>1,'sha256'=>str_repeat('a',64),'path'=>'storage/dataform/uploads/../secret'],JSON_THROW_ON_ERROR);p3eq(DataFormFieldStorageManager::descriptor($bad),null);});
p3t('filename-is-sanitized',function()use($mgr,$tmp){$f=p3file($tmp,'raw.txt',"hello world\n");$f['name']="../../evil\r\nname.php";$v=$mgr->storeUpload($f,'file',['type_settings'=>['storage_driver'=>'database','accept'=>'text/plain']],[]);$d=DataFormFieldStorageManager::descriptor($v);p3ok($d!==null);p3ok(!str_contains((string)$d['name'],'/'));p3ok(!str_contains((string)$d['name'],"\n"));});
p3t('filesystem-tamper-detected',function()use($mgr,$fsImage,$fsPath){file_put_contents($fsPath,'tampered');p3eq($mgr->payload($fsImage),null);});
p3t('filesystem-delete-managed',function()use($mgr,$tmp,$png,$root){$f=p3file($tmp,'delete.png',$png);$v=$mgr->storeUpload($f,'image',['type_settings'=>['storage_driver'=>'filesystem','accept'=>'image/png']],['project_id'=>9,'dataform_id'=>9,'field_name'=>'delete']);$d=DataFormFieldStorageManager::descriptor($v);p3ok($d!==null);$path=$root.'/'.(string)$d['path'];p3ok(is_file($path));p3ok($mgr->deleteManagedValue($v));p3ok(!file_exists($path));});
p3t('database-delete-is-noop',function()use($mgr,$dbFile){p3eq($mgr->deleteManagedValue($dbFile),false);p3ok($mgr->payload($dbFile)!==null);});
p3t('media-endpoint-security-headers-present',function(){ $src=(string)file_get_contents(__DIR__.'/products/dataform/media.php');foreach(['X-Content-Type-Options: nosniff','Content-Security-Policy: sandbox','Cross-Origin-Resource-Policy: same-origin','filename*=UTF-8'] as $needle)p3ok(str_contains($src,$needle),'missing '.$needle);});
p3t('record-replacement-deletes-old-after-commit',function(){ $src=(string)file_get_contents(__DIR__.'/products/dataform/records.php');$commit=strpos($src,'$recordSaveCommitted=true;');$del=strpos($src,'$mediaManager->deleteManagedValue($oldMediaValue);');p3ok($commit!==false&&$del!==false&&$commit<$del,'old media cleanup ordering invalid');});
p3t('record-failure-cleans-new-media',function(){ $src=(string)file_get_contents(__DIR__.'/products/dataform/records.php');p3ok(str_contains($src,'empty($recordSaveCommitted ?? false)'));p3ok(str_contains($src,'$newMediaOnFailure'));});
p3t('record-delete-and-bulk-delete-clean-media',function(){ $src=(string)file_get_contents(__DIR__.'/products/dataform/records.php');p3ok(substr_count($src,'deleteManagedValue($deleteMediaValue)')>=2,'delete cleanup missing');});
p3t('edit-ui-offers-media-preview-download-remove',function(){ $src=(string)file_get_contents(__DIR__.'/products/dataform/records.php');foreach(['df-media-existing','Vorschau','Download','vorhandene Datei entfernen'] as $needle)p3ok(str_contains($src,$needle),'missing '.$needle);});

p3rm($root);
echo "\nPASS=".count($passes)." FAIL=".count($failures)."\n";
exit($failures?1:0);
