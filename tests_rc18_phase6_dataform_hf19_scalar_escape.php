<?php
declare(strict_types=1);

$root = __DIR__;
$runtime = (string)file_get_contents($root . '/products/dataform/runtime.php');
$checks = [];
$check = function(string $name, bool $ok) use (&$checks): void {
    $checks[] = ['check' => $name, 'status' => $ok ? 'PASS' : 'FAIL'];
};

$check(
    'HF19 marker',
    str_contains($runtime, 'HF36 DATAFORM RUNTIME ACTIVE')
);
$check(
    'numeric width key cast before escape',
    str_contains($runtime, 'e((string)$value)')
);
$check(
    'numeric width key cast before strict comparison',
    str_contains($runtime, "===(string)\$value?'selected':''")
);
$check(
    'width label explicitly cast',
    str_contains($runtime, 'e((string)$label)')
);
$check(
    'render boundary retained',
    str_contains($runtime, 'catch (Throwable $renderThrowable)')
);
$check(
    'HF19 response header',
    str_contains($runtime, 'X-EasyIT-DataForm-Runtime: HF36')
);

$failed = count(array_filter(
    $checks,
    static fn(array $row): bool => $row['status'] === 'FAIL'
));

echo json_encode([
    'release' => 'RC1.8',
    'hotfix' => 'HF19',
    'status' => $failed === 0 ? 'PASS' : 'FAIL',
    'checks' => $checks,
    'summary' => ['checks' => count($checks), 'failed' => $failed],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($failed === 0 ? 0 : 1);
