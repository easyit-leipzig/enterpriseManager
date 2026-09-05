<?php
declare(strict_types=1);
use DataForm5\Installer\Core\InstallationLock;use DataForm5\Installer\Core\Installer;use DataForm5\Installer\Core\SystemInspector;use DataForm5\Installer\Exceptions\InstallerException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$base=sys_get_temp_dir().'/df5-installer-'.bin2hex(random_bytes(4));mkdir($base.'/storage',0775,true);file_put_contents($base.'/VERSION','0.41.0-dev');
$lock=new InstallationLock($base.'/storage/framework/installer/installed.json');$installer=new Installer($base,new SystemInspector($base,'8.0.0'),$lock);$inspection=$installer->inspect();assert($inspection['ready']===true);$status=$installer->install(['environment'=>'testing','application_name'=>'Test']);assert($status['installed']===true);assert($status['lock']['environment']==='testing');assert(is_dir($base.'/storage/framework/cache'));
$thrown=false;try{$installer->install();}catch(InstallerException){$thrown=true;}assert($thrown===true);
$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($iterator as $item){$item->isDir()?rmdir($item->getPathname()):unlink($item->getPathname());}rmdir($base);echo "PASS: Installation, Bootstrap and First-Run Layer\n";
