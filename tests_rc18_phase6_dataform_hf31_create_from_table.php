<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/DataFormManager.php');
$tableManager=(string)file_get_contents($r.'/products/dataform/system/TableWorkspaceManager.php');
$migration=(string)file_get_contents($r.'/installer/schema/project/005_dataform_table_bindings.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('direct create action exists',
    str_contains($runtime,"create_dataform_from_table")
    && str_contains($runtime,'DataForm aus Tabelle erstellen')
);
$f('action is CSRF protected',
    str_contains($runtime,"(string)(\$_POST['action'] ?? '') === 'create_dataform_from_table'")
    && str_contains($runtime,'enterprise_check_csrf')
);
$f('persistent binding schema',
    str_contains($manager,'dataform_table_bindings')
    && str_contains($migration,'dataform_table_bindings')
);
$f('one dataform per table binding',
    str_contains($manager,'uq_dataform_table_binding_form')
    && str_contains($manager,'uq_dataform_table_binding_source')
);
$f('project application tables can create dataform while internal tables stay protected',
    str_contains($manager,'TableWorkspaceManager::fieldCrudAllowed($pdo,$table)')
    && str_contains($manager,'geschützten internen Systemtabelle')
);
$f('technical id is omitted',
    str_contains($manager,'isTechnicalIdColumn')
    && str_contains($manager,"strtolower((string)(\$column['Field']??''))==='id'")
);
$f('SQL types map to DataForm types',
    str_contains($manager,"\$fieldType='checkbox'")
    && str_contains($manager,"\$fieldType='number'")
    && str_contains($manager,"\$fieldType='date'")
    && str_contains($manager,"\$fieldType='datetime'")
    && str_contains($manager,"\$fieldType='textarea'")
);
$f('required is derived from nullability',
    str_contains($manager,"'is_required'=>!\$nullable")
);
$f('SQL default is transferred',
    str_contains($manager,"\$configuration['default_mode']='current_timestamp'")
    && str_contains($manager,"\$configuration['default_value']=\$defaultString")
);
$f('table origin is stored per field',
    str_contains($manager,"'table_binding'=>[")
    && str_contains($manager,"'sql_type'=>\$type")
);
$f('existing binding opens dataform',
    str_contains($runtime,'Zugehöriges DataForm öffnen')
    && str_contains($runtime,"\$tableDataformBinding['dataform_id']")
);
$f('duplicate creation returns existing binding',
    str_contains($manager,"'created'=>false")
    && str_contains($manager,'tableBinding($pdo,$table')
);
$f('bound table delete is protected',
    str_contains($tableManager,'Die Tabelle ist mit dem DataForm')
    && str_contains($tableManager,'dataform_table_bindings')
);
$f('creation goes directly to designer',
    str_contains($runtime,"\$sectionKey='designer'")
    && str_contains($runtime,"\$selectedDataformId=(int)\$createdDataform['dataform_id']")
);
$f('table action styling',str_contains($css,'.df-table-actions'));
$f('HF31 marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
