<?php
declare(strict_types=1);

$root=__DIR__;
$required=[
    $root.'/DataForm5-Core/bootstrap/namespaces.php',
    $root.'/DataForm5-Core/config/providers.php',
    $root.'/tools/architecture-audit.php',
    $root.'/docs/RC1.8_PHASE1_CORE_CONSOLIDATION.md',
];
foreach($required as $file){
    if(!is_file($file)){fwrite(STDERR,"Missing: {$file}\n");exit(1);}
}
if(is_dir($root.'/modules/phase-o-smoke')){
    fwrite(STDERR,"Legacy smoke module still present\n");exit(2);
}
$app=(string)file_get_contents($root.'/DataForm5-Core/bootstrap/app.php');
if(!str_contains($app,"config/providers.php")||str_contains($app,"new FilesystemServiceProvider")){
    fwrite(STDERR,"Provider bootstrap not consolidated\n");exit(3);
}
$autoload=(string)file_get_contents($root.'/DataForm5-Core/bootstrap/autoload.php');
if(!str_contains($autoload,"namespaces.php")){
    fwrite(STDERR,"Namespace map not centralized\n");exit(4);
}
echo "RC18_PHASE1_CONSOLIDATION_OK\n";
