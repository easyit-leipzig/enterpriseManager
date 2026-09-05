<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Modules\Config\ModuleConfigSchema;
$dir=sys_get_temp_dir().'/easyit_phase_k_'.bin2hex(random_bytes(4));mkdir($dir.'/config',0777,true);
file_put_contents($dir.'/config/schema.php',"<?php return ['enabled'=>['type'=>'bool','default'=>true],'count'=>['type'=>'int','default'=>3],'token'=>['type'=>'string','secret'=>true]];");
$s=ModuleConfigSchema::fromModule($dir);$r=$s->validate(['enabled'=>'0','count'=>'5','token'=>'abc']);
if(!$r['valid']||$r['values']['enabled']!==false||$r['values']['count']!==5||$s->secrets()!==['token']){fwrite(STDERR,"PHASE_K_MODULE_CONFIGURATION_FAIL\n");exit(1);}unlink($dir.'/config/schema.php');rmdir($dir.'/config');rmdir($dir);echo "PHASE_K_MODULE_CONFIGURATION_OK\n";
