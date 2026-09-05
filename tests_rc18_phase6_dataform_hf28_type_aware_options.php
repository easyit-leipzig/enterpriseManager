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

$f('server-side request normalizer',
    str_contains($manager,'public static function normalizeColumnRequest')
);
$f('numeric extras are reset instead of blindly rejected',
    str_contains($manager,'UNSIGNED wurde entfernt')
    && str_contains($manager,'ZEROFILL wurde entfernt')
);
$f('ZEROFILL auto-enables UNSIGNED',
    str_contains($manager,'UNSIGNED wurde automatisch ergänzt')
);
$f('AUTO_INCREMENT incompatible type resets',
    str_contains($manager,'AUTO_INCREMENT wurde entfernt, weil es nur für INT/BIGINT möglich ist.')
);
$f('AUTO_INCREMENT nullable resets',
    str_contains($manager,'AUTO_INCREMENT wurde entfernt, weil das Feld NULL-Werte zulässt.')
);
$f('existing AUTO_INCREMENT conflict resets',
    str_contains($manager,'AUTO_INCREMENT wurde entfernt, weil die Tabelle bereits das AUTO_INCREMENT-Feld')
);
$f('ON UPDATE incompatibility resets',
    str_contains($manager,'ON UPDATE CURRENT_TIMESTAMP wurde entfernt')
);
$f('CURRENT_TIMESTAMP default resets',
    str_contains($manager,'CURRENT_TIMESTAMP wurde als Vorgabewert zurückgesetzt')
);
$f('DEFAULT NULL resets for NOT NULL',
    str_contains($manager,'DEFAULT NULL wurde zurückgesetzt')
);
$f('TEXT secondary index resets',
    str_contains($manager,'Der Sekundärindex wurde entfernt, weil TEXT ohne Präfix')
);
$f('runtime uses server normalization before create/update',
    substr_count($runtime,'TableWorkspaceManager::normalizeColumnRequest(')>=2
);
$f('global info notice for automatic corrections',
    str_contains($runtime,'<?php if ($info): ?>')
    && str_contains($runtime,'notice info')
);
$f('datatype-aware UI controller',
    str_contains($runtime,'id="hf28-column-editor-ui"')
    && str_contains($runtime,'function typeState()')
);
$f('numeric extra options dynamically hidden',
    str_contains($runtime,"setExtraAvailability(\n                'unsigned'")
    && str_contains($runtime,"setExtraAvailability(\n                'zerofill'")
);
$f('AUTO_INCREMENT dynamically constrained',
    str_contains($runtime,'var autoIncrementAvailable=')
    && str_contains($runtime,"tableAutoIncrementColumn===originalColumn")
);
$f('CURRENT_TIMESTAMP option dynamically constrained',
    str_contains($runtime,"'current_timestamp'")
    && str_contains($runtime,'state.temporal && !autoIncrementSelected')
);
$f('TEXT index dynamically constrained',
    str_contains($runtime,"setSingleOption(indexKind,'index',state.indexable)")
    && str_contains($runtime,"setSingleOption(indexKind,'unique',state.indexable)")
);
$f('local correction information box',
    str_contains($runtime,'data-column-option-info')
);
$f('type datalist provided',
    str_contains($runtime,'id="df-column-types"')
    && str_contains($runtime,'value="datetime"')
);
$f('info styling',
    str_contains($css,'.notice.info')
    && str_contains($css,'.df-column-option-info')
);
$f('HF28 marker',
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
