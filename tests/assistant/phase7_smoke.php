<?php
declare(strict_types=1);

session_start();
$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';

function ctx7(string $project, ?string $dataForm, array $input = [], string $method = 'POST'): \EasyIT\Assistant\AssistantContext
{
    return new \EasyIT\Assistant\AssistantContext($project, $dataForm, null, null, $input, ['requestMethod' => $method]);
}
function ok7(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    echo 'PASS: ' . $message . PHP_EOL;
}

$project = 'phase7-test';
$dataForm = 'ed_ev';

// Prepare a DataForm draft and verify the handoff into the field assistant.
$manager->run('dataform.create', ctx7($project, null, ['driver' => 'csv', 'connection' => 'profile:main', 'source_name' => 'ed_ev']), 'source');
$manager->run('dataform.create', ctx7($project, null, ['dataform_name' => $dataForm, 'primary_key' => 'id']), 'identity');
$manager->run('dataform.create', ctx7($project, null, ['full_text_search' => '1', 'filter_enabled' => '1', 'page_size' => '20']), 'features');
$manager->run('dataform.create', ctx7($project, null, ['crud_create' => '1', 'crud_show' => '1', 'crud_edit' => '1', 'crud_delete' => '1', 'crud_save' => '1']), 'crud');
$manager->run('dataform.create', ctx7($project, null, ['field_lines' => "id|ID|integer|1|1\nname|Name|text|1|0\nto_type_id|Typ|lookup|0|0\ntags|Merkmale|derived_enum|0|0"]), 'fields');
$r = $manager->run('dataform.create', ctx7($project, null, [], 'GET'), 'review');
ok7($r->isOk(), 'DataForm-Vorentwurf ist gültig.');
ok7(str_contains((string) ($r->getData()['handoffUrl'] ?? ''), 'dataform.fields'), 'DataForm erzeugt Handoff zum Feld-Assistenten.');

$r = $manager->run('dataform.fields', ctx7($project, $dataForm, ['import_dataform' => '1'], 'GET'), 'context');
ok7($r->isOk(), 'Feld-Assistent importiert DataForm-Kontext.');
$draft = $r->getData()['draft'];
$da = $draft instanceof JsonSerializable ? $draft->jsonSerialize() : [];
ok7(($da['context']['dataForm'] ?? '') === $dataForm, 'DataForm-Name wird übernommen.');
ok7(count($da['fields'] ?? []) === 4, 'Vorhandene DataForm-Felder werden übernommen.');

$r = $manager->run('dataform.fields', ctx7($project, $dataForm, [
    'field_lines' => "id|ID|integer|1|1|\nname|Name|text|1|0|\nto_type_id|Typ|lookup|0|0|\ntags|Merkmale|derived_enum|0|0|",
]), 'fields');
ok7($r->isOk(), 'Basisfelder einschließlich lookup und derived_enum sind gültig.');

$r = $manager->run('dataform.fields', ctx7($project, $dataForm, [
    'lookup_lines' => 'to_type_id|project-main|ed_ev_type|id|name||',
]), 'lookups');
ok7($r->isOk(), 'Lookup-Definition wird akzeptiert.');

$r = $manager->run('dataform.fields', ctx7($project, $dataForm, [
    'derived_enum_lines' => 'tags|project-main|ed_ev_person|id|name|to_ev_id|record.id',
]), 'derived_enum');
ok7($r->isOk(), 'Derived-Enum-Definition wird akzeptiert.');

$r = $manager->run('dataform.fields', ctx7($project, $dataForm, [], 'GET'), 'review');
ok7($r->isOk(), 'Feldkonfiguration besteht Gesamtvalidierung.');
ok7(($r->getData()['configurationReady'] ?? false) === true, 'Feldkonfiguration ist exportbereit.');
$config = $r->getData()['compiledConfig'] ?? [];
$byName = [];
foreach ($config['fields'] ?? [] as $field) { $byName[$field['name']] = $field; }
ok7(($byName['to_type_id']['lookup']['source'] ?? '') === 'ed_ev_type', 'Lookup referenziert die Quelltabelle.');
ok7(($byName['to_type_id']['lookup']['multiple'] ?? true) === false, 'Lookup ist ein Einzelwert.');
ok7(($byName['tags']['derivedEnum']['source'] ?? '') === 'ed_ev_person', 'Derived Enum referenziert die Quelltabelle.');
ok7(($byName['tags']['derivedEnum']['multiple'] ?? false) === true, 'Derived Enum ist Mehrfachauswahl.');
ok7(($byName['tags']['derivedEnum']['storage']['delimiter'] ?? '') === ',', 'Derived Enum speichert verbindlich kommasepariert.');
ok7(($byName['tags']['derivedEnum']['filterField'] ?? '') === 'to_ev_id', 'Derived Enum kann abhängig vom aktuellen Datensatz gefiltert werden.');
ok7(($byName['tags']['derivedEnum']['filterValueSource'] ?? '') === 'record.id', 'Filterwert stammt aus record.id.');
ok7(str_contains((string) ($r->getData()['handoffUrl'] ?? ''), 'import_fields=1'), 'Feld-Assistent erzeugt Rück-Handoff zum DataForm-Assistenten.');

$codec = new \EasyIT\Assistant\Field\DerivedEnumValueCodec();
ok7($codec->normalize('3, 7,3,9') === '3,7,9', 'Derived-Enum-Codecs trimmen und deduplizieren.');
ok7($codec->encode(['3', '7', '3']) === '3,7', 'Derived-Enum-Codecs erzeugen kommaseparierte Werte.');
$options = $codec->optionsFromRows([
    ['id' => 3, 'name' => 'A'], ['id' => 7, 'name' => 'B'], ['id' => 9, 'name' => 'C'],
], 'id', 'name', '7,9');
ok7($options[1]['selected'] === true && $options[2]['selected'] === true, 'Optionen markieren gespeicherte Mehrfachwerte korrekt.');
$thrown = false;
try { $codec->encode(['bad,key']); } catch (InvalidArgumentException) { $thrown = true; }
ok7($thrown, 'Ein einzelner Schlüssel mit Komma wird abgewiesen.');

$lookup = new \EasyIT\Assistant\Field\LookupResolver();
ok7($lookup->resolveLabel([['id' => '5', 'name' => 'Typ 5']], 'id', 'name', 5) === 'Typ 5', 'LookupResolver löst den Anzeigetext auf.');

// Import enriched field configuration back into DataForm.
$r = $manager->run('dataform.create', ctx7($project, null, ['import_fields' => '1'], 'GET'), 'review');
ok7($r->isOk(), 'Angereicherte Felder werden in den DataForm-Assistenten zurückimportiert.');
$dfConfig = $r->getData()['compiledConfig'] ?? [];
$dfFields = [];
foreach ($dfConfig['fields'] ?? [] as $field) { $dfFields[$field['name']] = $field; }
ok7(($dfFields['tags']['derivedEnum']['storage']['delimiter'] ?? '') === ',', 'Endgültige DataForm-Konfiguration enthält Derived-Enum-Metadaten.');
ok7(($dfFields['to_type_id']['lookup']['source'] ?? '') === 'ed_ev_type', 'Endgültige DataForm-Konfiguration enthält Lookup-Metadaten.');

// Negative validation: derived_enum field without definition must fail at review.
$bad = 'phase7-bad';
$manager->run('dataform.fields', ctx7($bad, 'badform', ['dataform_name' => 'badform', 'source_name' => 'x', 'primary_key' => 'id']), 'context');
$manager->run('dataform.fields', ctx7($bad, 'badform', ['field_lines' => "id|ID|integer|1|1|\ntags|Tags|derived_enum|0|0|"]), 'fields');
$r = $manager->run('dataform.fields', ctx7($bad, 'badform', [], 'GET'), 'review');
ok7(!$r->isOk(), 'Derived-Enum-Feld ohne Quellenbeschreibung wird abgelehnt.');

echo "PHASE7_SMOKE=PASS\n";
