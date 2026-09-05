<?php
declare(strict_types=1);
$root = __DIR__;
$checks = [];
function hf68(bool $ok, string $label): void { echo ($ok ? "PASS" : "FAIL") . " - $label\n"; if (!$ok) $GLOBALS['failed'] = true; }
$layout = file_get_contents($root . '/system/ui/layout.php');
$runtime = file_get_contents($root . '/products/dataform/system/WorkspaceLayout.php');
$css = file_get_contents($root . '/products/dataform/assets/branding/dataform-branding.css');
$enterpriseCss = file_get_contents($root . '/assets/css/enterprise.css');
hf68(str_contains($layout, 'assets/img/easyit-epManager-logo.png'), 'Enterprise layout references existing manager logo');
hf68(str_contains($layout, '$hasEnterpriseManagerLogo'), 'Enterprise layout has file-existence fallback');
hf68(str_contains($runtime, "assets/img/easyit-epManager-logo.png"), 'DataForm runtime references manager logo');
hf68(str_contains($runtime, '$hasEnterpriseManagerLogo'), 'DataForm runtime has file-existence fallback');
hf68(str_contains($css, 'justify-content:flex-start'), 'DataForm branding is left aligned');
hf68(str_contains($enterpriseCss, '.enterprise-manager-brand__logo'), 'Enterprise logo CSS exists');
hf68(str_contains($layout, 'data-dataform-branding="HF68"'), 'Global DataForm branding marks HF68');
hf68(str_contains($runtime, 'data-dataform-branding="HF68"'), 'Runtime DataForm branding marks HF68');
exit(!empty($GLOBALS['failed']) ? 1 : 0);
