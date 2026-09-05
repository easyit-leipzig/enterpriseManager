<?php
declare(strict_types=1);

$root = __DIR__;
$required = [
    'VERSION',
    'system/app/bootstrap.php',
    'system/ui/layout.php',
    'app/dashboard.php',
    'app/licensing/index.php',
    'installer/schema/admin/004_licensing.php',
    'DataForm5-Core/bootstrap/app.php',
    'DataForm5-Core/system/licensing/Core/LicenseRegistry.php',
    'DataForm5-Core/system/licensing/Core/PdoLicenseProvider.php',
    'products/dataform/index.php',
    'products/dataform/records.php',
    'products/dataform/relations.php',
    'products/dataform/workflow.php',
    'docs/MASTER_WORKING_STATE.md',
];

$missing = [];
foreach ($required as $relative) {
    if (!is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $missing[] = $relative;
    }
}

$version = trim((string)@file_get_contents($root . '/VERSION'));
if (!preg_match('/^(?:RC1\.(?:7|8)\.[0-9]+-dev-(?:master|phase[A-Z0-9]+)|RC1\.8-FC1-HF[0-9]+)$/', $version)) {
    $missing[] = 'VERSION=' . $version;
}

if ($missing !== []) {
    fwrite(STDERR, "MASTER_STATE_FAIL\n" . implode("\n", $missing) . "\n");
    exit(1);
}

echo "MASTER_STATE_OK\n";
