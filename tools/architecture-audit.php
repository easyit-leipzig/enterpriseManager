<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$phpFiles = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

$symbols = [];
$duplicates = [];
$legacy = [];
foreach ($phpFiles as $file) {
    $code = (string)file_get_contents($file);
    $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));

    if (str_contains($relative, '/storage/') || str_starts_with($relative, 'storage/')) {
        continue;
    }

    $isTestFile = str_starts_with($relative, 'tests_') || str_contains($relative, '/tests/');

    $namespace = '';
    if (preg_match('/namespace\s+([^;]+);/', $code, $m)) {
        $namespace = trim($m[1]);
    }

    if (preg_match_all('/\b(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)|\binterface\s+([A-Za-z_][A-Za-z0-9_]*)|\btrait\s+([A-Za-z_][A-Za-z0-9_]*)/', $code, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $name = $m[1] ?: ($m[2] ?: $m[3]);
            $fqcn = $namespace !== '' ? $namespace . '\\' . $name : $name;
            if ($isTestFile) {
                continue;
            }
            if (isset($symbols[$fqcn])) {
                $duplicates[$fqcn] = [$symbols[$fqcn], $relative];
            } else {
                $symbols[$fqcn] = $relative;
            }
        }
    }

    if (str_contains($relative, 'modules/phase-') && !str_contains($relative, 'tests')) {
        $legacy[] = $relative;
    }
}

$providers = require $root . '/DataForm5-Core/config/providers.php';
$providerErrors = [];
foreach ($providers as $class) {
    if (!is_string($class)) {
        $providerErrors[] = '[non-string]';
        continue;
    }
    require_once $root . '/DataForm5-Core/bootstrap/autoload.php';
    if (!class_exists($class)) {
        $providerErrors[] = $class;
    }
}

$result = [
    'php_files' => count($phpFiles),
    'symbols' => count($symbols),
    'duplicate_symbols' => $duplicates,
    'legacy_phase_module_files' => $legacy,
    'providers' => count($providers),
    'provider_errors' => $providerErrors,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

if ($duplicates !== [] || $legacy !== [] || $providerErrors !== []) {
    exit(1);
}

echo "ARCHITECTURE_AUDIT_OK\n";
