<?php
declare(strict_types=1);
$root=__DIR__;$runtime=(string)file_get_contents($root.'/system/app/project_runtime/DataFormApp.php');$js=(string)file_get_contents($root.'/system/app/project_runtime/assets/app.js');$css=(string)file_get_contents($root.'/system/app/project_runtime/assets/app.css');$checks=[];$c=function($n,$ok)use(&$checks){$checks[]=['name'=>$n,'status'=>$ok?'PASS':'FAIL'];};
$c('1:n child relation query',str_contains($runtime,"r.relation_type='1:n'")&&str_contains($runtime,'r.source_dataform_id=?')&&str_contains($runtime,'r.target_dataform_id'));
$c('child FK lookup field used',str_contains($runtime,"lookup_field_name")&&str_contains($runtime,'df_child_records'));
$c('all matching child records filtered by parent id',str_contains($runtime,'===(string)$parentRecordId'));
$c('child collections rendered in parent page',str_contains($runtime,'df_child_collections_html')&&str_contains($runtime,'data-child-collections'));
$c('child fragment endpoint',str_contains($runtime,"child_fragment")&&str_contains($runtime,"Content-Type: text/html"));
$c('reload-free pointer loads children',str_contains($js,'loadChildren(id)')&&str_contains($js,"fetch(u")&&str_contains($js,"child_fragment"));
$c('child create link preserves relation context',str_contains($runtime,"parent_relation=")&&str_contains($runtime,"parent_record="));
$c('child page filters to parent context',str_contains($runtime,'df_parent_context')&&str_contains($runtime,'$all=array_values(array_filter($all'));
$c('child create/update enforce parent FK',substr_count($runtime,'$data[(string)$parentContext[\'lookup_field_name\']]=(string)$parentContext[\'parent_record_id\']')>=2);
$c('parent FK field locked in child context',str_contains($runtime,'readonly')&&str_contains($runtime,"lookup_field_id"));
$c('child actions show and edit',str_contains($runtime,'Kinddatensatz anzeigen')&&str_contains($runtime,'Kinddatensatz bearbeiten'));
$c('child UI styling',str_contains($css,'.child-collections-panel')&&str_contains($css,'.child-collection'));
$fail=array_filter($checks,fn($x)=>$x['status']==='FAIL');foreach($checks as $x)echo $x['status'].' '.$x['name'].PHP_EOL;echo count($checks).'/'.count($checks).' checks, '.count($fail).' failed'.PHP_EOL;exit($fail?1:0);
