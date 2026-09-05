<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Modules\Versioning\VersionConstraint;
use DataForm5\Modules\Versioning\ModuleCompatibility;
use DataForm5\Modules\Core\ModuleManifest;
$ok=true;
$cases=[['1.7.8','>=1.7.1',true],['1.7.8','^1.7.0',true],['2.0.0','^1.7.0',false],['1.7.8','~1.7.2',true],['1.8.0','~1.7.2',false]];
foreach($cases as [$v,$c,$expected])if(VersionConstraint::matches($v,$c)!==$expected){$ok=false;echo "FAIL {$v} {$c}\n";}
$tmp=tempnam(sys_get_temp_dir(),'module-i-');file_put_contents($tmp,json_encode(['name'=>'phase-i-test','version'=>'1.0.0','entry'=>'X\\Y','dependencies'=>['dep-a'=>'^2.0.0'],'core_version'=>'>=1.7.1']));
$m=ModuleManifest::fromFile($tmp);@unlink($tmp);
if($m->dependencyConstraints!==['dep-a'=>'^2.0.0']||$m->dependencies!==['dep-a'])$ok=false;
$c=new ModuleCompatibility('RC1.7.8-dev-phaseH');$r=$c->check($m,['dep-a'=>['version'=>'2.3.0']]);if(!$r['compatible'])$ok=false;
if($ok){echo "PHASE_I_MODULE_VERSIONING_OK\n";exit(0);}echo "PHASE_I_MODULE_VERSIONING_FAIL\n";exit(1);
