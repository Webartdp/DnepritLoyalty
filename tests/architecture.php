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
    'core/components/dnepritloyalty/processors/accounts/sync.class.php',
    'assets/components/dnepritloyalty/connector.php',
    'assets/components/dnepritloyalty/js/mgr/widgets/accounts.grid.js',
    'assets/components/dnepritloyalty/js/mgr/widgets/settings.panel.js',
    'assets/components/dnepritloyalty/css/mgr.css',
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

$syncProcessor = file_get_contents(
    $root .
    '/core/components/dnepritloyalty/processors/accounts/sync.class.php'
);

foreach (
    [
        'dnepritloyalty.allowed_groups',
        'modUserGroupMember',
        'getAccount(',
        'PDO::FETCH_COLUMN',
    ] as $needle
) {
    if (
        strpos(
            $syncProcessor,
            $needle
        ) === false
    ) {
        fwrite(
            STDERR,
            'Office customer sync regression check failed: ' .
            $needle .
            PHP_EOL
        );

        exit(1);
    }
}

$accountsGrid = file_get_contents(
    $root .
    '/assets/components/dnepritloyalty/js/mgr/widgets/accounts.grid.js'
);

foreach (
    [
        'syncOfficeCustomers',
        "action: 'accounts/sync'",
        'dnepritloyalty_sync_office_customers',
    ] as $needle
) {
    if (
        strpos(
            $accountsGrid,
            $needle
        ) === false
    ) {
        fwrite(
            STDERR,
            'Accounts sync UI regression check failed: ' .
            $needle .
            PHP_EOL
        );

        exit(1);
    }
}

$settingsPanel = file_get_contents(
    $root .
    '/assets/components/dnepritloyalty/js/mgr/widgets/settings.panel.js'
);

foreach (
    [
        'MODx.FormPanel',
        "action: 'settings/save'",
        'this.loadSettings()',
        'primary-button',
        'dnepritloyalty-settings-fieldset',
        'formWidth',
        'autoScroll: true',
    ] as $needle
) {
    if (
        strpos(
            $settingsPanel,
            $needle
        ) === false
    ) {
        fwrite(
            STDERR,
            'Settings UI regression check failed: ' .
            $needle .
            PHP_EOL
        );

        exit(1);
    }
}

if (
    strpos(
        $settingsPanel,
        "xtype: 'form'"
    ) !== false
) {
    fwrite(
        STDERR,
        "Settings panel must not use a zero-height nested form.\n"
    );

    exit(1);
}

$managerCss = file_get_contents(
    $root .
    '/assets/components/dnepritloyalty/css/mgr.css'
);

foreach (
    [
        '.dnepritloyalty-settings-panel .x-form-item',
        '.dnepritloyalty-settings-fieldset',
        '.dnepritloyalty-settings-toolbar',
    ] as $selector
) {
    if (
        strpos(
            $managerCss,
            $selector
        ) === false
    ) {
        fwrite(
            STDERR,
            'Missing settings UI style: ' .
            $selector .
            PHP_EOL
        );

        exit(1);
    }
}

echo
    "Architecture checks passed.\n";
