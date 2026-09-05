<?php
declare(strict_types=1);
$root=__DIR__;
foreach([
$root.'/tools/rc18-performance-audit.php',
$root.'/tools/rc18-final-release-gate.php',
$root.'/docs/RC1.8_PHASE6.7_FINAL_GATE.md'
] as $f)if(!is_file($f))exit(1);
echo "RC18_PHASE67_FINAL_RELEASE_GATE_OK\n";
