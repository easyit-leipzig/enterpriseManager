<?php
declare(strict_types=1);

$source=(string)file_get_contents(__DIR__.'/products/dataform/records.php');
$start=strpos($source,'function df_record_inline_create_control(');
$end=strpos($source,"\nfunction df_server_default_value",$start);
if($start===false||$end===false){fwrite(STDERR,"FAIL function extraction\n");exit(1);}
$functionCode=substr($source,$start,$end-$start);

if(!class_exists('DataFormFieldTypeRegistry')){
    final class DataFormFieldTypeRegistry {
        public static function isBoolean(string $type): bool { return false; }
    }
}
if(!function_exists('df_server_default_value')){
    function df_server_default_value(array $field,array $cfg): string { return ''; }
}

eval($functionCode);
$pdo=(new ReflectionClass(PDO::class))->newInstanceWithoutConstructor();
$field=[
    'id'=>77,
    'name'=>'to_ev_id',
    'label'=>'To Ev Id',
    'field_type'=>'integer',
    'is_required'=>1,
    '_config'=>[],
];
$relations=[77=>['relation_type'=>'1:n']];
$options=[77=>[
    ['id'=>9199,'caption'=>'#9199'],
    ['id'=>9201,'caption'=>'irgendeine Beschriftung'],
    ['id'=>9202,'caption'=>'#9202'],
]];
$master=[
    'lookup_field_id'=>77,
    'parent_record_id'=>9201,
    'bound_field_readonly'=>true,
];
$html=df_record_inline_create_control(
    $pdo,$field,$relations,$options,[],'new-form',[],
    'df-inline-create-control',$master
);
$editableMaster=$master;
$editableMaster['bound_field_readonly']=false;
$editableInitial=df_record_inline_create_control(
    $pdo,$field,$relations,$options,[],'new-form',[],
    'df-inline-create-control',$editableMaster
);
$editableChanged=df_record_inline_create_control(
    $pdo,$field,$relations,$options,[],'new-form',['to_ev_id'=>'9202'],
    'df-inline-create-control',$editableMaster
);

$checks=[
    'parent id selected'=>str_contains($html,'value="9201" selected'),
    'visible id'=>str_contains($html,'>#9201</option>'),
    'no parent placeholder when readonly'=>!str_contains($html,'Eltern-Datensatz wählen'),
    'readonly select'=>str_contains($html,' disabled'),
    'hidden submitted parent'=>str_contains($html,'type="hidden"')&&str_contains($html,'value="9201"'),
    'master attribute'=>str_contains($html,'data-bound-parent-id="9201"'),
    'editable default still selects parent'=>str_contains($editableInitial,'value="9201" selected')&&!str_contains($editableInitial,' disabled'),
    'editable field has no hidden mirror'=>!str_contains($editableInitial,'type="hidden"'),
    'editable changed parent survives rerender'=>str_contains($editableChanged,'value="9202" selected'),
];
$failed=0;
foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if(!$ok)$failed++;}
echo count($checks).'/'.count($checks).' checks, '.$failed.' failures'.PHP_EOL;
exit($failed?1:0);
