<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/products/dataform/system/ProjectPackageManager.php';
$page=file_get_contents($root.'/products/dataform/packages.php');

$config=[
    'dataforms'=>[
        ['id'=>1,'name'=>'Ed Ev','slug'=>'ed-ev'],
        ['id'=>2,'name'=>'Ed Ev Info','slug'=>'ed-ev-info'],
    ],
    'dataform_fields'=>[
        ['id'=>10,'dataform_id'=>2,'name'=>'to_ev_id','label'=>'To Ev Id'],
        ['id'=>11,'dataform_id'=>2,'name'=>'typ','label'=>'Typ'],
        ['id'=>12,'dataform_id'=>2,'name'=>'from_person','label'=>'From Person'],
    ],
    'dataform_relations'=>[
        [
            'name'=>'ed_ev_id_to_ed_ev_info_id','relation_type'=>'1:n',
            'source_dataform_id'=>1,'target_dataform_id'=>2,
            'source_field_id'=>null,'lookup_field_id'=>10,'target_display_field_id'=>null,
            'junction_name'=>null,'is_required'=>1,'is_enabled'=>1,'configuration_json'=>'{}',
        ],
        [
            'name'=>'ed_ev_info_typ_to_ed_ev_type','relation_type'=>'n:1',
            'source_dataform_id'=>2,'target_dataform_id'=>2,
            'source_field_id'=>11,'lookup_field_id'=>null,'target_display_field_id'=>null,
            'junction_name'=>null,'is_required'=>1,'is_enabled'=>1,
            'configuration_json'=>json_encode([
                'semantics'=>'lookup-base-table-v1','lookup_source_kind'=>'base_table',
                'base_table'=>['table'=>'ed_ev_type','key_column'=>'id','display_column'=>'ev_type'],
            ],JSON_UNESCAPED_SLASHES),
        ],
        [
            'name'=>'ed_ev_info_fromperson_to_ed_person_id','relation_type'=>'n:1',
            'source_dataform_id'=>2,'target_dataform_id'=>2,
            'source_field_id'=>12,'lookup_field_id'=>null,'target_display_field_id'=>null,
            'junction_name'=>null,'is_required'=>1,'is_enabled'=>1,
            'configuration_json'=>json_encode([
                'semantics'=>'lookup-base-table-v1','lookup_source_kind'=>'base_table',
                'base_table'=>['table'=>'ed_ev_person','key_column'=>'id','display_column'=>'email'],
            ],JSON_UNESCAPED_SLASHES),
        ],
    ],
];
$method=new ReflectionMethod(ProjectPackageManager::class,'relationPreviewRows');
$method->setAccessible(true);
$rows=$method->invoke(null,$config);
$checks=[
    'HF51 marker'=>str_contains($page,'DataForm Workspace · HF51'),
    'detail heading'=>str_contains($page,'Beziehungen und Lookups im Paket'),
    'relation table mapping column'=>str_contains($page,'<th>Zuordnung</th>'),
    'three relation descriptions'=>count($rows)===3,
    '1:n mapping'=>($rows[0]['mapping']??'')==='id → to_ev_id',
    '1:n source'=>($rows[0]['source']??'')==='Ed Ev',
    '1:n target'=>($rows[0]['target']??'')==='Ed Ev Info',
    'type base target'=>($rows[1]['target']??'')==='Basistabelle → ed_ev_type',
    'type mapping'=>($rows[1]['mapping']??'')==='typ → ed_ev_type.id',
    'type display'=>($rows[1]['display']??'')==='ev_type',
    'person mapping'=>($rows[2]['mapping']??'')==='from_person → ed_ev_person.id',
    'person display'=>($rows[2]['display']??'')==='email',
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
