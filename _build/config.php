<?php

declare(strict_types=1);

$basePath = getenv('MODX_BASE_PATH');

if (!$basePath) {
    $basePath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
}

define('DNEPRITLOYALTY_BUILD_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('DNEPRITLOYALTY_MODX_BASE_PATH', rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR);
define('DNEPRITLOYALTY_VERSION', '0.1.0');
define('DNEPRITLOYALTY_RELEASE', 'beta4');
define('DNEPRITLOYALTY_SIGNATURE', 'dnepritloyalty-' . DNEPRITLOYALTY_VERSION . '-' . DNEPRITLOYALTY_RELEASE);
