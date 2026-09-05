<?php
declare(strict_types=1);
$root=__DIR__;$runtime=file_get_contents($root.'/products/dataform/runtime.php');$records=file_get_contents($root.'/products/dataform/records.php');$mgr=file_get_contents($root.'/products/dataform/system/DataFormManager.php');$app=file_get_contents($root.'/system/app/project_runtime/DataFormApp.php');$js=file_get_contents($root.'/system/app/project_runtime/assets/app.js');$checks=[];$c=function($n,$v)use(&$checks){$checks[$n]=$v?'PASS':'FAIL';};
$c('view mode setting',str_contains($runtime,'name="view_mode"')&&str_contains($runtime,'dialog'));
$c('per page setting',str_contains($runtime,'name="default_per_page"'));
$c('addcss setting',str_contains($runtime,'name="additional_css"'));
$c('css class setting',str_contains($runtime,'name="css_class"'));
$c('permissions settings',str_contains($runtime,"'allow_create'=>'Datensätze anlegen'")&&str_contains($runtime,"'allow_edit'=>'Datensätze bearbeiten'")&&str_contains($runtime,"'allow_delete'=>'Datensätze löschen'"));
$c('search pagination settings',str_contains($runtime,"'show_search'=>'Suche anzeigen'")&&str_contains($runtime,"'show_pagination'=>'Paginierung anzeigen'"));
$c('event hooks',str_contains($runtime,"'open'=>'Öffnen'")&&str_contains($runtime,"'close'=>'Schließen'")&&str_contains($runtime,"'record_change'=>'Datensatzwechsel'")&&str_contains($runtime,"'before_save'=>'Vor Speichern'")&&str_contains($runtime,"'after_delete'=>'Nach Löschen'"));
$c('schema upgrade',str_contains($mgr,"'view_mode'")&&str_contains($mgr,"'event_handlers_json'"));
$c('records honors default per page',str_contains($records,'$defaultPerPage')&&str_contains($records,'$showSearch')&&str_contains($records,'$showPagination'));
$c('export runtime honors metadata',str_contains($app,"\$form['view_mode']")&&str_contains($app,'event_handlers_json'));
$c('export JS events',str_contains($js,'easyitDataFormEvent')&&str_contains($js,"view_mode==='dialog'"));
foreach($checks as $n=>$v)echo "$v $n\n";$fail=count(array_filter($checks,fn($v)=>$v==='FAIL'));exit($fail?1:0);
