<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
    $root.'/tools/rc18-production-security-audit.php',
    $root.'/DataForm5-Core/.env.production.example',
    $root.'/docs/RC1.8_PHASE6.6_PRODUCTION_SECURITY.md'
] as $file) if(!is_file($file)) exit(1);
if(is_file($root.'/DataForm5-Core/.env')) exit(2);
$ignore=(string)file_get_contents($root.'/DataForm5-Core/.gitignore');
if(!str_contains($ignore,'.env')) exit(3);
echo "RC18_PHASE66_PRODUCTION_SECURITY_OK
";
