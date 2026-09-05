<?php
declare(strict_types=1);

$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/TableWorkspaceManager.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');

$c=[];
$f=function(string $name,bool $ok)use(&$c):void{
    $c[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];
};

$f('editable default selector',
    str_contains($runtime,'name="column_default_kind"')
    && str_contains($runtime,'CURRENT_TIMESTAMP')
    && str_contains($runtime,'Fester Wert')
);
$f('editable default value input',
    str_contains($runtime,'name="column_default_value"')
    && str_contains($runtime,'data-column-default-value')
);
$f('default passed to create method',
    str_contains($runtime,"(string)(\$_POST['column_default_kind'] ?? 'none')")
    && str_contains($runtime,"isset(\$_POST['column_default_value'])")
);
$f('secondary index selector',
    str_contains($runtime,'name="column_index_kind"')
    && str_contains($runtime,'UNIQUE INDEX')
    && str_contains($runtime,'Kein DataForm-Index')
);
$f('multiple extra selector',
    str_contains($runtime,'name="column_extra[]"')
    && str_contains($runtime,'multiple size="4"')
);
foreach(['UNSIGNED','ZEROFILL','AUTO_INCREMENT','ON UPDATE CURRENT_TIMESTAMP'] as $extra){
    $f('extra option '.$extra,str_contains($runtime,$extra));
}
$f('primary and foreign keys remain protected',
    str_contains($manager,"CONSTRAINT_NAME='PRIMARY'")
    && str_contains($manager,'REFERENCED_TABLE_NAME IS NOT NULL')
);
$f('unique secondary index is mutable',
    str_contains($manager,'Primär-/Fremdschlüssel')
    && !str_contains($manager,"CONSTRAINT_NAME='UNIQUE'")
);
$f('managed secondary indexes only',
    str_contains($manager,"str_starts_with(\$name,'idx_df_')")
    && str_contains($manager,"str_starts_with(\$name,'uq_df_')")
);
$f('index kind whitelist',
    str_contains($manager,"['none','index','unique']")
);
$f('managed index names are bounded',
    str_contains($manager,'64-strlen($prefix)')
    && str_contains($manager,"hash('sha256'")
);
$f('unique duplicate precheck',
    str_contains($manager,'assertUniqueValues')
    && str_contains($manager,'HAVING COUNT(*)>1')
);
$f('default kind whitelist',
    str_contains($manager,"['none','null','literal','current_timestamp']")
);
$f('CURRENT_TIMESTAMP type guard',
    str_contains($manager,'CURRENT_TIMESTAMP ist als Vorgabewert nur für DATETIME/TIMESTAMP zulässig.')
);
$f('extra whitelist',
    str_contains($manager,"'unsigned'")
    && str_contains($manager,"'zerofill'")
    && str_contains($manager,"'auto_increment'")
    && str_contains($manager,"'on_update_current_timestamp'")
);
$f('numeric extra type guard',
    str_contains($manager,'UNSIGNED und ZEROFILL sind nur für numerische Felder zulässig.')
);
$f('auto increment safety',
    str_contains($manager,'Die Tabelle besitzt bereits das AUTO_INCREMENT-Feld')
    && str_contains($manager,'AUTO_INCREMENT kann nicht mit einem eigenen Vorgabewert kombiniert werden.')
);
$f('on update type guard',
    str_contains($manager,'ON UPDATE CURRENT_TIMESTAMP ist nur für DATETIME/TIMESTAMP zulässig.')
);
$f('existing external indexes are displayed but preserved',
    str_contains($runtime,'Bereits vorhandene externe/mehrspaltige Indizes')
    && str_contains($runtime,'Diese werden nicht verändert.')
);
$f('multiple-select css',
    str_contains($css,'.df-multi-select')
);
$f('HF27 runtime marker',
    str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE')
);

$failed=count(array_filter($c,static fn(array $row):bool=>$row['status']==='FAIL'));

echo json_encode([
    'release'=>'RC1.8',
    'hotfix'=>'HF34',
    'status'=>$failed===0?'PASS':'FAIL',
    'checks'=>$c,
    'summary'=>['checks'=>count($c),'failed'=>$failed]
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;

exit($failed===0?0:1);
