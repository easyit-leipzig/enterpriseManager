<?php
declare(strict_types=1);
$root=__DIR__;
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$css=(string)file_get_contents($root.'/products/dataform/assets/workspace.css');
$checks=[
 'Menüleiste verwendet echte Dropdown-Gruppen'=>substr_count($runtime,'<details class="df-menu-item">')===7,
 'Alle sieben Hauptmenüs vorhanden'=>array_reduce(['Datei','Bearbeiten','Ansicht','Projekt','Werkzeuge','Fenster','Hilfe'],fn($ok,$x)=>$ok&&str_contains($runtime,'<summary>'.$x.'</summary>'),true),
 'Alte unbelegte Button-Menüleiste entfernt'=>!str_contains($runtime,'<button type="button">Datei</button><button type="button">Bearbeiten</button>'),
 'Datei bietet DataForm-Neuanlage'=>str_contains($runtime,'section=dataforms#new-dataform">Neues DataForm'),
 'Datei bietet echten Anwenderpaket-POST'=>str_contains($runtime,'action="../../app/projects/export.php"')&&str_contains($runtime,'Anwenderpaket herunterladen'),
 'Bearbeiten verlinkt DataForm-Einstellungen und Designer'=>str_contains($runtime,'#dataform-settings">DataForm-Einstellungen')&&str_contains($runtime,'section=designer&amp;dataform='),
 'Bearbeiten verlinkt Layout Verhalten Workflow Beziehungen'=>array_reduce(['mode=layout','mode=behavior','workflow.php?project=','relations.php?project='],fn($ok,$x)=>$ok&&str_contains($runtime,$x),true),
 'Ansicht verlinkt Realvorschau und Datensätze'=>str_contains($runtime,'#real-preview">Realvorschau')&&str_contains($runtime,'records.php?project='),
 'Projekt verlinkt Übersicht Bearbeiten Builder Pakete Wechsel'=>array_reduce(['../../app/projects/view.php?id=','../../app/projects/edit.php?id=','applications.php?project=','packages.php?project=','../../app/projects/index.php'],fn($ok,$x)=>$ok&&str_contains($runtime,$x),true),
 'Werkzeuge verlinkt Query Reports API Module'=>array_reduce(['queries.php?project=','reports.php?project=','api.php?project=','modules.php?project='],fn($ok,$x)=>$ok&&str_contains($runtime,$x),true),
 'Fenster besitzt echte neue-Fenster-Aktionen'=>substr_count($runtime,'target="_blank" rel="noopener"')>=2,
 'Hilfe springt zur Kontexthilfe und Dokumentation'=>str_contains($runtime,'href="#df-context-help">Kontexthilfe')&&str_contains($runtime,'href="../../documentation.php"'),
 'Kontexthilfe besitzt Sprungziel'=>str_contains($runtime,'id="df-context-help" aria-label="Eigenschaften und Kontexthilfe"'),
 'Dropdown-CSS vorhanden'=>str_contains($css,'.df-menu-dropdown{')&&str_contains($css,'.df-menu-item>summary'),
 'Menüs schließen bei Außenklick und Escape'=>str_contains($runtime,"if(menu.contains(ev.target))return;")&&str_contains($runtime,"if(ev.key!=='Escape')return;"),
];
$pass=0;
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." | $name\n"; if($ok)$pass++;}
echo "RESULT | $pass/".count($checks)." PASS\n";
exit($pass===count($checks)?0:1);
