<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$store=(string)file_get_contents($r.'/products/dataform/system/DataFormRecordStore.php');
$relations=(string)file_get_contents($r.'/products/dataform/relations.php');
$parentValue=(string)file_get_contents($r.'/products/dataform/parent-value.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('physical binding detection',
    str_contains($store,'dataform_table_bindings')
    && str_contains($store,"source_kind='system'")
);
$f('physical list reads bound table',
    str_contains($store,"'SELECT * FROM '.self::quoteIdentifier(\$table)")
);
$f('physical create writes bound table',
    str_contains($store,"'INSERT INTO '.self::quoteIdentifier(\$table)")
);
$f('physical update writes bound table',
    str_contains($store,"'UPDATE '.self::quoteIdentifier(\$table)")
);
$f('physical delete writes bound table',
    str_contains($store,"'DELETE FROM '.self::quoteIdentifier(\$table)")
);
$f('generic store remains supported',
    str_contains($store,'INSERT INTO dataform_records')
    && str_contains($store,'UPDATE dataform_records')
);
$f('field mapping uses table binding metadata',
    str_contains($store,"cfg['table_binding']")
    && str_contains($store,"['column']")
);
$f('datetime local is normalized for SQL',
    str_contains($store,"str_replace('T',' ',\$string)")
);
$f('records CRUD uses record store',
    str_contains($records,'DataFormRecordStore::create')
    && str_contains($records,'DataFormRecordStore::update')
    && str_contains($records,'DataFormRecordStore::delete')
    && str_contains($records,'DataFormRecordStore::all')
);
$f('parent options use physical/generic store',
    str_contains($records,'DataFormRecordStore::all')
    && str_contains($records,"source_dataform_id")
);
$f('master context validates child relation',
    str_contains($records,'$parentRelationContextId')
    && str_contains($records,'Die angeforderte Elternbeziehung gehört nicht zu diesem Kind-DataForm.')
);
$f('master context locks child FK',
    str_contains($records,'df-master-lock')
    && str_contains($records,"lookup_field_name")
    && str_contains($records,"parent_record_id")
);
$f('child collection uses parent source relation',
    str_contains($records,'WHERE r.source_dataform_id=?')
    && str_contains($records,"r.target_dataform_id")
);
$f('children are filtered by parent id',
    str_contains($records,"===(string)\$recordId")
);
$f('parent detail has add-child action',
    str_contains($records,'+ Kinddatensatz')
    && str_contains($records,'parent_relation=')
    && str_contains($records,'parent_record=')
);
$f('parent detail renders child records',
    str_contains($records,'df-child-collection')
    && str_contains($records,'Kinddatensatz')
);
$f('parent delete protects children',
    str_contains($records,'df_child_record_count')
    && str_contains($records,'Löschen Sie zuerst die Kinddatensätze.')
);
$f('parent value endpoint uses record store',
    str_contains($parentValue,'DataFormRecordStore::find')
);
$f('dataform list can create directly from managed table',
    str_contains($runtime,'DataForm aus Projekttabelle erzeugen')
    && str_contains($runtime,'$unboundManagedTables')
    && str_contains($runtime,'create_dataform_from_table')
);
$f('dataform list exposes record runtime',
    str_contains($runtime,'>Datensätze</a>')
);
$f('1:n remains explicit parent to child',
    str_contains($relations,'Eltern-DataForm')
    && str_contains($relations,'Fremdschlüsselfeld im Kind')
);
$f('master detail CSS exists',
    str_contains($css,'.df-child-collection')
    && str_contains($css,'.df-master-lock')
);
$f('HF34 marker',
    str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE')
    && str_contains($records,'DataForm Workspace · HF36')
);

$failed=count(array_filter(
    $c,
    static fn(array $row):bool=>$row['status']==='FAIL'
));

echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>[
        'checks'=>count($c),
        'failed'=>$failed,
    ],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;

exit($failed===0?0:1);
