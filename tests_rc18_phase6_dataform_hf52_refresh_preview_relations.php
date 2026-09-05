<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/products/dataform/system/ProjectPackageManager.php';
$page=file_get_contents($root.'/products/dataform/packages.php');
$manager=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$config=[
 'dataforms'=>[
  ['id'=>1,'name'=>'Ed Ev','slug'=>'ed-ev'],
  ['id'=>2,'name'=>'Ed Ev Info','slug'=>'ed-ev-info'],
 ],
 'dataform_fields'=>[
  ['id'=>10,'dataform_id'=>2,'name'=>'to_ev_id','label'=>'To Ev Id'],
  ['id'=>11,'dataform_id'=>2,'name'=>'typ','label'=>'Typ'],
 ],
 'dataform_relations'=>[
  [
   'name'=>'ed_ev_id_to_ed_ev_info_id','relation_type'=>'1:n',
   'source_dataform_id'=>1,'target_dataform_id'=>2,'source_field_id'=>null,
   'lookup_field_id'=>10,'target_display_field_id'=>null,'junction_name'=>null,
   'is_required'=>1,'is_enabled'=>1,'configuration_json'=>'{}',
  ],
  [
   'name'=>'ed_ev_info_typ_to_ed_ev_type','relation_type'=>'n:1',
   'source_dataform_id'=>2,'target_dataform_id'=>2,'source_field_id'=>11,
   'lookup_field_id'=>null,'target_display_field_id'=>null,'junction_name'=>null,
   'is_required'=>1,'is_enabled'=>1,
   'configuration_json'=>json_encode([
    'semantics'=>'lookup-base-table-v1','lookup_source_kind'=>'base_table',
    'base_table'=>['table'=>'ed_ev_type','key_column'=>'id','display_column'=>'ev_type'],
   ],JSON_UNESCAPED_SLASHES),
  ],
 ],
];
$method=new ReflectionMethod(ProjectPackageManager::class,'relationPreviewRows');
$method->setAccessible(true);
$rows=$method->invoke(null,$config);
$checks=[
 'HF52+ marker'=>str_contains($page,'DataForm Workspace · HF53'),
 'public refresh method'=>str_contains($manager,'public static function refreshPreviewDetails(array $preview): array'),
 'refresh reads config trio'=>str_contains($manager,"foreach (['dataforms','dataform_fields','dataform_relations'] as \$table)") && str_contains($manager,"\$zip->getFromName('database/'.\$table.'.json')"),
 'refresh recomputes relations'=>str_contains($manager,'$preview[\'relations\']=self::relationPreviewRows($configRows);'),
 'page refresh call'=>str_contains($page,'ProjectPackageManager::refreshPreviewDetails($preview)'),
 'page persists refreshed session'=>str_contains($page,'$_SESSION[\'project_package_preview\']=$preview;'),
 'two relation descriptions'=>count($rows)===2,
 '1:n mapping'=>($rows[0]['mapping']??'')==='id → to_ev_id',
 'lookup target'=>($rows[1]['target']??'')==='Basistabelle → ed_ev_type',
 'lookup mapping'=>($rows[1]['mapping']??'')==='typ → ed_ev_type.id',
 'lookup display'=>($rows[1]['display']??'')==='ev_type',
 'stale preview diagnostic'=>str_contains($page,'Beziehungsdetails konnten aus diesem Vorschauzustand nicht rekonstruiert werden'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok)$failed[]=$name;}
exit($failed?1:0);
