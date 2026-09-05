<?php
declare(strict_types=1);
$root=__DIR__;
$pkg=(string)file_get_contents($root.'/products/dataform/packages.php');
$mgr=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$checks=[
 'HF48 badge'=>str_contains($pkg,'DataForm Workspace · HF48'),
 'clickable stored package row'=>str_contains($pkg,'data-package-load='),
 'load function'=>str_contains($pkg,'function loadStoredPackageConfig(row)'),
 'reload includes'=>str_contains($pkg,"setNamedCheckbox('include_relations',includes.relations)"),
 'reload dataforms'=>str_contains($pkg,"setMultiSelection('dataform_ids',selection.dataformIds||[])"),
 'reload tables'=>str_contains($pkg,"setMultiSelection('base_tables',selection.physicalTables||[])"),
 'selection highlight'=>str_contains($pkg,"row.classList.add('is-selected')"),
 'loaded notice'=>str_contains($pkg,'df-package-loaded-package'),
 'safe notice rendering'=>str_contains($pkg,"strong.textContent='Gespeicherte Paketkonfiguration geladen:'"),
 'ignore CRUD click'=>str_contains($pkg,"event.target.closest('a,button,input,select,textarea,label,form')"),
 'keyboard support'=>str_contains($pkg,"event.key==='Enter'||event.key===' '"),
 'manager exposes includes'=>str_contains($mgr,"'includes'=>is_array(\$manifest['includes']??null)"),
 'manager exposes selection'=>str_contains($mgr,"'selection'=>is_array(\$manifest['selection']??null)"),
];
$fail=[];
foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if(!$ok)$fail[]=$name;}
exit($fail?1:0);
