<?php
declare(strict_types=1);

$required=[
__DIR__.'/DataForm5-Core/system/core/Filesystem/Contracts/StorageDriverInterface.php',
__DIR__.'/DataForm5-Core/system/core/Filesystem/Drivers/LocalStorageDriver.php',
__DIR__.'/DataForm5-Core/system/core/Filesystem/Drivers/SharedFilesystemStorageDriver.php',
__DIR__.'/DataForm5-Core/system/core/Filesystem/Drivers/S3CompatibleStorageDriver.php',
__DIR__.'/DataForm5-Core/system/core/Filesystem/StorageManager.php',
__DIR__.'/DataForm5-Core/system/core/Providers/StorageServiceProvider.php',
__DIR__.'/DataForm5-Core/config/storage.php',
__DIR__.'/app/storage/index.php',
__DIR__.'/tools/storage-status.php',
__DIR__.'/docs/PHASE_X_SHARED_STORAGE.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}
$app=(string)file_get_contents(__DIR__.'/DataForm5-Core/config/providers.php');
if(!str_contains($app,'StorageServiceProvider')){fwrite(STDERR,"Storage provider not registered\n");exit(2);}
$bootstrap=(string)file_get_contents(__DIR__.'/system/app/bootstrap.php');
foreach(['storage.view','storage.manage','enterprise_storage'] as $needle)if(!str_contains($bootstrap,$needle)){fwrite(STDERR,"Missing {$needle}\n");exit(3);}
$layout=(string)file_get_contents(__DIR__.'/system/ui/layout.php');
if(!str_contains($layout,"'operations' => ['Betrieb'") && !str_contains($layout,"'storage' => ['Storage'")) exit(4);
echo "PHASE_X_SHARED_STORAGE_OK\n";
