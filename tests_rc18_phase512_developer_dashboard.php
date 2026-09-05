<?php
declare(strict_types=1);
$r=__DIR__;
$f=$r.'/app/developer/index.php';
if(!is_file($f))exit(1);
$c=(string)file_get_contents($f);
foreach(['Developer Dashboard 5.12','Quality Center','SDK-Konsole','Profiler','Container Inspector','Event Inspector','Hook Inspector','enterprise_quality_center'] as $needle)if(!str_contains($c,$needle))exit(2);
echo "RC18_PHASE512_DEVELOPER_DASHBOARD_OK\n";
