<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Modules\Core\{ModuleManager,ModuleRegistry};
use DataForm5\Modules\UI\ModuleUiRegistry;
$dir=sys_get_temp_dir().'/easyit_phase_m_'.bin2hex(random_bytes(4)); mkdir($dir.'/demo',0777,true);
file_put_contents($dir.'/demo/module.json',json_encode(['name'=>'demo','version'=>'1.0.0','entry'=>'Demo\\Module','enabled'=>true,'capabilities'=>['demo.view'],'ui'=>['navigation'=>[['label'=>'Demo','href'=>'modules/demo/index.php','capability'=>'demo.view']],'dashboard'=>[['label'=>'Demo','href'=>'modules/demo/index.php','capability'=>'demo.view']]]]));
$manager=new ModuleManager(new ServiceContainer(),new ModuleRegistry()); $manager->discover($dir); $ui=new ModuleUiRegistry($manager);
function enterprise_can(array $user,string $capability): bool { return in_array($capability,$user['permissions']??[],true); }
if(count($ui->navigation(['permissions'=>['demo.view']]))!==1) exit("PHASE_M_FAIL_NAV\n");
if(count($ui->navigation(['permissions'=>[]]))!==0) exit("PHASE_M_FAIL_CAP\n");
if(count($ui->dashboard(['permissions'=>['demo.view']]))!==1) exit("PHASE_M_FAIL_DASH\n");
@unlink($dir.'/demo/module.json'); @rmdir($dir.'/demo'); @rmdir($dir);
echo "PHASE_M_MODULE_UI_OK\n";
