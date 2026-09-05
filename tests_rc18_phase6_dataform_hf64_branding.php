<?php
declare(strict_types=1);
$root=__DIR__;
$layout=(string)file_get_contents($root.'/system/ui/layout.php');
$runtime=(string)file_get_contents($root.'/products/dataform/system/WorkspaceLayout.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$css=(string)file_get_contents($root.'/products/dataform/assets/branding/dataform-branding.css');
$logo=$root.'/products/dataform/assets/branding/easyit-dataform-logo.png';
$png=is_file($logo)?(string)file_get_contents($logo):'';
$tests=[
    'branding logo exists'=>is_file($logo) && filesize($logo)>0,
    'branding logo png'=>str_starts_with($png,"\x89PNG\r\n\x1a\n"),
    'branding css exists'=>$css!=='' && str_contains($css,'.easyit-dataform-branding__logo'),
    'generic layout detects DataForm page'=>str_contains($layout,"/products/dataform/"),
    'generic layout loads branding css'=>str_contains($layout,'easyit-dataform-branding-css'),
    'generic layout renders logo'=>str_contains($layout,'easyit-dataform-logo.png') && str_contains($layout,'data-dataform-branding="HF64"'),
    'runtime loads branding css'=>str_contains($runtime,'dataform-branding.css'),
    'runtime renders logo'=>str_contains($runtime,'easyit-dataform-logo.png'),
    'package manifest declares branding'=>str_contains($manager,"'branding'=>[") && str_contains($manager,"'logo'=>'assets/branding/easyit-dataform-logo.png'"),
    'package exports logo'=>str_contains($manager,'addFile($brandingLogo,\'assets/branding/easyit-dataform-logo.png\')'),
    'package whitelist accepts logo'=>str_contains($manager,'assets/branding/easyit-dataform-logo\\.png'),
    'package validates png'=>str_contains($manager,'Das DataForm-Logo im Paket ist keine gültige PNG-Datei.'),
];
$failed=[];
foreach($tests as $name=>$ok){echo ($ok?'PASS':'FAIL').' - '.$name.PHP_EOL;if(!$ok)$failed[]=$name;}
if($failed){fwrite(STDERR,'Failed: '.implode(', ',$failed).PHP_EOL);exit(1);} 
echo 'HF64: '.count($tests).'/'.count($tests).' PASS'.PHP_EOL;
