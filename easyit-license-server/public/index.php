<?php
declare(strict_types=1);
$config=require dirname(__DIR__).'/bootstrap.php';
\EasyIT\LicenseServer\App\Application::run($config);
