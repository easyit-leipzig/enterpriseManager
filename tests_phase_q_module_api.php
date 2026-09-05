<?php
declare(strict_types=1);
$files=[
__DIR__.'/DataForm5-Core/system/modules/Routing/ModuleApiRegistry.php',
__DIR__.'/DataForm5-Core/system/modules/Routing/ModuleApiDispatcher.php',
__DIR__.'/app/module-api.php',
__DIR__.'/docs/PHASE_Q_MODULE_API.md'];
foreach($files as $f)if(!is_file($f)){fwrite(STDERR,"Missing {$f}\n");exit(1);}
$m=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/modules/Core/ModuleManifest.php');
if(!str_contains($m,'apiRoutes'))exit(2);
$s=(string)file_get_contents(__DIR__.'/DataForm5-Core/system/modules/SDK/ModuleScaffolder.php');
if(!str_contains($s,"api_routes")||!str_contains($s,"ApiController.php"))exit(3);
echo "PHASE_Q_MODULE_API_OK\n";
