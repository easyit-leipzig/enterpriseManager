<?php
$records=file_get_contents(__DIR__.'/products/dataform/records.php');
$checks=[
    'hf60 badge'=>str_contains($records,'DataForm Workspace · HF60'),
    'physical table view marker'=>str_contains($records,"\$isPhysicalTableView=\$recordStorageMode==='physical'"),
    'physical list uses all fields'=>str_contains($records,'? array_values($fields)'),
    'default columns are all list fields'=>str_contains($records,"\$defaultColumns = array_map("),
    'physical visible columns all valid names'=>str_contains($records,'$visibleColumns=$validNames;'),
    'no physical 4-column default slice'=>!str_contains($records,'array_slice($listFields, 0, 4)'),
    'physical column settings explanation'=>str_contains($records,'alle Tabellenfelder angezeigt'),
    'physical columns cannot be unchecked'=>str_contains($records,'<input type="checkbox" checked disabled>'),
    'hidden physical post columns exist'=>str_contains($records,'type="hidden" name="columns[]"'),
    'inline create still follows visible columns'=>str_contains($records,'df_record_inline_create_control($pdo,$field'),
];
$ok=true;
foreach($checks as $name=>$pass){
    echo ($pass?'PASS':'FAIL')." - $name\n";
    $ok=$ok&&$pass;
}
exit($ok?0:1);
