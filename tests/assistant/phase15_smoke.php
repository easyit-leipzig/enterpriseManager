<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/system/assistant/bootstrap.php';

use EasyIT\Assistant\State\AssistantStateStore;
use EasyIT\Assistant\Template\DataFormTemplateStore;
use EasyIT\Assistant\Template\DataFormTemplateMapper;
use EasyIT\Assistant\Template\DataFormTemplateService;
use EasyIT\Assistant\Field\FieldTypeRegistry;
use EasyIT\Assistant\Action\DataFormActionRegistry;

function tassert(bool $ok, string $message): void { if (!$ok) { throw new RuntimeException('FAIL: ' . $message); } echo "PASS: $message\n"; }

$source = 'phase15-source-' . bin2hex(random_bytes(3));
$target = 'phase15-target-' . bin2hex(random_bytes(3));
foreach ([$source,$target] as $p) { @mkdir($root . '/projects/' . $p . '/config', 0770, true); }
@mkdir($root . '/storage/assistant/templates', 0770, true);
$store = new AssistantStateStore('easyit_assistant_phase15', $root);

$df = [
 'source'=>['profile'=>'project-main','driver'=>'csv','connection'=>'profile:project-main','name'=>'ed_ev'],
 'identity'=>['dataFormName'=>'ed_ev','primaryKey'=>'id'],
 'features'=>['fullTextSearch'=>true,'filter'=>true,'pagination'=>['enabled'=>true,'position'=>'below-records','pageSize'=>20,'windowLeft'=>2,'windowRight'=>2,'showFirst'=>true,'showLast'=>true]],
 'crud'=>['create'=>true,'show'=>true,'edit'=>true,'delete'=>true,'save'=>true],
 'fields'=>[['name'=>'id','label'=>'ID','type'=>'integer','required'=>true,'readOnly'=>true,'default'=>null],['name'=>'name','label'=>'Name','type'=>'text','required'=>true,'readOnly'=>false,'default'=>null]],
];
$fields = [
 'context'=>['dataForm'=>'ed_ev','sourceProfile'=>'project-main','sourceName'=>'ed_ev','primaryKey'=>'id'],
 'fields'=>$df['fields'],'lookups'=>[],'derivedEnums'=>[],
 'rules'=>['derivedEnumStorage'=>'comma-separated','derivedEnumDelimiter'=>',','trimValues'=>true,'deduplicateValues'=>true],
];
$relations = [
 'relation'=>['name'=>'ev-info','type'=>'one_to_many'],
 'parent'=>['dataForm'=>'ed_ev','source'=>'ed_ev','keyField'=>'id'],
 'child'=>['dataForm'=>'ed_ev_info','source'=>'ed_ev_info','foreignKeyField'=>'to_ev_id'],
 'binding'=>['enabled'=>true,'valueSource'=>'parent.currentRecord','parentValueField'=>'id','childTargetField'=>'to_ev_id','fillOnNewRecord'=>true,'readOnly'=>true],
 'manyToMany'=>['junctionSource'=>'','parentForeignKeyField'=>'','childForeignKeyField'=>'','childKeyField'=>'id'],
 'display'=>['paginationPosition'=>'below-records'],
];
$events = [
 'context'=>['objectName'=>'dataFormContext','schema'=>'easyit.dataform.action-context.v1','includeOriginalRecord'=>true,'includeChanges'=>true,'includeRelationContext'=>true,'includePagination'=>true],
 'events'=>['beforeSave'=>['enabled'=>false,'handler'=>'','blocking'=>true],'afterSave'=>['enabled'=>true,'handler'=>'app.ev.afterSave','blocking'=>false],'beforeDelete'=>['enabled'=>false,'handler'=>'','blocking'=>true],'afterDelete'=>['enabled'=>false,'handler'=>'','blocking'=>false]],
 'runtime'=>['handlerResolution'=>'global-path','allowEval'=>false,'beforeEventFalseCancelsAction'=>true,'captureHandlerErrors'=>true],
];
$actions = [
 'dataForm'=>['name'=>'ed_ev'],
 'actions'=>['new'=>true,'show'=>true,'edit'=>true,'save'=>true,'delete'=>true,'first'=>true,'previous'=>true,'next'=>true,'last'=>true],
 'ui'=>['buttonRegistry'=>'central','localTitlesAllowed'=>false,'localAriaLabelsAllowed'=>false,'cssBackgroundButtonsAllowed'=>false],
];
$store->putForProject('dataform.create',$source,$df,$source);
$store->putForProject('dataform.fields',$source,$fields,$source.'|ed_ev');
$store->putForProject('dataform.relations',$source,$relations,$source.'|ed_ev');
$store->putForProject('dataform.events',$source,$events,$source.'|ed_ev');
$store->putForProject('dataform.actions',$source,$actions,$source.'|ed_ev');

$templateStore = new DataFormTemplateStore($root);
$service = new DataFormTemplateService($store,$templateStore,new DataFormTemplateMapper(),new FieldTypeRegistry(),new DataFormActionRegistry(),$root);
$template = $service->capture('Phase 15 Vorlage','Smoke Test',$source,'ed_ev');
tassert(($template['schema'] ?? '') === 'easyit.assistant.dataform-template.v1','Vorlage mit kanonischem Schema gespeichert');
tassert(count((array)$template['bundle']) === 5,'fünf DataForm-Konfigurationsbereiche erfasst');

$mapping = ['project-main'=>'customer-main','ed_ev_info'=>'ed_customer_info'];
$preview = $service->preview((string)$template['id'],$target,'ed_customer',$mapping,false);
tassert(($preview['verdict'] ?? '') !== 'FAIL','Klon-Vorschau ist ohne Zielkonflikt gültig');
tassert(($preview['mappedBundle']['dataform.create']['identity']['dataFormName'] ?? '') === 'ed_customer','DataForm-Name gemappt');
tassert(($preview['mappedBundle']['dataform.create']['source']['profile'] ?? '') === 'customer-main','Datenquellenprofil gemappt');
tassert(($preview['mappedBundle']['dataform.relations']['parent']['dataForm'] ?? '') === 'ed_customer','Eltern-DataForm gemappt');
tassert(($preview['mappedBundle']['dataform.relations']['child']['dataForm'] ?? '') === 'ed_customer_info','zusätzliche Relationsreferenz gemappt');

$apply = $service->apply((string)$template['id'],$target,'ed_customer',$mapping,false);
tassert(!empty($apply['applied']),'Vorlage angewendet');
$targetDf = $store->getForProject('dataform.create',$target,$target);
tassert(($targetDf['identity']['dataFormName'] ?? '') === 'ed_customer','Zielzustand persistent geschrieben');
$targetActions = $store->getForProject('dataform.actions',$target,$target.'|ed_customer');
tassert(($targetActions['dataForm']['name'] ?? '') === 'ed_customer','Aktionskonfiguration auf Ziel-DataForm umgeschrieben');
$history = $store->history('dataform.create',$target,$target);
tassert(!empty($history['entries']),'Übernahme ist in Phase-14-Historie versioniert');

$conflict = $service->preview((string)$template['id'],$target,'ed_customer',$mapping,false);
tassert(($conflict['verdict'] ?? '') === 'FAIL','vorhandenes Ziel blockiert ohne Überschreibbestätigung');
$overwrite = $service->preview((string)$template['id'],$target,'ed_customer',$mapping,true);
tassert(($overwrite['verdict'] ?? '') === 'PASS_WITH_WARNINGS','versioniertes Überschreiben wird als Warnung zugelassen');

// Secret sanitizing in template store.
$manual = $templateStore->save(['id'=>'phase15-secret-' . bin2hex(random_bytes(2)),'name'=>'Secret','bundle'=>['x'=>['password'=>'NO','passwordRef'=>'DB_PASSWORD']]]);
tassert(!isset($manual['bundle']['x']['password']),'Klartext-Secret wird aus Vorlage entfernt');
tassert(($manual['bundle']['x']['passwordRef'] ?? '') === 'DB_PASSWORD','Secret-Referenz bleibt erlaubt');

// Cleanup.
function rrmdir15(string $dir): void { if (!is_dir($dir)) return; foreach (array_diff(scandir($dir) ?: [],['.','..']) as $x) { $p=$dir.'/'.$x; is_dir($p)?rrmdir15($p):@unlink($p); } @rmdir($dir); }
rrmdir15($root . '/projects/' . $source); rrmdir15($root . '/projects/' . $target);
@unlink($root . '/storage/assistant/templates/' . $template['id'] . '.json');
@unlink($root . '/storage/assistant/templates/' . $manual['id'] . '.json');
echo "PHASE15_SMOKE_PASS\n";
