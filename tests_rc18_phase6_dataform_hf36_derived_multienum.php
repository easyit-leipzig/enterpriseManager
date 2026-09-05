<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/DerivedMultiEnumManager.php');
$store=(string)file_get_contents($r.'/products/dataform/system/DataFormRecordStore.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

require_once $r.'/products/dataform/system/DerivedMultiEnumManager.php';

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('new logical field type exists',
    str_contains($runtime,"'derived_multienum'=>'Abgeleitete Mehrfachauswahl'")
    && str_contains($runtime,'<option value="derived_multienum">Abgeleitete Mehrfachauswahl</option>')
);
$f('create and update whitelist derived field type',
    substr_count($runtime,"'derived_multienum'")>=5
);
$f('designer has source table setting',
    str_contains($runtime,'name="derived_source_table"')
    && str_contains($runtime,'Quelltabelle')
);
$f('designer has value and label columns',
    str_contains($runtime,'name="derived_value_column"')
    && str_contains($runtime,'name="derived_label_column"')
);
$f('designer has dependency modes',
    str_contains($runtime,'current_record_id')
    && str_contains($runtime,'parent_record_id')
    && str_contains($runtime,'derived_filter_source_column')
);
$f('designer has min max selection',
    str_contains($runtime,'derived_min_selected')
    && str_contains($runtime,'derived_max_selected')
);
$f('dynamic UI filters source columns',
    str_contains($runtime,'hf36-derived-multienum-ui')
    && str_contains($runtime,'data-derived-table')
    && str_contains($runtime,'filterColumns')
);
$f('record form uses real multiple select',
    str_contains($records,'df-derived-multiselect')
    && str_contains($records,'name="values[<?= e($name) ?>][]"')
    && str_contains($records,' multiple size="8"')
);
$f('CSV separator is fixed to comma',
    DerivedMultiEnumManager::SEPARATOR===','
    && str_contains($manager,"public const SEPARATOR = ','")
);
$f('selection canonicalizes duplicates and blanks',
    DerivedMultiEnumManager::canonicalizeSelection(['3',' 7 ','3','','12'])===['3','7','12']
);
$f('selection stores stable source values as CSV',
    DerivedMultiEnumManager::csv(['3','7','12'])==='3,7,12'
);
$f('configuration stores value column not labels',
    str_contains($manager,"'value_column'")
    && str_contains($manager,"'label_column'")
);
$f('physical bound DataForm gets text storage column',
    str_contains($manager,"TEXT NULL COMMENT 'easyIT derived_multienum'")
    && str_contains($manager,"'managed_storage'")
);
$f('existing physical storage must be text compatible',
    str_contains($manager,'CHAR/VARCHAR/TEXT-Feld')
);
$f('generic and physical record store remain compatible',
    str_contains($store,'dataform_table_bindings')
    && str_contains($store,'dataform_records')
);
$f('server validates selected source values',
    str_contains($records,'DerivedMultiEnumManager::validateSelection')
    && str_contains($manager,'nicht zulässigen abgeleiteten Wert')
);
$f('source values must be unique',
    str_contains($manager,'enthält den Wert')
    && str_contains($manager,'mehrfach. Eine abgeleitete Mehrfachauswahl benötigt eindeutige Quellwerte')
);
$f('list detail and export resolve labels',
    str_contains($records,'df_display_record_value')
    && substr_count($records,'df_display_record_value(')>=4
    && str_contains($manager,'labelsForCsv')
);
$f('missing source values are visible',
    str_contains($manager,'nicht mehr vorhanden')
    && str_contains($records,'nicht mehr verfügbar')
);
$f('current record dependency supported',
    str_contains($manager,"\$filterMode === 'current_record_id'")
    && str_contains($manager,"\$context['record_id']")
);
$f('parent dependency supported',
    str_contains($manager,"\$filterMode === 'parent_record_id'")
    && str_contains($records,'df_effective_parent_record_id')
);
$f('source record deletion is protected',
    str_contains($records,'referencesToSourceRecord')
    && str_contains($records,'abgeleiteten Mehrfachauswahl-Referenz')
);
$f('source table deletion is protected',
    str_contains($runtime,'assertSourceTableNotReferenced')
    && str_contains($manager,'wird noch als Quelle der abgeleiteten Mehrfachauswahl')
);
$f('source column deletion is protected',
    str_contains($runtime,'assertSourceColumnNotReferenced')
    && str_contains($manager,'assertSourceColumnNotReferenced')
);
$f('source column protection marker',
    str_contains($manager,'Ändern Sie zuerst die Felddefinition.')
    && str_contains($manager,"'Wertspalte'")
    && str_contains($manager,"'Anzeigespalte'")
    && str_contains($manager,"'Filterspalte'")
);
$f('derived field preview exists',
    str_contains($runtime,"field['field_type'] === 'derived_multienum'")
    && str_contains($runtime,'Abgeleitete Werte aus')
);
$f('derived CSS exists',
    str_contains($css,'.df-derived-config')
    && str_contains($css,'.df-derived-multiselect')
    && str_contains($css,'.df-derived-chip')
);
$f('HF36 runtime marker',
    str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE')
    && str_contains($runtime,'X-EasyIT-DataForm-Runtime: HF36')
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF36',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
