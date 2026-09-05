<?php
declare(strict_types=1);
use DataForm5\Deployment\Core\ReleaseManager;
$root=dirname(__DIR__);$kernel=require $root.'/bootstrap/app.php';$manager=$kernel->container()->get(ReleaseManager::class);
$tmp=$root.'/storage/framework/deployment-test';if(is_dir($tmp)){array_map('unlink',glob($tmp.'/*')?:[]);rmdir($tmp);}mkdir($tmp,0775,true);file_put_contents($tmp.'/a.txt','alpha');
$manifest=$manager->createManifest($tmp,'5.0.0-test',['phase'=>24]);assert($manager->verifyManifest($tmp,$manifest)===true);file_put_contents($tmp.'/a.txt','changed');assert($manager->verifyManifest($tmp,$manifest)===false);
$manager->enterMaintenance('Update läuft');assert($manager->isMaintenance()===true);assert(($manager->maintenanceData()['message']??'')==='Update läuft');$manager->leaveMaintenance();assert($manager->isMaintenance()===false);
assert($manager->updateAvailable('99.0.0')===true);unlink($tmp.'/a.txt');rmdir($tmp);@unlink($root.'/storage/releases/release-5.0.0-test.json');echo "PASS: Deployment, Release and Update Layer\n";
