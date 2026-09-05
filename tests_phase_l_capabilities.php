<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Modules\Core\ModuleManifest;
$tmp=sys_get_temp_dir().'/df5cap'.bin2hex(random_bytes(4));mkdir($tmp);file_put_contents($tmp.'/module.json',json_encode(['name'=>'x','version'=>'1.0.0','entry'=>'X\M','capabilities'=>['x.view','x.manage']]));$m=ModuleManifest::fromFile($tmp.'/module.json');if($m->capabilities!==['x.view','x.manage'])exit(1);unlink($tmp.'/module.json');rmdir($tmp);echo 'PHASE_L_CAPABILITIES_OK'.PHP_EOL;
