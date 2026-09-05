<?php
declare(strict_types=1);
$root=__DIR__;$fail=[];
$check=function(bool $ok,string $msg)use(&$fail){echo($ok?'PASS ':'FAIL ').$msg.PHP_EOL;if(!$ok)$fail[]=$msg;};
$r=(string)file_get_contents($root.'/recovery.php');
$m=(string)file_get_contents($root.'/tools/rc18-build-release-manifest.php');
$check(str_contains($r,"\$backupRoot=\$root.'/backup'"),'Recovery verwendet Projektordner backup/');
$check(str_contains($r,'recovery_backup_create'),'Vollbackup-Erzeugung vorhanden');
$check(str_contains($r,'recovery_backup_restore'),'Vollrestore vorhanden');
$check(str_contains($r,"'project/DataForm5-Core/.env'"),'Restore liest .env aus Backup');
$check(str_contains($r,"'databases/'"),'Backup enthält Datenbank-Dumps');
$check(str_contains($r,'password_hash'),'UI dokumentiert Admin-Passwort-Hash');
$check(str_contains($r,'RESTORE EASYIT'),'Restore verlangt explizite Bestätigung');
$check(str_contains($r,'DROP DATABASE IF EXISTS'),'Restore ersetzt Datenbanken');
$check(str_contains($r,'--EASYIT-STMT:'),'DB-Dump verwendet robuste Statement-Kapselung');
$check(str_contains($r,"str_starts_with(\$rel,'backup/')"),'Restore schützt Backup-Ordner vor Bereinigung');
$check(str_contains($m,'backup/easyit-full-backup-'),'Release-Manifest ignoriert erzeugte Runtime-Backups');
$check(is_file($root.'/backup/.htaccess'),'Backup-Ordner ist gegen HTTP-Zugriff geschützt');
exit($fail?1:0);
