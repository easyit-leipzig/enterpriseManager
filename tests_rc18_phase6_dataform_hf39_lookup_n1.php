<?php
declare(strict_types=1);

$r=__DIR__;
$page=(string)file_get_contents($r.'/products/dataform/relations.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/RelationManager.php');
$records=(string)file_get_contents($r.'/products/dataform/records.php');
$workspace=(string)file_get_contents($r.'/products/dataform/system/WorkspaceController.php');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('HF39 workspace marker',preg_match('/DataForm Workspace · HF(?:39|4[0-9])/', $page)===1);
$f('n:1 type is selectable',
    str_contains($page,'value="n:1"')
    && str_contains($page,'n:1 / Lookup – ein Datensatz wählt einen Referenzdatensatz')
);
$f('n:1 source and lookup controls exist',
    str_contains($page,'name="lookup_source_field_id"')
    && str_contains($page,'name="lookup_display_field_id"')
    && str_contains($page,'data-many-to-one')
);
$f('relation page persists n:1 create',
    str_contains($page,'RelationManager::createManyToOne(')
    && str_contains($page,"\$type==='n:1'")
);
$f('relation page persists n:1 update',
    str_contains($page,'RelationManager::updateManyToOne(')
    && str_contains($page,'Die n:1-/Lookup-Beziehung wurde aktualisiert.')
);
$f('manager stores source FK field explicitly',
    str_contains($manager,"public const LOOKUP_SEMANTICS = 'lookup-n-to-one-v1'")
    && str_contains($manager,'source_field_id')
    && str_contains($manager,"'n:1'")
);
$f('manager validates source field against source DataForm',
    str_contains($manager,'validateManyToOneEndpoints')
    && str_contains($manager,'Das Zuordnungsfeld muss als reales Feld zum Ausgangs-DataForm gehören.')
);
$f('n:1 health validation exists',
    str_contains($manager,"if (\$type==='n:1')")
    && str_contains($manager,'Kein Zuordnungsfeld im Ausgangs-DataForm zugeordnet.')
);
$f('runtime loads n:1 lookup options',
    str_contains($records,"r.relation_type='n:1'")
    && str_contains($records,'r.source_field_id AS lookup_field_id')
    && str_contains($records,'r.target_dataform_id AS source_dataform_id')
);
$f('runtime renders n:1 field as lookup select',
    str_contains($records,"'Lookup-Datensatz wählen'")
    && str_contains($records,'$parentRecordOptionsByLookupFieldId')
);
$f('runtime resolves lookup caption in lists and details',
    str_contains($records,"relation_type='n:1' AND source_dataform_id=? AND source_field_id=?")
    && str_contains($records,'$lookupCache[$cacheKey]')
);
$f('runtime validates selected lookup record',
    str_contains($records,'Die Auswahl im Feld „')
    && str_contains($records,'DataFormRecordStore::find(')
);
$f('lookup target deletion counts n:1 references',
    str_contains($records,"WHERE target_dataform_id=?")
    && str_contains($records,"relation_type='n:1'")
    && str_contains($records,'source_field_id IS NOT NULL')
);
$f('relation-required makes lookup field required at runtime',
    str_contains($records,"(int)(\$lookupRelation['is_required']??0)===1")
    && str_contains($records,"\$relationField['is_required']=1")
);
$f('workspace help lists n:1 lookup',
    str_contains($workspace,'1:n-, n:1/Lookup- und n:m-Beziehungen')
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF39',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
