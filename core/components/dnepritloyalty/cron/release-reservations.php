<?php

define('MODX_API_MODE', true);

$basePath = getenv(
    'MODX_BASE_PATH'
);

if (!$basePath) {
    $basePath =
        dirname(__DIR__, 4) .
        DIRECTORY_SEPARATOR;
}

require_once
    rtrim($basePath, '/\\') .
    '/config.core.php';

require_once
    MODX_CORE_PATH .
    'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$corePath = $modx->getOption(
    'dnepritloyalty.core_path',
    null,
    $modx->getOption('core_path') .
    'components/dnepritloyalty/'
);

require_once $corePath .
    'model/dnepritloyalty/dnepritloyalty.class.php';

$loyalty = new DnepritLoyalty(
    $modx
);

$count =
    $loyalty->releaseExpiredReservations();

echo
    'Released reservations: ' .
    $count .
    PHP_EOL;
