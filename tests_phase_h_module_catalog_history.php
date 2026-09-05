<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Core\Filesystem\Filesystem;
use DataForm5\Modules\Packages\ModuleInstallHistory;
$root=__DIR__;
foreach(['DataForm5-Core/system/modules/Packages/ModuleInstallHistory.php','DataForm5-Core/system/modules/Packages/ModuleCatalog.php','docs/PHASE_H_MODULE_CATALOG_HISTORY.md','app/modules/index.php'] as $f){if(!is_file($root.'/'.$f)){fwrite(STDERR,"MISSING: $f\n");exit(1);}}
$tmp=$root.'/DataForm5-Core/storage/framework/phase-h-test';$fs=new Filesystem();if(is_dir($tmp))$fs->delete($tmp);$fs->ensureDirectory($tmp);
try{$h=new ModuleInstallHistory($tmp.'/history.json',$fs);$h->add('install','demo','success',['version'=>'1.0.0']);$r=$h->recent();if(count($r)!==1||($r[0]['module']??'')!=='demo')throw new RuntimeException('Historie funktioniert nicht.');
$page=file_get_contents($root.'/app/modules/index.php')?:'';foreach(['Modulkatalog','Installationshistorie','availablePackages','ModuleCatalog'] as $n){if(!str_contains($page,$n))throw new RuntimeException("UI fehlt: $n");}
echo "PHASE_H_MODULE_CATALOG_HISTORY_OK\n";}finally{if(is_dir($tmp))$fs->delete($tmp);}
