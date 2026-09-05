<?php
declare(strict_types=1);

$root=dirname(__DIR__);
require $root.'/products/dataform/system/DataFormTransport.php';
require $root.'/products/dataform/system/ApiDesigner.php';

$tests=[];
function t(string $name,bool $ok,string $detail=''): void {
    global $tests;
    $tests[]=[$name,$ok,$detail];
    echo ($ok?'[PASS] ':'[FAIL] ').$name.($detail!==''?' – '.$detail:'').PHP_EOL;
    if (!$ok) throw new RuntimeException('Test fehlgeschlagen: '.$name);
}

$types=DataFormFieldTypeRegistry::allowedTypes();
t('Registry enthält 33 Feldtypen',count($types)===33,'count='.count($types));
foreach (['mysql','mariadb','sqlite','oracle','csv'] as $driver) {
    $missing=[];
    foreach ($types as $type) {
        $sql=DataFormFieldTypeRegistry::sqlType($type,$driver,[]);
        if (trim($sql)==='') $missing[]=$type;
    }
    t('Backend-Mapping '.$driver,$missing===[],$missing?implode(',',$missing):'33/33');
}

$tmp=sys_get_temp_dir().'/easyit-hf76-p4-'.bin2hex(random_bytes(6));
mkdir($tmp,0770,true);
$storage=new DataFormFieldStorageManager($tmp);
$fields=[
    ['name'=>'qty','label'=>'Menge','field_type'=>'integer','is_required'=>1,'configuration_json'=>'{}','configuration'=>[]],
    ['name'=>'active','label'=>'Aktiv','field_type'=>'boolean','is_required'=>0,'configuration_json'=>'{}','configuration'=>[]],
    ['name'=>'meta','label'=>'JSON','field_type'=>'json','is_required'=>0,'configuration_json'=>'{}','configuration'=>[]],
    ['name'=>'tags','label'=>'Tags','field_type'=>'tags','is_required'=>0,'configuration_json'=>'{}','configuration'=>[]],
    ['name'=>'link','label'=>'Link','field_type'=>'link','is_required'=>0,'configuration_json'=>'{}','configuration'=>[]],
    ['name'=>'file','label'=>'Datei','field_type'=>'file','is_required'=>0,
        'configuration_json'=>json_encode(['type_settings'=>['storage_driver'=>'database','accept'=>'text/plain','max_bytes'=>1024*1024]]),
        'configuration'=>['type_settings'=>['storage_driver'=>'database','accept'=>'text/plain','max_bytes'=>1024*1024]]],
    ['name'=>'computed','label'=>'Berechnet','field_type'=>'computed','is_required'=>0,
        'configuration_json'=>json_encode(['type_settings'=>['template'=>'Q={{qty}}']]),
        'configuration'=>['type_settings'=>['template'=>'Q={{qty}}']]],
];
$input=[
    'qty'=>'12','active'=>true,'meta'=>['a'=>1],'tags'=>['x','y','x'],
    'link'=>['url'=>'https://example.org/x','label'=>'X','target'=>'_blank'],
    'file'=>['name'=>'hello.txt','data_base64'=>base64_encode('hello transport')],
];
$norm=DataFormTransport::normalizeIncoming($fields,$input,[],$storage,['project_id'=>7,'dataform_id'=>9],false);
t('Transport-Normalisierung integer',$norm['data']['qty']==='12');
t('Transport-Normalisierung boolean',$norm['data']['active']==='1');
t('Transport-Normalisierung JSON',json_decode($norm['data']['meta'],true)===['a'=>1]);
t('Transport-Normalisierung tags',json_decode($norm['data']['tags'],true)===['x','y']);
t('Transport-Normalisierung computed',$norm['data']['computed']==='Q=12');
$media=DataFormFieldStorageManager::descriptor($norm['data']['file']);
t('REST/CSV Media DB-Descriptor',is_array($media) && ($media['storage']??'')==='database' && ($media['size']??0)===15);
$external=DataFormTransport::externalize($fields,$norm['data'],$storage,false);
t('Externalisierung native Zahl',$external['qty']===12);
t('Externalisierung native Boolean',$external['active']===true);
t('Externalisierung JSON-Objekt',is_array($external['meta']) && $external['meta']['a']===1);
t('Externalisierung Media ohne Bytes',is_array($external['file']) && !array_key_exists('data_base64',$external['file']));
$csv=DataFormTransport::csvValue($fields[5],$norm['data']['file'],$storage);
$csvMedia=json_decode($csv,true);
t('CSV Media portabel',is_array($csvMedia) && base64_decode((string)$csvMedia['data_base64'],true)==='hello transport');

$fsCfg=['type_settings'=>['storage_driver'=>'filesystem','accept'=>'text/plain','max_bytes'=>1024*1024]];
$fs=$storage->storeBytes('filesystem payload','note.txt','file',$fsCfg,['project_id'=>7,'dataform_id'=>9,'field_name'=>'file']);
$fsDesc=DataFormFieldStorageManager::descriptor($fs);
t('Filesystem-Transport speichert verwaltet',is_array($fsDesc) && ($fsDesc['storage']??'')==='filesystem' && str_starts_with((string)$fsDesc['path'],'storage/dataform/uploads/project-7/dataform-9/file/'));
$payload=$storage->payload($fs);
t('Filesystem-Transport Roundtrip',is_array($payload) && $payload['bytes']==='filesystem payload');
t('Filesystem-Transport Löschung',$storage->deleteManagedValue($fs));

$schema=DataFormTransport::openApiRecordSchema($fields,true);
t('OpenAPI Input Media data_base64',isset($schema['properties']['file']['properties']['data_base64']));
t('OpenAPI computed nicht beschreibbar',!isset($schema['properties']['computed']));
t('OpenAPI Pflichtfeld',$schema['required']===['qty']);
$api=['name'=>'Test API','version'=>'1.0.0','description'=>''];
$eps=[['name'=>'Liste','slug'=>'items','http_method'=>'GET','operation'=>'list','requires_auth'=>1,'dataform_slug'=>'sample'],['name'=>'Neu','slug'=>'items-create','http_method'=>'POST','operation'=>'create','requires_auth'=>1,'dataform_slug'=>'sample']];
$dfs=[['slug'=>'sample','fields'=>$fields]];
$spec=ApiDesigner::openApi($api,$eps,$dfs,'https://example.test/api-runtime.php/7/test');
t('OpenAPI Response-Schema vorhanden',isset($spec['components']['schemas']['sample']));
t('OpenAPI Input-Schema vorhanden',isset($spec['components']['schemas']['sampleInput']));
t('OpenAPI REST-Pfad',isset($spec['paths']['/items']['get']));

$apiRuntime=file_get_contents($root.'/products/dataform/api-runtime.php')?:'';
t('API nutzt DataFormRecordStore',str_contains($apiRuntime,'DataFormRecordStore::all') && str_contains($apiRuntime,'DataFormRecordStore::create') && !str_contains($apiRuntime,'INSERT INTO dataform_records'));
$importSource=file_get_contents($root.'/products/dataform/import.php')?:'';
t('CSV Import nutzt DataFormRecordStore',str_contains($importSource,'DataFormRecordStore::create') && str_contains($importSource,'DataFormRecordStore::update') && !str_contains($importSource,"INSERT INTO dataform_records (dataform_id,data_json)"));
$packageSource=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php')?:'';
t('.dfpkg Format 1.2 Media',str_contains($packageSource,"'formatVersion'=>'1.2'") && str_contains($packageSource,"'media/index.json'") && str_contains($packageSource,'storeBytes($bytes'));
$ht=file_get_contents($root.'/products/dataform/.htaccess')?:'';
t('REST PATH_INFO aktiviert',str_contains($ht,'AcceptPathInfo On'));

DataFormTransport::cleanup($norm['created_media'],$storage);
function rrmdir(string $dir): void { if (!is_dir($dir)) return; foreach (scandir($dir)?:[] as $item) { if ($item==='.'||$item==='..') continue; $p=$dir.'/'.$item; is_dir($p)?rrmdir($p):@unlink($p); } @rmdir($dir); }
rrmdir($tmp);

$pass=count(array_filter($tests,static fn(array $x):bool=>$x[1]));
echo 'RESULT '.$pass.'/'.count($tests).' PASS'.PHP_EOL;
