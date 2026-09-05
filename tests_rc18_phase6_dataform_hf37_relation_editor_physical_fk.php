<?php
declare(strict_types=1);

$r=__DIR__;
$page=(string)file_get_contents($r.'/products/dataform/relations.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/RelationManager.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('relations can be edited',
    str_contains($page,"action==='update_relation'")
    && str_contains($page,'Beziehung bearbeiten')
    && str_contains($page,'>Bearbeiten</a>')
    && str_contains($page,'data-crud="edit"')
);
$f('edit reuses existing relation instead of insert',
    str_contains($manager,'public static function updateOneToMany')
    && str_contains($manager,'UPDATE dataform_relations')
    && str_contains($manager,'WHERE id=?')
);
$f('child FK dropdown uses only usable fields',
    str_contains($page,'RelationManager::selectableFields($pdo)')
    && str_contains($manager,'public static function selectableFields')
    && str_contains($manager,"if (!\$state['usable'])")
);
$f('physical child column is verified',
    str_contains($manager,'SHOW FULL COLUMNS FROM')
    && str_contains($manager,'existiert nicht in der Kindtabelle')
    && str_contains($manager,'dataform_table_bindings')
);
$f('metadata-only phantom FK is rejected',
    str_contains($manager,'relationHealth')
    && str_contains($manager,'Das Fremdschlüsselfeld besitzt keinen gültigen Datenspeicher')
    && str_contains($page,'Die bisherige Zuordnung ist ungültig')
);
$f('existing v2 relation is revalidated',
    str_contains($manager,'even relations already carrying the v2 semantics')
    && str_contains($manager,'physical_fk_repaired')
    && str_contains($manager,'invalid_lookup_field_id')
);
$f('unique real child id field can repair phantom relation',
    str_contains($manager,'$candidates=self::fkCandidates')
    && str_contains($manager,'count($candidates)===1')
    && str_contains($manager,'SET lookup_field_id=?,configuration_json=?')
);
$f('ambiguous invalid relation is disabled safely',
    str_contains($manager,'invalid_auto_disabled_hf37')
    && str_contains($manager,'SET is_enabled=0,configuration_json=?')
    && str_contains($manager,'wurde vorsorglich deaktiviert')
);
$f('repair edit restores auto-disabled relation',
    str_contains($manager,'$restoreAfterRepair')
    && str_contains($manager,'$nextEnabled=$restoreAfterRepair?1:')
);
$f('implicit auto-create wording is removed',
    !str_contains($page,'Bitte wählen oder automatisch erzeugen')
    && !str_contains($page,'Falls kein Feld gewählt ist: Lookup-Feld')
    && str_contains($page,'Bitte vorhandenes Feld wählen')
);
$f('new FK creation is explicit',
    str_contains($page,'Neues Fremdschlüsselfeld im Kind anlegen')
    && str_contains($page,'name="new_child_fk_name"')
    && str_contains($page,'nur verwenden, wenn kein bestehendes Feld geeignet ist')
);
$f('physical FK creation really alters bound child table',
    str_contains($manager,"ALTER TABLE ")
    && str_contains($manager,'BIGINT UNSIGNED NULL')
    && str_contains($manager,"['managed_relation_fk']=true")
    && str_contains($manager,"['table_binding']=[")
);
$f('normal existing FK is not deleted with relation',
    str_contains($page,"['auto_form']")
    && str_contains($page,"['auto_physical']")
);
$f('relation status exposes invalid mapping',
    str_contains($page,'df-relation-invalid')
    && str_contains($page,'fehlerhaft')
    && str_contains($css,'.df-relation-invalid')
);
$f('live semantics show selected real mapping',
    str_contains($page,'relation-parent-key')
    && str_contains($page,'relation-child-key')
    && str_contains($page,'data-field-name')
    && str_contains($page,'syncSemantics')
);
$f('relations workspace hotfix marker',preg_match('/DataForm Workspace · HF(?:3[7-9]|[4-9][0-9])/', $page)===1);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF37',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
