<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$version=trim((string)file_get_contents($root.'/VERSION'));
$excluded=[
 'storage/test-runtime/','DataForm5-Core/storage/test-runtime/',
 'storage/cache/','storage/logs/','storage/sessions/','storage/tmp/',
 'DataForm5-Core/storage/framework/cache/','DataForm5-Core/storage/framework/sessions/',
 'DataForm5-Core/storage/logs/','DataForm5-Core/storage/tmp/',
 'backup/easyit-full-backup-'
];
$skip=function(string $rel)use($excluded):bool{
 if(in_array($rel,['RELEASE_MANIFEST.json','FILE_MANIFEST_SHA256.txt'],true))return true;
 foreach($excluded as $prefix)if(str_starts_with($rel,$prefix))return true;
 return false;
};
$files=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
foreach($it as $f){
 if(!$f->isFile())continue;
 $path=$f->getPathname();$rel=str_replace('\\','/',substr($path,strlen($root)+1));
 if($skip($rel))continue;
 $files[$rel]=['sha256'=>hash_file('sha256',$path),'size'=>(int)(filesize($path)?:0)];
}
ksort($files,SORT_STRING);
$m=['format'=>'easyit-enterprise-release-manifest/1','version'=>$version,'release_family'=>'RC1.8','build_phase'=>'HF76-FINAL',
'php'=>['minimum'=>'8.2'],'excluded_runtime_prefixes'=>$excluded,'files'=>$files,
'summary'=>['files'=>count($files),'bytes'=>array_sum(array_column($files,'size'))]];
$json=json_encode($m,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n";
file_put_contents($root.'/RELEASE_MANIFEST.json',$json);
echo hash('sha256',$json)."  RELEASE_MANIFEST.json\n";
