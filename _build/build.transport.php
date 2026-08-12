<?php

declare(strict_types=1);

require_once __DIR__ .
    '/config.php';

$configCore =
    DNEPRITLOYALTY_MODX_BASE_PATH .
    'config.core.php';

if (!is_file($configCore)) {
    fwrite(
        STDERR,
        "MODX config.core.php not found: {$configCore}\n"
    );

    exit(1);
}

require_once $configCore;

require_once
    MODX_CORE_PATH .
    'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');

$modx->setLogLevel(
    modX::LOG_LEVEL_INFO
);

$modx->setLogTarget(
    'ECHO'
);

$modx->getService(
    'error',
    'error.modError'
);

if (
    !$modx->loadClass(
        'transport.modPackageBuilder',
        '',
        false,
        true
    )
) {
    throw new RuntimeException(
        'Could not load MODX package builder.'
    );
}

$root =
    DNEPRITLOYALTY_BUILD_ROOT;

$core =
    $root .
    'core/components/dnepritloyalty/';

$assets =
    $root .
    'assets/components/dnepritloyalty/';

$model =
    $core .
    'model/';

$schema =
    $core .
    'schema/dnepritloyalty.mysql.schema.xml';

$required = [
    $schema,
    $core .
        'model/dnepritloyalty/dnepritloyalty.class.php',
    $core .
        'elements/plugins/dnepritloyalty.plugin.php',
    $core .
        'elements/snippets/balance.snippet.php',
    $core .
        'elements/snippets/account.snippet.php',
    $core .
        'elements/snippets/cart.snippet.php',
    $assets .
        'connector.php',
    __DIR__ .
        '/resolvers/resolve.tables.php',
    $root .
        'README.md',
    $root .
        'CHANGELOG.md',
    $root .
        'LICENSE',
];

foreach ($required as $file) {
    if (!is_file($file)) {
        throw new RuntimeException(
            'Required build source is missing: ' .
            $file
        );
    }
}

$manager = $modx->getManager();

$generator =
    $manager->getGenerator();

if (
    !$generator->parseSchema(
        $schema,
        $model
    )
) {
    throw new RuntimeException(
        'Unable to generate xPDO model.'
    );
}

/** @var modPackageBuilder $builder */
$builder =
    new modPackageBuilder(
        $modx
    );

$package =
    $builder->createPackage(
        'dnepritloyalty',
        DNEPRITLOYALTY_VERSION,
        DNEPRITLOYALTY_RELEASE
    );

if (
    !$package ||
    $builder->getSignature() !==
        DNEPRITLOYALTY_SIGNATURE
) {
    throw new RuntimeException(
        'Unexpected transport signature.'
    );
}

if (
    !$builder->registerNamespace(
        'dnepritloyalty',
        false,
        false,
        '{core_path}components/dnepritloyalty/',
        '{assets_path}components/dnepritloyalty/'
    )
) {
    throw new RuntimeException(
        'Could not register namespace.'
    );
}

$namespaceVehicle =
    $builder->createVehicle(
        $builder->namespace,
        [
            xPDOTransport::UNIQUE_KEY =>
                'name',

            xPDOTransport::PRESERVE_KEYS =>
                true,

            xPDOTransport::UPDATE_OBJECT =>
                true,

            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL =>
                true,
        ]
    );

if (
    !$namespaceVehicle->resolve(
        'file',
        [
            'source' =>
                $core,

            'target' =>
                "return MODX_CORE_PATH . 'components/';",
        ]
    )
) {
    throw new RuntimeException(
        'Could not add core resolver.'
    );
}

if (
    !$namespaceVehicle->resolve(
        'file',
        [
            'source' =>
                $assets,

            'target' =>
                "return MODX_ASSETS_PATH . 'components/';",
        ]
    )
) {
    throw new RuntimeException(
        'Could not add assets resolver.'
    );
}

if (
    !$namespaceVehicle->resolve(
        'php',
        [
            'source' =>
                __DIR__ .
                '/resolvers/resolve.tables.php',
        ]
    )
) {
    throw new RuntimeException(
        'Could not add tables resolver.'
    );
}

if (
    !$builder->putVehicle(
        $namespaceVehicle
    )
) {
    throw new RuntimeException(
        'Could not package namespace.'
    );
}

$menu =
    $modx->newObject(
        'modMenu'
    );

$menu->fromArray([
    'text' =>
        'dnepritloyalty',

    'parent' =>
        'components',

    'action' =>
        'index',

    'description' =>
        'dnepritloyalty_menu_desc',

    'namespace' =>
        'dnepritloyalty',

    'menuindex' =>
        0,
], '', true, true);

if (
    !$builder->putVehicle(
        $builder->createVehicle(
            $menu,
            [
                xPDOTransport::UNIQUE_KEY =>
                    'text',

                xPDOTransport::PRESERVE_KEYS =>
                    true,

                xPDOTransport::UPDATE_OBJECT =>
                    true,
            ]
        )
    )
) {
    throw new RuntimeException(
        'Could not package manager menu.'
    );
}

$snippets = [
    'DnepritLoyaltyBalance' =>
        'balance.snippet.php',

    'DnepritLoyaltyAccount' =>
        'account.snippet.php',

    'DnepritLoyaltyCart' =>
        'cart.snippet.php',
];

foreach (
    $snippets as
    $name => $source
) {
    $snippet =
        $modx->newObject(
            'modSnippet'
        );

    $snippet->fromArray([
        'name' =>
            $name,

        'description' =>
            'DnepritLoyalty public snippet.',

        'snippet' =>
            file_get_contents(
                $core .
                'elements/snippets/' .
                $source
            ),
    ], '', true, true);

    if (
        !$builder->putVehicle(
            $builder->createVehicle(
                $snippet,
                [
                    xPDOTransport::UNIQUE_KEY =>
                        'name',

                    xPDOTransport::PRESERVE_KEYS =>
                        true,

                    xPDOTransport::UPDATE_OBJECT =>
                        true,
                ]
            )
        )
    ) {
        throw new RuntimeException(
            'Could not package snippet ' .
            $name
        );
    }
}

$plugin =
    $modx->newObject(
        'modPlugin'
    );

$plugin->fromArray([
    'name' =>
        'DnepritLoyalty',

    'description' =>
        'miniShop2 loyalty and bonus wallet integration.',

    'plugincode' =>
        file_get_contents(
            $core .
            'elements/plugins/dnepritloyalty.plugin.php'
        ),
], '', true, true);

$events = [
    'OnUserSave',
    'msOnSubmitOrder',
    'msOnGetOrderCost',
    'msOnBeforeCreateOrder',
    'msOnCreateOrder',
    'msOnChangeOrderStatus',
    'OnDnepritNewsletterSubscribe',
];

foreach ($events as $eventName) {
    $event =
        $modx->newObject(
            'modPluginEvent'
        );

    $event->fromArray([
        'event' =>
            $eventName,

        'priority' =>
            0,

        'propertyset' =>
            0,
    ], '', true, true);

    $plugin->addMany(
        $event
    );
}

if (
    !$builder->putVehicle(
        $builder->createVehicle(
            $plugin,
            [
                xPDOTransport::UNIQUE_KEY =>
                    'name',

                xPDOTransport::PRESERVE_KEYS =>
                    true,

                xPDOTransport::UPDATE_OBJECT =>
                    true,

                xPDOTransport::RELATED_OBJECTS =>
                    true,

                xPDOTransport::RELATED_OBJECT_ATTRIBUTES =>
                    [
                        'PluginEvents' => [
                            xPDOTransport::UNIQUE_KEY =>
                                [
                                    'pluginid',
                                    'event',
                                ],

                            xPDOTransport::PRESERVE_KEYS =>
                                true,

                            xPDOTransport::UPDATE_OBJECT =>
                                false,
                        ],
                    ],
            ]
        )
    )
) {
    throw new RuntimeException(
        'Could not package plugin.'
    );
}

$settings = [
    'dnepritloyalty.enabled' =>
        [
            1,
            'combo-boolean',
        ],

    'dnepritloyalty.point_value' =>
        [
            1,
            'numberfield',
        ],

    'dnepritloyalty.order_reward_percent' =>
        [
            5,
            'numberfield',
        ],

    'dnepritloyalty.min_order_for_reward' =>
        [
            0,
            'numberfield',
        ],

    'dnepritloyalty.spend_enabled' =>
        [
            1,
            'combo-boolean',
        ],

    'dnepritloyalty.max_spend_percent' =>
        [
            30,
            'numberfield',
        ],

    'dnepritloyalty.min_spend_points' =>
        [
            100,
            'numberfield',
        ],

    'dnepritloyalty.min_order_for_spend' =>
        [
            0,
            'numberfield',
        ],

    'dnepritloyalty.discount_min_order' =>
        [
            0,
            'numberfield',
        ],

    'dnepritloyalty.lifetime_statuses' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.reward_statuses' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.cancel_statuses' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.lifetime_from' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.lifetime_to' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.allowed_groups' =>
        [
            '',
            'textfield',
        ],

    'dnepritloyalty.sort_order' =>
        [
            30,
            'numberfield',
        ],
];

foreach (
    $settings as
    $key => $data
) {
    $setting =
        $modx->newObject(
            'modSystemSetting'
        );

    $setting->fromArray([
        'key' =>
            $key,

        'value' =>
            $data[0],

        'xtype' =>
            $data[1],

        'namespace' =>
            'dnepritloyalty',

        'area' =>
            'dnepritloyalty_main',
    ], '', true, true);

    if (
        !$builder->putVehicle(
            $builder->createVehicle(
                $setting,
                [
                    xPDOTransport::UNIQUE_KEY =>
                        'key',

                    xPDOTransport::PRESERVE_KEYS =>
                        true,

                    xPDOTransport::UPDATE_OBJECT =>
                        true,
                ]
            )
        )
    ) {
        throw new RuntimeException(
            'Could not package setting ' .
            $key
        );
    }
}

$builder->setPackageAttributes([
    'license' =>
        file_get_contents(
            $root .
            'LICENSE'
        ),

    'readme' =>
        file_get_contents(
            $root .
            'README.md'
        ),

    'changelog' =>
        file_get_contents(
            $root .
            'CHANGELOG.md'
        ),

    'requires' => [
        'php' =>
            '>=7.4',
    ],
]);

if (!$builder->pack()) {
    throw new RuntimeException(
        'Could not pack transport archive.'
    );
}

$sourcePackage =
    $builder->directory .
    $builder->filename;

if (
    !is_file($sourcePackage) ||
    filesize($sourcePackage) === 0
) {
    throw new RuntimeException(
        'Transport archive was not created.'
    );
}

$dist =
    $root .
    '_dist/';

if (
    !is_dir($dist) &&
    !mkdir(
        $dist,
        0775,
        true
    ) &&
    !is_dir($dist)
) {
    throw new RuntimeException(
        'Could not create _dist.'
    );
}

$target =
    $dist .
    $builder->filename;

if (
    !copy(
        $sourcePackage,
        $target
    )
) {
    throw new RuntimeException(
        'Could not copy transport package.'
    );
}

$checksum =
    hash_file(
        'sha256',
        $target
    );

if ($checksum === false) {
    throw new RuntimeException(
        'Could not calculate checksum.'
    );
}

file_put_contents(
    $target .
    '.sha256',
    $checksum .
    '  ' .
    basename($target) .
    PHP_EOL
);

file_put_contents(
    $dist .
    'release.json',
    json_encode(
        [
            'name' =>
                'dnepritloyalty',

            'version' =>
                DNEPRITLOYALTY_VERSION,

            'release' =>
                DNEPRITLOYALTY_RELEASE,

            'signature' =>
                $builder->getSignature(),

            'filename' =>
                basename($target),

            'sha256' =>
                $checksum,

            'built_at' =>
                gmdate('c'),
        ],
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_SLASHES
    ) .
    PHP_EOL
);

$modx->log(
    modX::LOG_LEVEL_INFO,
    'DnepritLoyalty package built successfully: ' .
    $target
);

$modx->log(
    modX::LOG_LEVEL_INFO,
    'SHA-256: ' .
    $checksum
);
