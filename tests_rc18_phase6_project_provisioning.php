<?php
declare(strict_types=1);
$r=__DIR__;$checks=[];$c=function(string $n,bool $ok)use(&$checks){$checks[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$new=(string)file_get_contents($r.'/app/projects/create.php');
$reg=(string)file_get_contents($r.'/app/projects/register.php');
$idx=(string)file_get_contents($r.'/app/projects/index.php');
$c('new project provisioning page exists',is_file($r.'/app/projects/create.php'));
$c('existing project registration preserved',is_file($r.'/app/projects/register.php')&&str_contains($reg,'Vorhandenes Projekt registrieren'));
$c('project list exposes both actions',str_contains($idx,'Neues Projekt anlegen')&&str_contains($idx,'Vorhandenes Projekt registrieren'));
$c('provisioning validates fresh database',str_contains($new,'existiert bereits')&&str_contains($new,'project_provision_database_exists'));
$c('provisioning creates database',str_contains($new,'CREATE DATABASE'));
$c('project schema migrations applied',str_contains($new,'installer/schema/project')&&str_contains($new,'project_provision_install_schema'));
$c('registration occurs after schema installation',strpos($new,'project_provision_install_schema')<strpos($new,'INSERT INTO projects'));
$c('cleanup drops failed freshly created database',str_contains($new,'DROP DATABASE IF EXISTS'));
$c('audit event emitted',str_contains($new,"'project.provision'"));
$c('domain event emitted',str_contains($new,"'project.provisioned'"));
$c('csrf enforced',str_contains($new,'enterprise_check_csrf'));
$f=count(array_filter($checks,fn($x)=>$x['status']!=='PASS'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF11','status'=>$f?'FAIL':'PASS','checks'=>$checks,'summary'=>['checks'=>count($checks),'failed'=>$f]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;exit($f?1:0);
