<?php
declare(strict_types=1);
$r=__DIR__;
foreach(['README.md','MODULE_ANATOMY.md','CLI_REFERENCE.md','LIFECYCLE.md','QUALITY_GATE.md','PACKAGING.md','EXAMPLES.md'] as $f)if(!is_file($r.'/docs/SDK/'.$f))exit(1);
echo "RC18_PHASE511_SDK_DOCUMENTATION_OK\n";
