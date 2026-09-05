<?php
declare(strict_types=1);
$root=__DIR__;
$required=[
$root.'/DataForm5-Core/system/installer/Core/EnterpriseInstaller.php',
$root.'/DataForm5-Core/system/installer/Core/EnvironmentWriter.php',
$root.'/DataForm5-Core/system/installer/Core/AdminDatabaseInstaller.php',
$root.'/DataForm5-Core/system/installer/Core/InstallationHealthGate.php',
$root.'/tools/installer-preflight.php',
$root.'/docs/RC1.8_PHASE3_INSTALLER2.md'];
foreach($required as $file)if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}
$setup=(string)file_get_contents($root.'/setup.php');
foreach(['Installer 2.0','admin_password','products[]','Health-Gate'] as $needle)if(!str_contains($setup,$needle)){fwrite(STDERR,"Setup missing {$needle}\n");exit(2);}
foreach(['$value[\'name\']','$value[\'passed\']','$value[\'required\']','$value[\'message\']'] as $needle)if(!str_contains($setup,$needle)){fwrite(STDERR,"Setup inspector mapping missing {$needle}\n");exit(5);}
$databaseInstaller=(string)file_get_contents($root.'/installer/database.php');
if(str_contains($databaseInstaller,'CURRENT_USER() AS current_user')){fwrite(STDERR,"Database installer uses reserved current_user alias\n");exit(6);}
foreach(['CURRENT_USER() AS authenticated_user',"\$row['authenticated_user']"] as $needle)if(!str_contains($databaseInstaller,$needle)){fwrite(STDERR,"Database diagnostics mapping missing {$needle}\n");exit(7);}
if(str_contains($databaseInstaller,'$server->beginTransaction()')||str_contains($databaseInstaller,'$server->commit()')){fwrite(STDERR,"Database schema runner must not wrap MySQL/MariaDB DDL in PDO transactions\n");exit(8);}
foreach(['$installer($server);','registerMigration($server, $name, $checksum);'] as $needle)if(!str_contains($databaseInstaller,$needle)){fwrite(STDERR,"Database schema runner missing {$needle}\n");exit(9);}
$installer=(string)file_get_contents($root.'/DataForm5-Core/system/installer/Core/EnterpriseInstaller.php');
if(strpos($installer,'$this->health->check')>strpos($installer,'$this->lock->create')){fwrite(STDERR,"Lock happens before health gate\n");exit(3);}
$env=(string)file_get_contents($root.'/DataForm5-Core/config/installer.php');
if(str_contains($env,'$_ENV')){fwrite(STDERR,"Installer config bypasses Env abstraction\n");exit(4);}
echo "RC18_PHASE3_INSTALLER2_OK\n";
