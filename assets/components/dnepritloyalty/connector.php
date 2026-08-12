<?php

require_once
    dirname(__DIR__, 3) .
    '/config.core.php';

require_once
    MODX_CORE_PATH .
    'config/' .
    MODX_CONFIG_KEY .
    '.inc.php';

require_once
    MODX_CONNECTORS_PATH .
    'index.php';

$corePath = $modx->getOption(
    'dnepritloyalty.core_path',
    null,
    $modx->getOption('core_path') .
    'components/dnepritloyalty/'
);

require_once $corePath .
    'model/dnepritloyalty/dnepritloyalty.class.php';

$service = new DnepritLoyalty(
    $modx
);

$modx->request->handleRequest([
    'processors_path' =>
        $service->config[
            'processorsPath'
        ],

    'location' =>
        '',
]);
