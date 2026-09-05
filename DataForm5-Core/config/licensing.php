<?php
declare(strict_types=1);
return [
    'mode' => getenv('LICENSE_MODE') ?: 'community',
    'allow_community' => filter_var(getenv('LICENSE_ALLOW_COMMUNITY') ?: '1', FILTER_VALIDATE_BOOL),
    'license' => [
        'id' => getenv('LICENSE_ID') ?: 'local-community',
        'holder' => getenv('LICENSE_HOLDER') ?: 'Local installation',
        'edition' => getenv('LICENSE_EDITION') ?: 'community',
        'capabilities' => ['core.*'],
        'products' => ['*'],
        'valid_from' => null,
        'expires_at' => null,
        'grace_days' => 0,
        'enabled' => true,
        'metadata' => [],
    ],
];
