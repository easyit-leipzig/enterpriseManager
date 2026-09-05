<?php
declare(strict_types=1);

use EasyIT\DataForm5\Help\HelpRegistry;
use EasyIT\DataForm5\Help\HelpService;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/system/help/HelpRegistry.php';
require_once $projectRoot . '/system/help/HelpService.php';

$registry = HelpRegistry::fromProjectRoot($projectRoot);
$service = new HelpService($projectRoot, $registry);

$requiredIds = [
    'dataform.designer',
    'dataformContext',
    'dataformContext.examples',
    'recordset.callbacks',
    'recordset.beforeSave',
    'recordset.afterSave',
    'recordset.beforeDelete',
    'recordset.afterDelete',
    'recordset.afterNavigate',
];

$failures = [];

foreach ($requiredIds as $helpId) {
    try {
        $help = $service->getHelp($helpId);
        $doc = $service->getDocument($helpId);

        if (!isset($help['title'])) {
            $failures[] = $helpId . ': title fehlt';
        }

        if (trim($doc['html']) === '') {
            $failures[] = $helpId . ': Dokument leer';
        }
    } catch (Throwable $e) {
        $failures[] = $helpId . ': ' . $e->getMessage();
    }
}

if ($failures !== []) {
    fwrite(STDERR, "FAIL\n" . implode("\n", $failures) . "\n");
    exit(1);
}

echo "PASS: DataForm5 dataformContext Help Package\n";
