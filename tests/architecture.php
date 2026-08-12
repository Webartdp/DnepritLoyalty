<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$required = [
    'core/components/dnepritloyalty/schema/dnepritloyalty.mysql.schema.xml',
    'core/components/dnepritloyalty/model/dnepritloyalty/dnepritloyalty.class.php',
    'core/components/dnepritloyalty/elements/plugins/dnepritloyalty.plugin.php',
    'core/components/dnepritloyalty/elements/snippets/balance.snippet.php',
    'core/components/dnepritloyalty/elements/snippets/account.snippet.php',
    'core/components/dnepritloyalty/elements/snippets/cart.snippet.php',
    'assets/components/dnepritloyalty/connector.php',
    '_build/build.transport.php',
    '_build/resolvers/resolve.tables.php',
];

foreach ($required as $file) {
    if (
        !is_file(
            $root .
            '/' .
            $file
        )
    ) {
        fwrite(
            STDERR,
            'Missing required file: ' .
            $file .
            PHP_EOL
        );

        exit(1);
    }
}

$plugin = file_get_contents(
    $root .
    '/core/components/dnepritloyalty/elements/plugins/dnepritloyalty.plugin.php'
);

foreach (
    [
        'msOnSubmitOrder',
        'msOnGetOrderCost',
        'msOnBeforeCreateOrder',
        'msOnCreateOrder',
        'msOnChangeOrderStatus',
    ] as $event
) {
    if (
        strpos(
            $plugin,
            "case '{$event}'"
        ) === false
    ) {
        fwrite(
            STDERR,
            'Missing miniShop2 event handler: ' .
            $event .
            PHP_EOL
        );

        exit(1);
    }
}

$service = file_get_contents(
    $root .
    '/core/components/dnepritloyalty/model/dnepritloyalty/dnepritloyalty.class.php'
);

foreach (
    [
        'addTransaction',
        'reservePoints',
        'renameReservation',
        'finalizeReservation',
        'releaseReservation',
        'recalculateLifetime',
        'calculateLifetimeDiscount',
        'getAllowedSpendPoints',
        'processOrderStatus',
    ] as $method
) {
    if (
        strpos(
            $service,
            'function ' .
            $method .
            '('
        ) === false
    ) {
        fwrite(
            STDERR,
            'Missing service method: ' .
            $method .
            PHP_EOL
        );

        exit(1);
    }
}

if (
    strpos(
        $service,
        'FOR UPDATE'
    ) === false
) {
    fwrite(
        STDERR,
        "Account mutations are not row-locked.\n"
    );

    exit(1);
}

echo
    "Architecture checks passed.\n";
