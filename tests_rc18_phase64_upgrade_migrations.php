<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/installer/MIGRATION_MANIFEST.json',
$root.'/tools/rc18-upgrade-migration-audit.php',
$root.'/docs/RC1.8_PHASE6.4_UPGRADE_MIGRATIONS.md'
] as $file)if(!is_file($file))exit(1);
$runner=(string)file_get_contents($root.'/installer/database.php');
foreach(['migrationRecord','registerMigration','hash_equals','SKIP ','APPLY '] as $needle)
    if(!str_contains($runner,$needle))exit(2);
if(str_contains($runner,'$server->beginTransaction()')||str_contains($runner,'$server->commit()')||str_contains($runner,'$server->rollBack()'))exit(3);
echo "RC18_PHASE64_UPGRADE_MIGRATIONS_OK\n";
