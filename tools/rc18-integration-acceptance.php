<?php
declare(strict_types=1);
$root=dirname(__DIR__);$checks=[];
$check=function(string $name,bool $ok,string $detail='')use(&$checks):void{$checks[]=['name'=>$name,'status'=>$ok?'PASS':'FAIL','detail'=>$detail];};
$check('Enterprise bootstrap',is_file($root.'/system/app/bootstrap.php'));
$check('DataForm5 Core',is_dir($root.'/DataForm5-Core/system'));
$check('DataForm product',is_dir($root.'/products/dataform'));
$check('License management',is_file($root.'/DataForm5-Core/system/licensing/Core/LicenseManager.php')&&is_file($root.'/DataForm5-Core/system/licensing/Providers/LicensingServiceProvider.php'));
$check('Installer',is_dir($root.'/installer')&&is_file($root.'/installer/schema/admin/004_licensing.php'));
$check('Developer dashboard',is_file($root.'/app/developer/index.php'));
$check('SDK documentation',is_file($root.'/docs/SDK/README.md'));
$check('Module quality gate',is_file($root.'/DataForm5-Core/system/modules/SDK/ModuleQualityValidator.php'));
$check('Module packager',is_file($root.'/DataForm5-Core/system/modules/SDK/ModulePackager.php'));
$check('Quality center',is_file($root.'/DataForm5-Core/system/testing/Core/DeveloperQualityCenter.php'));
$php=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
foreach($it as $f)if($f->isFile()&&strtolower($f->getExtension())==='php'&&!str_contains(str_replace('\\','/',$f->getPathname()),'/storage/'))$php[]=$f->getPathname();
$bad=[];foreach($php as $file){$out=[];$code=0;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1',$out,$code);if($code!==0)$bad[]=str_replace($root.'/','',$file);}
$check('PHP syntax',$bad===[],count($php).' Dateien'.($bad?'; '.implode(', ',$bad):''));
$temp=[];foreach(['*.tmp','*.bak','*.orig'] as $pattern)foreach(glob($root.'/'.$pattern)?:[] as $f)$temp[]=basename($f);
$check('Root cleanup',$temp===[],$temp?implode(', ',$temp):'keine temporären Root-Dateien');
$failed=array_values(array_filter($checks,fn($c)=>$c['status']==='FAIL'));
$r=['release'=>'RC1.8','phase'=>'6.1','status'=>$failed===[]?'PASS':'FAIL','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>count($failed),'php_files'=>count($php)]];
echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($failed===[]?0:1);
