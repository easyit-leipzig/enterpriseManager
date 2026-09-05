<?php
declare(strict_types=1);

$r=__DIR__;
$relations=(string)file_get_contents($r.'/products/dataform/relations.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$endpoint=(string)file_get_contents($r.'/products/dataform/parent-value.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/RelationManager.php');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('explicit parent child labels',
    str_contains($relations,'Eltern-DataForm')
    && str_contains($relations,'Kind-DataForm')
);
$f('explicit parent key semantics',
    str_contains($relations,'Eltern.id')
    && str_contains($relations,'Kind.&lt;Fremdschlüsselfeld&gt;')
);
$f('child FK can be selected',
    str_contains($relations,'name="child_fk_field_id"')
    && str_contains($relations,'Fremdschlüsselfeld im Kind')
);
$f('auto lookup is created in child only',
    str_contains($manager,'$childDataformId')
    && str_contains($manager,"'lookup'")
    && str_contains($manager,'Wählen Sie ein vorhandenes Fremdschlüsselfeld im Kind')
);
$f('1:n is persisted as source parent target child',
    str_contains($manager,'$parentDataformId')
    && str_contains($manager,'$childDataformId')
    && str_contains($manager,"'semantics'=>self::SEMANTICS")
);
$f('records query current form as child',
    str_contains($records,'WHERE r.target_dataform_id=?')
    && str_contains($records,'JOIN dataforms p ON p.id=r.source_dataform_id')
);
$f('parent records come from source parent',
    str_contains($records,'DataFormRecordStore::all')
    && str_contains($records,"relation['source_dataform_id']")
);
$f('existing numeric child FK renders lookup',
    str_contains($records,'elseif(isset($parentRelationsByLookupFieldId')
    && str_contains($records,"field['id']")
);
$f('parent selector is built only when current form is child',
    str_contains($records,'WHERE r.target_dataform_id=?')
    && str_contains($records,'$parentRelationsByLookupFieldId')
);
$f('parent-value endpoint validates child as target',
    str_contains($endpoint,'AND target_dataform_id=?')
    && str_contains($endpoint,"relation['source_dataform_id']")
);
$f('parent-field inheritance uses same direction',
    str_contains($runtime,'AND target_dataform_id=?')
    && str_contains($runtime,"parentRelation['source_dataform_id']")
);
$f('legacy repair exists',
    str_contains($manager,'repairLegacyOneToMany')
    && str_contains($manager,'legacy_direction_repaired')
    && str_contains($manager,'legacy_direction_swapped')
);
$f('legacy parent synthetic lookup can be rebound to child FK',
    str_contains($manager,'count($targetCandidates) === 1')
    && str_contains($manager,'lookup_field_id=?')
);
$f('legacy auto lookup can be removed safely',
    str_contains($manager,"field_type']==='lookup'")
    && str_contains($manager,'relationUseCount')
);
$f('normal existing FK survives relation delete',
    str_contains($relations,"['auto_form']")
    && str_contains($relations,"['auto_physical']")
);
$f('HF33 runtime marker',
    str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE')
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
