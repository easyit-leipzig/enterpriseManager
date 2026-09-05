<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Modules\SDK\ModuleScaffolder;
use DataForm5\Modules\SDK\ModuleValidator;
$tmp=sys_get_temp_dir().'/easyit-module-sdk-'.bin2hex(random_bytes(4)); mkdir($tmp,0775,true);
try{
 $r=(new ModuleScaffolder())->create($tmp,'sdk-test','EasyIT\\Modules\\SdkTest','SdkTestModule','SDK test');
 $v=(new ModuleValidator())->validateDirectory($r['directory']);
 if(!$v['valid']) throw new RuntimeException(implode('; ',$v['errors']));
 foreach(['module.json','bootstrap.php','src/SdkTestModule.php','README.md','tests/smoke.php'] as $f) if(!is_file($r['directory'].'/'.$f)) throw new RuntimeException("Fehlt: {$f}");
 echo "PHASE_E_MODULE_SDK_OK\n";
} finally {
 if(is_dir($tmp)){ $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST); foreach($it as $f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());} rmdir($tmp); }
}
