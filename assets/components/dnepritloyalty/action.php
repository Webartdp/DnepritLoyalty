<?php

define('MODX_API_MODE', true);

require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$context = isset($_GET['ctx'])
    ? preg_replace('/[^a-zA-Z0-9_-]+/', '', (string)$_GET['ctx'])
    : 'web';

if ($context === '') {
    $context = 'web';
}

$modx = new modX();
$modx->initialize($context);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$reply = function ($success, array $data = [], $message = '') {
    echo json_encode([
        'success' => (bool)$success,
        'message' => (string)$message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

$corePath = $modx->getOption(
    'dnepritloyalty.core_path',
    null,
    $modx->getOption('core_path') . 'components/dnepritloyalty/'
);

require_once $corePath . 'model/dnepritloyalty/dnepritloyalty.class.php';

$loyalty = new DnepritLoyalty($modx);
$userId = $modx->user ? (int)$modx->user->id : 0;

if (
    $userId <= 0 ||
    !$loyalty->isUserAllowed($userId)
) {
    $reply(true, [
        'visible' => false,
    ]);
}

if (!$loyalty->loadMiniShop2()) {
    $reply(false, [], 'miniShop2 is not available.');
}

$miniShop2 = $modx->getService('miniShop2');

if (!$miniShop2) {
    $reply(false, [], 'miniShop2 service is not available.');
}

$miniShop2->initialize($context);
$status = $miniShop2->cart->status();
$cartCost = isset($status['total_cost'])
    ? max(0, (float)$status['total_cost'])
    : 0.0;

$discount = $loyalty->calculateLifetimeDiscount(
    $userId,
    $cartCost
);

$afterDiscount = max(
    0,
    round($cartCost - $discount, 2)
);

$available = $loyalty->getAvailableBalance($userId);
$maxPoints = $loyalty->getAllowedSpendPoints(
    $userId,
    $afterDiscount
);

$account = $loyalty->getAccount($userId);
$levelTitle = '';

if (
    $account &&
    (int)$account->get('level_id') > 0
) {
    $level = $account->getOne('Level');
    if ($level) {
        $levelTitle = (string)$level->get('title');
    }
}

$pointValue = max(
    0.000001,
    (float)$modx->getOption(
        'dnepritloyalty.point_value',
        null,
        1
    )
);

$maxSpendPercent = min(
    100,
    max(
        0,
        (float)$modx->getOption(
            'dnepritloyalty.max_spend_percent',
            null,
            30
        )
    )
);

$minSpendPoints = max(
    0,
    (float)$modx->getOption(
        'dnepritloyalty.min_spend_points',
        null,
        0
    )
);

$minOrderForSpend = max(
    0,
    (float)$modx->getOption(
        'dnepritloyalty.min_order_for_spend',
        null,
        0
    )
);

$spendEnabled = (bool)$modx->getOption(
    'dnepritloyalty.spend_enabled',
    null,
    true
);

$reply(true, [
    'visible' => true,
    'user_id' => $userId,
    'cart_cost' => round($cartCost, 2),
    'discount_amount' => round($discount, 2),
    'after_discount' => round($afterDiscount, 2),
    'available' => round($available, 2),
    'max_points' => round($maxPoints, 2),
    'point_value' => round($pointValue, 6),
    'max_spend_percent' => round($maxSpendPercent, 2),
    'min_spend_points' => round($minSpendPoints, 2),
    'min_order_for_spend' => round($minOrderForSpend, 2),
    'spend_enabled' => $spendEnabled,
    'can_spend' => $spendEnabled && $maxPoints > 0,
    'level_title' => $levelTitle,
    'discount_type' => $account
        ? (string)$account->get('discount_type')
        : 'percent',
    'discount_value' => $account
        ? round((float)$account->get('discount_value'), 2)
        : 0,
]);
