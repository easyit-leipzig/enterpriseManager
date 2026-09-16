<?php
declare(strict_types=1);
$root=dirname(__DIR__);$checks=[];$c=function(string $name,bool $ok)use(&$checks):void{$checks[$name]=$ok;};
$c('server app',is_file($root.'/easyit-license-server/src/App/Application.php'));
$c('server compiler protected',is_file($root.'/easyit-license-server/src/ProtectedCore/DataFormCompiler.php'));
$c('server action resolver protected',is_file($root.'/easyit-license-server/src/ProtectedCore/ActionResolver.php'));
$c('designer validator protected',is_file($root.'/easyit-license-server/src/ProtectedCore/DefinitionValidator.php'));
$c('designer service protected',is_file($root.'/easyit-license-server/src/Service/DesignerService.php'));
$c('client api',is_file($root.'/enterprise-integration/system/licensing/LicenseApiClient.php'));
$c('client runtime',is_file($root.'/enterprise-integration/system/licensing/DataFormRuntimeClient.php'));
$c('client designer',is_file($root.'/enterprise-integration/system/licensing/DataFormDesignerClient.php'));
$c('client bridge',is_file($root.'/enterprise-integration/system/licensing/EnterpriseRuntimeBridge.php'));
$c('offline runtime cache',str_contains((string)file_get_contents($root.'/enterprise-integration/system/licensing/DataFormRuntimeClient.php'),'loadOfflineCache'));
$c('production admin cli',is_file($root.'/easyit-license-server/bin/admin.php'));
$c('definition publish cli',is_file($root.'/enterprise-integration/tools/publish_dataform_definition.php'));
$c('patch installer',is_file($root.'/enterprise-integration/tools/apply_to_enterpriseManager.php'));
$c('server private key not shipped',!is_file($root.'/easyit-license-server/secure/keys/server-private.key'));
$c('config is example only',!is_file($root.'/easyit-license-server/config/app.php'));
$fail=0;foreach($checks as$name=>$ok){echo str_pad($name,42).($ok?'PASS':'FAIL').PHP_EOL;if(!$ok)$fail++;}echo 'FINAL STATUS: '.($fail?'FAILED':'PASSED').PHP_EOL;exit($fail?1:0);
