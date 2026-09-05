<?php
declare(strict_types=1);

use EasyIT\DataForm5\Help\HelpRegistry;
use EasyIT\DataForm5\Help\HelpService;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/system/help/HelpRegistry.php';
require_once $projectRoot . '/system/help/HelpService.php';

$failures = [];

try {
    $registry = HelpRegistry::fromProjectRoot($projectRoot);

    if (!$registry->has('dataform.layout')) {
        $failures[] = 'Help-ID dataform.layout fehlt.';
    }

    $entry = $registry->get('dataform.layout');

    foreach (['helpFile', 'document', 'anchor'] as $key) {
        if (!isset($entry[$key])) {
            $failures[] = 'Registry-Feld fehlt: ' . $key;
        }
    }

    $helpFile = $projectRoot . '/' . $entry['helpFile'];
    $docFile = $projectRoot . '/' . $entry['document'];

    if (!is_file($helpFile)) {
        $failures[] = 'Hilfedatei fehlt: ' . $helpFile;
    }

    if (!is_file($docFile)) {
        $failures[] = 'HTML-Dokument fehlt: ' . $docFile;
    }

    if (is_file($helpFile)) {
        $json = json_decode(
            file_get_contents($helpFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (($json['id'] ?? null) !== 'dataform.layout') {
            $failures[] = 'Falsche Help-ID in layout.json.';
        }
    }

    if (is_file($docFile)) {
        $html = file_get_contents($docFile);

        if (
            !str_contains(
                $html,
                'id="' . $entry['anchor'] . '"'
            )
        ) {
            $failures[] = 'HTML-Anker fehlt: ' . $entry['anchor'];
        }
    }
} catch (Throwable $e) {
    $failures[] = $e->getMessage();
}

if ($failures !== []) {
    fwrite(
        STDERR,
        "FAIL\n" . implode("\n", $failures) . "\n"
    );
    exit(1);
}

echo "PASS: DataForm5 Layout Help Package\n";
