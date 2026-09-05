<?php
declare(strict_types=1);
$root=__DIR__;
$files=[
'app/modules/index.php',
'DataForm5-Core/system/modules/Packages/ModulePackageInstaller.php',
'system/ui/layout.php',
'docs/PHASE_G_MODULE_ADMIN.md'
];
foreach($files as $file){if(!is_file($root.'/'.$file)){fwrite(STDERR,"MISSING: {$file}\n");exit(1);}}
$page=file_get_contents($root.'/app/modules/index.php')?:'';
foreach(['install','update','enable','disable','remove','enterprise_check_csrf','is_uploaded_file'] as $needle){if(!str_contains($page,$needle)){fwrite(STDERR,"MISSING_FEATURE: {$needle}\n");exit(1);}}
$installer=file_get_contents($root.'/DataForm5-Core/system/modules/Packages/ModulePackageInstaller.php')?:'';
if(!str_contains($installer,'setEnabled')){fwrite(STDERR,"MISSING_FEATURE: setEnabled\n");exit(1);}
echo "PHASE_G_MODULE_ADMIN_OK\n";
