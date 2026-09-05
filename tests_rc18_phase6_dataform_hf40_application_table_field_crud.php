<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/TableWorkspaceManager.php');
$dataform=(string)file_get_contents($r.'/products/dataform/system/DataFormManager.php');

require_once $r.'/products/dataform/system/TableWorkspaceManager.php';

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('application table classifier exists',
    str_contains($manager,'public static function isApplicationTable')
    && str_contains($manager,'public static function fieldCrudAllowed')
);
$f('internal dataform tables stay protected',
    TableWorkspaceManager::isProtectedInternalTable('dataform_fields')
    && TableWorkspaceManager::isProtectedInternalTable('dataform_relations')
);
$f('internal workflow tables stay protected',
    TableWorkspaceManager::isProtectedInternalTable('workflow_states')
    && TableWorkspaceManager::isProtectedInternalTable('workflow_transitions')
);
$f('migration and datasource tables stay protected',
    TableWorkspaceManager::isProtectedInternalTable('migrations')
    && TableWorkspaceManager::isProtectedInternalTable('data_sources')
);
$f('ed application tables are not classified internal',
    !TableWorkspaceManager::isProtectedInternalTable('ed_ev')
    && !TableWorkspaceManager::isProtectedInternalTable('ed_ev_info')
    && !TableWorkspaceManager::isProtectedInternalTable('ed_ev_type')
    && !TableWorkspaceManager::isProtectedInternalTable('ed_ev_person')
);
$f('field mutation guard uses field CRUD policy',
    str_contains($manager,'self::assertFieldCrudTable($pdo,$table);')
    && str_contains($manager,'self::fieldCrudAllowed($pdo,$table)')
);
$f('runtime exposes field CRUD by field_crud state',
    str_contains($runtime,"!empty(\$tableInspection['field_crud'])")
    && str_contains($runtime,'<th>Aktionen</th>')
    && str_contains($runtime,'+ Feld hinzufügen')
    && str_contains($runtime,'>Bearbeiten</a>')
    && str_contains($runtime,'>Löschen</button>')
);
$f('application table notice is explicit',
    str_contains($runtime,'Anwendungstabelle der Projekt-Datenbank')
    && str_contains($runtime,'Feld-CRUD ist aktiv')
);
$f('application table can create DataForm',
    str_contains($dataform,'TableWorkspaceManager::fieldCrudAllowed($pdo,$table)')
    && str_contains($runtime,'DataForm aus Tabelle erstellen')
);
$f('table deletion remains managed-only',
    str_contains($runtime,"if(!empty(\$tableInspection['managed']))")
    && str_contains($runtime,'drop_managed_table')
);
$f('technical id remains protected',
    str_contains($manager,"if (\$column === 'id')")
    && str_contains($manager,'DataForm-Pflichtfeld')
);
$f('foreign keys remain protected from destructive column mutation',
    str_contains($manager,'information_schema.KEY_COLUMN_USAGE')
    && str_contains($manager,'Primär-/Fremdschlüssel')
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF40',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
