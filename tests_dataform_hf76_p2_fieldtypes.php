<?php
declare(strict_types=1);
require __DIR__.'/products/dataform/system/DataFormFieldTypes.php';

$passes=[];
$failures=[];
function t(string $name, callable $fn): void {
    global $passes,$failures;
    try { $fn(); $passes[]=$name; echo "[PASS] $name\n"; }
    catch (Throwable $e) { $failures[]=$name.': '.$e->getMessage(); echo "[FAIL] $name: {$e->getMessage()}\n"; }
}
function eq(mixed $a,mixed $b,string $m=''): void { if($a!==$b) throw new RuntimeException($m!==''?$m:var_export($a,true).' !== '.var_export($b,true)); }
function ok(bool $v,string $m='Assertion failed'): void { if(!$v) throw new RuntimeException($m); }
function norm(string $type,mixed $value,array $cfg=[],$existing=''): string { return DataFormFieldTypeRegistry::normalizeValue($type,$value,$cfg,'Test',$existing); }

t('registry-has-33-types', fn()=>eq(count(DataFormFieldTypeRegistry::all()),33));
t('legacy-types-present', function(){ foreach(['text','textarea','number','date','datetime','email','url','checkbox','select','derived_multienum'] as $v) ok(DataFormFieldTypeRegistry::has($v),'missing '.$v); });
t('integer-valid', fn()=>eq(norm('integer','42'),'42'));
t('integer-invalid', function(){ try{norm('integer','4.2');}catch(RuntimeException){return;} throw new RuntimeException('invalid integer accepted'); });
t('decimal-comma-normalized', fn()=>eq(norm('decimal','12,5',['type_settings'=>['precision'=>10,'scale'=>2]]),'12.50'));
t('currency-scale', fn()=>eq(norm('currency','12.345',['type_settings'=>['precision'=>10,'scale'=>2,'currency'=>'EUR']]),'12.34'));
t('percentage-range', function(){ try{norm('percentage','101');}catch(RuntimeException){return;} throw new RuntimeException('101% accepted'); });
t('boolean-ja', fn()=>eq(norm('boolean','ja'),'1'));
t('boolean-empty', fn()=>eq(norm('checkbox',''),'0'));
t('date-valid', fn()=>eq(norm('date','2026-08-26'),'2026-08-26'));
t('date-invalid', function(){ try{norm('date','2026-02-30');}catch(RuntimeException){return;} throw new RuntimeException('invalid date accepted'); });
t('time-normalized', fn()=>eq(norm('time','14:05'),'14:05:00'));
t('datetime-normalized', fn()=>eq(norm('datetime','2026-08-26T14:05'),'2026-08-26 14:05:00'));
t('email-valid', fn()=>eq(norm('email','test@example.org'),'test@example.org'));
t('url-valid', fn()=>eq(norm('url','https://example.org/a?b=1'),'https://example.org/a?b=1'));
t('link-object', function(){ $d=json_decode(norm('link',['url'=>'https://example.org','label'=>'Details','target'=>'_blank']),true,512,JSON_THROW_ON_ERROR); eq($d['label'],'Details'); eq($d['target'],'_blank'); });
t('multiselect-json', function(){ $v=norm('multiselect',['a','b','a'],['options'=>['a','b','c']]); eq(json_decode($v,true),['a','b']); });
t('tags-normalize', function(){ $v=norm('tags','rot, blau; rot'); eq(json_decode($v,true),['rot','blau']); });
t('json-pretty-valid', function(){ $v=norm('json','{"a":1,"b":[2]}'); $d=json_decode($v,true,512,JSON_THROW_ON_ERROR); eq($d['b'][0],2); });
t('json-invalid', function(){ try{norm('json','{"a":}');}catch(RuntimeException){return;} throw new RuntimeException('invalid json accepted'); });
t('uuid-valid', fn()=>eq(norm('uuid','123e4567-e89b-12d3-a456-426614174000'),'123e4567-e89b-12d3-a456-426614174000'));
t('coordinates', function(){ $v=norm('coordinates',['lat'=>'59.33','lng'=>'18.06']); $d=json_decode($v,true,512,JSON_THROW_ON_ERROR); eq($d['lat'],59.33); eq($d['lng'],18.06); });
t('coordinates-range', function(){ try{norm('coordinates',['lat'=>'91','lng'=>'18']);}catch(RuntimeException){return;} throw new RuntimeException('invalid coordinates accepted'); });
t('color-valid', fn()=>eq(norm('color','#A1B2C3'),'#A1B2C3'));
t('password-hashed', function(){ $h=norm('password','Secret-123'); ok(password_verify('Secret-123',$h)); ok($h!=='Secret-123'); });
t('password-empty-preserves', fn()=>eq(norm('password','',[],'existing-hash'),'existing-hash'));
t('richtext-sanitized', function(){ $v=norm('richtext','<p onclick="x">Hallo<script>alert(1)</script><strong style="x">Welt</strong></p>'); ok(!str_contains($v,'script')); ok(!str_contains($v,'onclick')); ok(!str_contains($v,'style=')); ok(str_contains($v,'<strong>Welt</strong>')); });
t('richtext-display-resanitized', function(){ $v=DataFormFieldTypeRegistry::sanitizeRichTextForDisplay('<p onmouseover="x">Alt <b class="x">Text</b></p>'); eq($v,'<p>Alt <b>Text</b></p>'); });
t('computed-template', fn()=>eq(DataFormFieldTypeRegistry::computeValue(['type_settings'=>['template'=>'{{first}} {{last}}']],['first'=>'Ada','last'=>'Lovelace']),'Ada Lovelace'));
t('display-password-masked', fn()=>eq(DataFormFieldTypeRegistry::displayText('password','$2y$dummy'),'••••••••'));
t('sql-map-json-mysql', fn()=>eq(DataFormFieldTypeRegistry::sqlType('json','mysql'),'JSON'));
t('sql-map-time-mysql', fn()=>eq(DataFormFieldTypeRegistry::sqlType('time','mysql'),'TIME'));
t('sql-map-decimal-custom', fn()=>eq(DataFormFieldTypeRegistry::sqlType('decimal','mysql',['type_settings'=>['precision'=>12,'scale'=>3]]),'DECIMAL(12,3)'));

echo "\nPASS=".count($passes)." FAIL=".count($failures)."\n";
exit($failures?1:0);
