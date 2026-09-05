<?php
declare(strict_types=1);
$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/packages.php');
$checks=[
 'HF54 badge'=>str_contains($page,'DataForm Workspace · HF54'),
 'export exposes package id'=>str_contains($page,"X-DataForm-Package-ID"),
 'package id comes from manifest'=>str_contains($page,'$result[\'manifest\'][\'packageId\']'),
 'transient export state key'=>str_contains($page,'easyit.dataform.packageExportState.'),
 'state captures dataforms'=>str_contains($page,"state.dataformIds.push"),
 'state captures base tables'=>str_contains($page,"state.baseTables.push"),
 'state captures component checkboxes'=>str_contains($page,'state.checks[input.name]'),
 'export reads package id response header'=>str_contains($page,"response.headers.get('X-DataForm-Package-ID')"),
 'post export reload targets package id'=>str_contains($page,"'&load_package='+encodeURIComponent(packageId)"),
 'fallback restores transient selection'=>str_contains($page,'restoreExportState();'),
 'package row has stable href'=>str_contains($page,'data-package-href='),
 'package name is real link'=>str_contains($page,'class="df-package-load-link"'),
 'broken row token falls back to href'=>str_contains($page,"if(!config){") && str_contains($page,"window.location.href=fallback"),
 'export form has stable action'=>str_contains($page,'action="packages.php?project=<?=$projectId?>" class="form-grid" id="df-package-export-form"'),
];
$failed=[];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n";if(!$ok)$failed[]=$name;}
exit($failed?1:0);
