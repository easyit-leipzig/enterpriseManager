<?php
declare(strict_types=1);

$base = __DIR__;
$configFile = $base . '/config/app.php';
if (!is_file($configFile)) {
    $configFile = $base . '/config/app.example.php';
}
$config = require $configFile;

spl_autoload_register(static function (string $class) use ($base): void {
    $prefix = 'EasyIT\\LicenseServer\\';
    if (!str_starts_with($class, $prefix)) return;
    $rel = substr($class, strlen($prefix));
    $file = $base . '/src/' . str_replace('\\', '/', $rel) . '.php';
    if (is_file($file)) require $file;
});

return $config;
