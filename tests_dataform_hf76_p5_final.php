<?php
declare(strict_types=1);
$root=__DIR__;
$checks=[];
function p5(string $name,bool $ok,string $detail=''):void{global $checks;$checks[]=[$name,$ok,$detail];echo ($ok?'[PASS] ':'[FAIL] ').$name.($detail!==''?' – '.$detail:'').PHP_EOL;}
$version=trim((string)file_get_contents($root.'/VERSION'));
p5('final-version',$version==='RC1.8-FC1-HF76',$version);
p5('real-env-not-shipped',!is_file($root.'/DataForm5-Core/.env'));
$recoveryEnvs=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/DataForm5-Core/storage/recovery',FilesystemIterator::SKIP_DOTS));foreach($it as $f)if($f->isFile()&&$f->getFilename()==='.env')$recoveryEnvs[]=$f->getPathname();
p5('recovery-envs-not-shipped',$recoveryEnvs===[],(string)count($recoveryEnvs));
require_once $root.'/products/dataform/system/DataFormFieldTypes.php';
p5('33-field-types',count(DataFormFieldTypeRegistry::allowedTypes())===33,(string)count(DataFormFieldTypeRegistry::allowedTypes()));
foreach(['json','link','image','file','integer','decimal','currency','percentage','time','phone','multiselect','tags','lookup','multi_lookup','uuid','coordinates','computed'] as $type)p5('type-'.$type,DataFormFieldTypeRegistry::has($type));
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
p5('final-workspace-badge',str_contains($records,'DataForm Workspace · HF76'));
p5('final-runtime-header',str_contains($runtime,'X-EasyIT-DataForm-Runtime: HF76'));
p5('lookup-wording',str_contains($records,'Lookup-Datensatz wählen'));
p5('derived-display-resolution',str_contains($records,'df_display_record_value($pdo,$field,$value)'));
p5('media-endpoint',is_file($root.'/products/dataform/media.php'));
p5('media-direct-access-denied',str_contains((string)file_get_contents($root.'/storage/dataform/uploads/.htaccess'),'Require all denied'));
p5('package-format-1.2',str_contains((string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php'),"'formatVersion'=>'1.2'"));
p5('transport-layer',is_file($root.'/products/dataform/system/DataFormTransport.php'));
p5('migration-guide',is_file($root.'/MIGRATION_HF19_TO_HF76.md'));
$bad=[];
foreach(['products','system','app','installer'] as $dir){$base=$root.'/'.$dir;if(!is_dir($base))continue;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if(!$f->isFile())continue;$s=@file_get_contents($f->getPathname());if(is_string($s)&&(str_contains($s,'D:\\xampp\\htdocs\\easyIT-Enterprise-RC1.8-FC1-HF19')||str_contains($s,'/easyIT-Enterprise-RC1.8-FC1-HF19')))$bad[]=substr($f->getPathname(),strlen($root)+1);}}
p5('no-production-hf19-paths',$bad===[],$bad===[]?'':implode(',',$bad));
$failed=array_filter($checks,static fn(array $r):bool=>!$r[1]);
echo 'RESULT '.(count($checks)-count($failed)).'/'.count($checks).' PASS'.PHP_EOL;
exit($failed===[]?0:1);
