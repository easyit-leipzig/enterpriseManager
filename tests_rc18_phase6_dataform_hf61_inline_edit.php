<?php
$records=file_get_contents(__DIR__.'/products/dataform/records.php');
$checks=[
    'hf61 badge'=>str_contains($records,'DataForm Workspace · HF61'),
    'inline edit attempt flag'=>str_contains($records,'$inlineEditAttempt = false'),
    'post detects inline edit'=>str_contains($records,"inline_edit']??''"),
    'inline edit forms generated'=>str_contains($records,"df-inline-edit-"),
    'inline edit hidden marker'=>str_contains($records,'name="inline_edit" value="1"'),
    'row form keeps active record'=>str_contains($records,'name="active_record"'),
    'existing rows use editable controls'=>str_contains($records,'df-record-inline-edit-cell'),
    'existing rows call input renderer'=>str_contains($records,'df-inline-create-control df-inline-edit-control'),
    'existing rows have save button'=>str_contains($records,'>Speichern</button>'),
    'legacy readonly output not used in rows'=>!str_contains($records,'<td class="df-record-output-cell"><?= df_record_list_control'),
    'save keeps list open'=>str_contains($records,'Bestehende Datensaetze werden direkt in der Tabelle gespeichert.'),
    'validation stays in row'=>str_contains($records,'Validierungsfehler bleiben in derselben Tabellenzeile sichtbar.'),
    'focus activates row without reload'=>str_contains($records,"row.addEventListener('focusin'"),
    'new row remains inline create'=>str_contains($records,'id="record-new-row"'),
];
$ok=true;
foreach($checks as $name=>$pass){
    echo ($pass?'PASS':'FAIL')." - $name\n";
    $ok=$ok&&$pass;
}
exit($ok?0:1);
