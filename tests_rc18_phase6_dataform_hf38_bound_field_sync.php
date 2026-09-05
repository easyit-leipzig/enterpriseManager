<?php
declare(strict_types=1);

$r=__DIR__;
$page=(string)file_get_contents($r.'/products/dataform/relations.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/DataFormManager.php');
$relation=(string)file_get_contents($r.'/products/dataform/system/RelationManager.php');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('HF38+ workspace marker',preg_match('/DataForm Workspace · HF(?:3[89]|4[0-9]|[5-9][0-9])/', $page)===1);
$f('relations loads DataFormManager',str_contains($page,"require __DIR__ . '/system/DataFormManager.php';"));
$f('bound fields synchronize before relation repair',
    ($syncPos=strpos($page,'DataFormManager::synchronizeAllBoundTableFields($pdo)'))!==false
    && ($repairPos=strpos($page,'RelationManager::repairLegacyOneToMany($pdo)'))!==false
    && $syncPos<$repairPos
);
$f('all persistent table bindings are scanned',
    str_contains($manager,'public static function synchronizeAllBoundTableFields')
    && str_contains($manager,'FROM dataform_table_bindings ORDER BY dataform_id')
);
$f('single bound form synchronization exists',
    str_contains($manager,'public static function synchronizeBoundTableFields')
    && str_contains($manager,'SHOW FULL COLUMNS FROM')
    && str_contains($manager,'information_schema.TABLES')
);
$f('technical auto increment id is excluded',
    str_contains($manager,'if (self::isTechnicalIdColumn($column))')
);
$f('missing physical columns become dataform fields',
    str_contains($manager,"'INSERT INTO dataform_fields('")
    && str_contains($manager,'$mapped=self::mapColumn(')
    && str_contains($manager,'$created++')
);
$f('existing matching fields are not duplicated',
    str_contains($manager,'LOWER(name)=LOWER(?)')
    && str_contains($manager,"\$cfg['table_binding']=\$mapped")
);
$f('missing table_binding metadata is healed',
    str_contains($manager,'$needsLink=')
    && str_contains($manager,"\$cfg['table_binding']=\$expected")
    && str_contains($manager,'UPDATE dataform_fields SET configuration_json=?')
);
$f('phantom fields are still rejected by relation selector',
    str_contains($relation,"if (!\$state['usable'])")
    && str_contains($relation,'existiert nicht in der Kindtabelle')
);
$f('physical field ordering is synchronized afterwards',
    str_contains($manager,'self::syncFieldPositionsFromTable($pdo,$table)')
);
$f('sync result is visible to administrator',
    str_contains($page,'fehlende physische Tabellenfeld(er) wurden als DataForm-Felder registriert')
    && str_contains($page,'vorhandene Feldbindung(en) wurden synchronisiert')
);
$f('dropdown is built only after synchronized selectable fields',
    ($selectPos=strpos($page,'RelationManager::selectableFields($pdo)'))!==false
    && isset($syncPos)
    && $syncPos<$selectPos
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));
echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF38',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed],
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed===0?0:1);
