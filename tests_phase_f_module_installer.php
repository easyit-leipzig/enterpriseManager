<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Core\Filesystem\Filesystem; use DataForm5\Modules\SDK\{ModuleScaffolder,ModuleValidator}; use DataForm5\Modules\Packages\{ModuleArchiveExtractor,ModulePackageInstaller,ModulePackageRegistry};
$base=__DIR__; $tmp=$base.'/DataForm5-Core/storage/framework/phase-f-test'; $mods=$tmp.'/modules'; $src=$tmp.'/source'; $fs=new Filesystem(); if(is_dir($tmp))$fs->delete($tmp);$fs->ensureDirectory($src);
try{
 $sc=new ModuleScaffolder();$r=$sc->create($src,'phase-f-test','EasyIT\\Modules\\PhaseF','PhaseFModule','Phase F test');
 $reg=new ModulePackageRegistry($tmp.'/installed.json',$fs);$installer=new ModulePackageInstaller($mods,$tmp.'/work',$fs,new ModuleValidator(),$reg,new ModuleArchiveExtractor($fs));
 $a=$installer->installDirectory($r['directory']);if(($a['module']['version']??'')!=='0.1.0')throw new RuntimeException('Installation fehlgeschlagen.');
 $mf=$r['directory'].'/module.json';$d=json_decode((string)file_get_contents($mf),true);$d['version']='0.2.0';file_put_contents($mf,json_encode($d,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
 $u=$installer->updateDirectory($r['directory']);if(($u['module']['version']??'')!=='0.2.0')throw new RuntimeException('Update fehlgeschlagen.');
 $installer->uninstall('phase-f-test');if(is_dir($mods.'/phase-f-test'))throw new RuntimeException('Deinstallation fehlgeschlagen.');
 if(class_exists(ZipArchive::class)===false){try{$installer->installArchive($tmp.'/missing.zip');throw new RuntimeException('ZipArchive-Prüfung fehlt.');}catch(RuntimeException $e){if(!str_contains($e->getMessage(),'ext-zip'))throw $e;}}
 echo "PHASE_F_MODULE_INSTALLER_OK\n";
} finally {if(is_dir($tmp))$fs->delete($tmp);}
