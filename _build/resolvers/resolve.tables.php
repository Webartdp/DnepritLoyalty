<?php

/** @var xPDOObject $object */
/** @var array $options */

if (
    !isset($object) ||
    !($object instanceof xPDOObject) ||
    !$object->xpdo
) {
    return false;
}

/** @var modX $modx */
$modx = $object->xpdo;

$action = isset(
    $options[
        xPDOTransport::PACKAGE_ACTION
    ]
)
    ? (int)$options[
        xPDOTransport::PACKAGE_ACTION
    ]
    : xPDOTransport::ACTION_INSTALL;

if (
    $action ===
    xPDOTransport::ACTION_UNINSTALL
) {
    return true;
}

$modelPath =
    MODX_CORE_PATH .
    'components/dnepritloyalty/model/';

if (
    !$modx->addPackage(
        'dnepritloyalty',
        $modelPath,
        $modx->config['table_prefix']
    )
) {
    $modx->log(
        modX::LOG_LEVEL_ERROR,
        '[DnepritLoyalty] Could not load installed xPDO package.'
    );

    return false;
}

$manager = $modx->getManager();

$classes = [
    'DnepritLoyaltyLevel',
    'DnepritLoyaltyAccount',
    'DnepritLoyaltyTransaction',
    'DnepritLoyaltyRule',
    'DnepritLoyaltyOrderReward',
];

foreach ($classes as $class) {
    $tableName = trim(
        $modx->getTableName($class),
        '`'
    );

    $statement = $modx->prepare(
        'SELECT COUNT(*) ' .
        'FROM information_schema.tables ' .
        'WHERE table_schema = DATABASE() ' .
        'AND table_name = ?'
    );

    if (
        !$statement ||
        !$statement->execute(
            [$tableName]
        )
    ) {
        $modx->log(
            modX::LOG_LEVEL_ERROR,
            '[DnepritLoyalty] Could not inspect table for ' .
            $class
        );

        return false;
    }

    $exists =
        (int)$statement->fetchColumn() >
        0;

    if (
        !$exists &&
        !$manager->createObjectContainer(
            $class
        )
    ) {
        $modx->log(
            modX::LOG_LEVEL_ERROR,
            '[DnepritLoyalty] Could not create table for ' .
            $class
        );

        return false;
    }
}

$defaults = [
    'registration' => [
        'Реєстрація',
        0,
        1,
        'Одноразовий бонус після створення облікового запису',
    ],

    'first_order' => [
        'Перше оплачене замовлення',
        0,
        1,
        'Одноразовий бонус після першого замовлення у reward-статусі',
    ],

    'newsletter' => [
        'Підписка на розсилку',
        0,
        1,
        'Використовує OnDnepritNewsletterSubscribe',
    ],

    'review' => [
        'Відгук',
        0,
        1,
        'Підготовлено для інтеграції з модулем відгуків',
    ],
];

foreach (
    $defaults as
    $key => $row
) {
    $existing = $modx->getObject(
        'DnepritLoyaltyRule',
        [
            'key' =>
                $key,
        ]
    );

    if ($existing) {
        continue;
    }

    $rule = $modx->newObject(
        'DnepritLoyaltyRule'
    );

    $rule->fromArray([
        'key' =>
            $key,

        'title' =>
            $row[0],

        'enabled' =>
            0,

        'amount' =>
            $row[1],

        'once_only' =>
            $row[2],

        'description' =>
            $row[3],
    ], '', true, true);

    if (!$rule->save()) {
        $modx->log(
            modX::LOG_LEVEL_ERROR,
            '[DnepritLoyalty] Could not create default rule ' .
            $key
        );

        return false;
    }
}

return true;
