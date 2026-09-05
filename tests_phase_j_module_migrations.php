<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';

use DataForm5\Modules\Migrations\{ModuleMigrationRepository,ModuleMigrationManager};

$required=[
    __DIR__.'/DataForm5-Core/system/modules/Migrations/ModuleMigrationRepository.php',
    __DIR__.'/DataForm5-Core/system/modules/Migrations/ModuleMigrationManager.php',
    __DIR__.'/tools/module-migrate.php',
    __DIR__.'/tools/module-migration-status.php',
    __DIR__.'/tools/module-migration-rollback.php',
    __DIR__.'/DataForm5-Core/docs/MODULE_MIGRATIONS.md',
];
foreach($required as $file) if(!is_file($file)) throw new RuntimeException('Fehlt: '.$file);
$ref=new ReflectionClass(ModuleMigrationManager::class);
foreach(['status','migrate','rollbackLastBatch'] as $method) if(!$ref->hasMethod($method)) throw new RuntimeException('Methode fehlt: '.$method);
$repo=new ReflectionClass(ModuleMigrationRepository::class);
foreach(['applied','nextBatch','record','forget'] as $method) if(!$repo->hasMethod($method)) throw new RuntimeException('Repository-Methode fehlt: '.$method);
$installer=file_get_contents(__DIR__.'/DataForm5-Core/system/modules/Packages/ModulePackageInstaller.php');
if(!is_string($installer)||!str_contains($installer,'ModuleMigrationManager')||!str_contains($installer,'->migrate(')) throw new RuntimeException('Installer ist nicht mit Migrationen verbunden.');
$scaffolder=file_get_contents(__DIR__.'/DataForm5-Core/system/modules/SDK/ModuleScaffolder.php');
if(!is_string($scaffolder)||!str_contains($scaffolder,"'database/migrations'")) throw new RuntimeException('SDK erzeugt keinen Migrationsordner.');
echo "PHASE_J_MODULE_MIGRATIONS_OK\n";
