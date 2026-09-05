<?php
declare(strict_types=1);
$r=__DIR__;
$checks=[];
$c=function(string $n,bool $ok)use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$idx=(string)file_get_contents($r.'/app/projects/index.php');
$restore=(string)file_get_contents($r.'/app/projects/restore.php');
$helper=(string)file_get_contents($r.'/system/app/project_restore.php');
$backup=(string)file_get_contents($r.'/system/app/project_backup.php');
$view=(string)file_get_contents($r.'/app/projects/view.php');
$c('project list exposes restore action',str_contains($idx,'restore.php')&&str_contains($idx,'Projektsicherung wiederherstellen'));
$c('restore page uses authenticated enterprise shell',str_contains($restore,"enterprise_require_auth('../../')")&&str_contains($restore,'RESTORE · HF73'));
$c('restore upload requires csrf and zip file',str_contains($restore,'csrf_token')&&str_contains($restore,'name="backup_zip"')&&str_contains($restore,'enctype="multipart/form-data"'));
$c('archive validation requires backup format and known files',str_contains($helper,'easyit-project-backup')&&str_contains($helper,"manifest.json")&&str_contains($helper,"project.json")&&str_contains($helper,"database.sql"));
$c('archive rejects traversal paths and is not blindly extracted',str_contains($helper,"preg_match('~(^|/)\\.\\.(/|$)~")&&!str_contains($helper,'extractTo('));
$c('restore previews are session protected and expiring',str_contains($helper,"\$_SESSION['project_restore_previews']")&&str_contains($helper,'time() + 7200')&&str_contains($helper,'realpath(enterprise_project_restore_ensure_root())'));
$c('restore allows project metadata overrides',str_contains($restore,'name="name"')&&str_contains($restore,'name="slug"')&&str_contains($restore,'name="database_name"'));
$c('database strategies expose auto reuse restore replace',str_contains($restore,'value="auto"')&&str_contains($restore,'value="reuse"')&&str_contains($restore,'value="restore"')&&str_contains($restore,'value="replace"'));
$c('replace requires exact database confirmation',str_contains($helper,'replaceConfirmation')&&str_contains($helper,'hash_equals($targetDatabase, trim($replaceConfirmation))'));
$c('protected enterprise databases cannot be restore targets',str_contains($helper,"'information_schema'")&&str_contains($helper,"ADMIN_DB_DATABASE")&&str_contains($helper,'geschützte System-/Enterprise-Datenbank'));
$c('restore sql supports mysql delimiter blocks',str_contains($helper,'DELIMITER')&&str_contains($helper,'enterprise_project_restore_execute_sql'));
$c('database references can be rewritten for alternate target',str_contains($helper,'enterprise_project_restore_rewrite_database_sql')&&str_contains($helper,'str_replace('));
$c('duplicate project slug or database is blocked before restore',str_contains($restore,'WHERE slug = ? OR database_name = ?')&&str_contains($restore,'Slug oder Zieldatenbank'));
$c('restore is audited and evented',str_contains($restore,"'project.restore'")&&str_contains($restore,"'project.restored'"));
$c('successful restore opens restored project with flash',str_contains($restore,"header('Location: view.php?id='")&&str_contains($view,"\$_SESSION['project_flash']"));
$c('new backup readme points to built in restore',str_contains($backup,'Projekte → Projektsicherung wiederherstellen'));

require_once $r.'/system/app/project_restore.php';
class Hf73FakePdo extends PDO {
    public array $statements=[];
    public function __construct() {}
    public function exec(string $statement): int|false { $this->statements[]=trim($statement); return 0; }
}
$fake=new Hf73FakePdo();
$sql="SET NAMES utf8mb4;\nINSERT INTO `t` VALUES (1,'a;b');\nDELIMITER $$\nCREATE TRIGGER `tr` BEFORE INSERT ON `t` FOR EACH ROW BEGIN SET @a=1; SET @b=2; END$$\nDELIMITER ;\n";
$count=enterprise_project_restore_execute_sql($fake,$sql);
$c('sql parser preserves semicolons in values and trigger bodies',$count===3&&count($fake->statements)===3&&str_contains($fake->statements[1],"'a;b'")&&str_contains($fake->statements[2],'SET @a=1; SET @b=2;'));
$rewritten=enterprise_project_restore_rewrite_database_sql('CREATE DATABASE `old_db`; USE `old_db`; SELECT * FROM `old_db`.`x`;','old_db','new_db');
$c('database rewrite changes quoted database references only',str_contains($rewritten,'`new_db`.`x`')&&!str_contains($rewritten,'`old_db`'));

$f=count(array_filter($checks,fn($x)=>$x['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF73','status'=>$f?'FAIL':'PASS','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>$f]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($f?1:0);
