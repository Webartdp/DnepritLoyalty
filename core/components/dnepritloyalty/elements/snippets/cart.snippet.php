<?php

if (
    !$modx->user ||
    !(int)$modx->user->id
) {
    return '';
}

$corePath = $modx->getOption(
    'dnepritloyalty.core_path',
    null,
    $modx->getOption('core_path') . 'components/dnepritloyalty/'
);

$assetsUrl = $modx->getOption(
    'dnepritloyalty.assets_url',
    null,
    $modx->getOption('assets_url') . 'components/dnepritloyalty/'
);

require_once $corePath . 'model/dnepritloyalty/dnepritloyalty.class.php';

$loyalty = new DnepritLoyalty($modx);
$userId = (int)$modx->user->id;

if (
    !$loyalty->isUserAllowed($userId) ||
    !$loyalty->loadMiniShop2()
) {
    return '';
}

$miniShop2 = $modx->getService('miniShop2');

if (!$miniShop2) {
    return '';
}

$miniShop2->initialize($modx->context->key);
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

$isRu = strtolower((string)$modx->getOption('cultureKey')) === 'ru';

$labels = $isRu
    ? [
        'base' => 'Бонусная программа',
        'balance' => 'Баланс',
        'points' => 'бонусов',
        'cart' => 'Товары',
        'discount' => 'Постоянная скидка',
        'bonuses' => 'Бонусами',
        'total' => 'К оплате',
        'spend' => 'Списать бонусы',
        'max_button' => 'Максимум',
        'clear_button' => 'Не списывать',
        'empty' => 'Добавьте товары в корзину, чтобы использовать бонусы.',
        'disabled' => 'Списание бонусов сейчас отключено.',
        'min_points' => 'Для списания нужно минимум бонусов:',
        'min_order' => 'Минимальная сумма заказа для списания:',
        'unavailable' => 'В этом заказе бонусы пока нельзя списать.',
        'max' => 'Максимум для этого заказа:',
        'error' => 'Не удалось обновить бонусный баланс.',
    ]
    : [
        'base' => 'Бонусна програма',
        'balance' => 'Баланс',
        'points' => 'бонусів',
        'cart' => 'Товари',
        'discount' => 'Постійна знижка',
        'bonuses' => 'Бонусами',
        'total' => 'До сплати',
        'spend' => 'Списати бонуси',
        'max_button' => 'Максимум',
        'clear_button' => 'Не списувати',
        'empty' => 'Додайте товари до кошика, щоб використати бонуси.',
        'disabled' => 'Списання бонусів зараз вимкнено.',
        'min_points' => 'Для списання потрібно мінімум бонусів:',
        'min_order' => 'Мінімальна сума замовлення для списання:',
        'unavailable' => 'У цьому замовленні бонуси поки не можна списати.',
        'max' => 'Максимум для цього замовлення:',
        'error' => 'Не вдалося оновити бонусний баланс.',
    ];

$modx->regClientCSS(
    $assetsUrl . 'css/web.css?v=0.1.0-beta19'
);
$modx->regClientScript(
    $assetsUrl . 'js/web/cart.js?v=0.1.0-beta19'
);

$endpoint = $assetsUrl . 'action.php?ctx=' . rawurlencode($modx->context->key);

$e = function ($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$fmt = function ($value) {
    return number_format(
        (float)$value,
        2,
        '.',
        ' '
    );
};

$levelLabel = $levelTitle !== ''
    ? $levelTitle
    : $labels['base'];

$html = '<div class="dneprit-loyalty-checkout"'
    . ' data-dneprit-loyalty-cart'
    . ' data-user-id="' . $userId . '"'
    . ' data-endpoint="' . $e($endpoint) . '"'
    . ' data-label-base="' . $e($labels['base']) . '"'
    . ' data-points-word="' . $e($labels['points']) . '"'
    . ' data-msg-disabled="' . $e($labels['disabled']) . '"'
    . ' data-msg-empty="' . $e($labels['empty']) . '"'
    . ' data-msg-min-points="' . $e($labels['min_points']) . '"'
    . ' data-msg-min-order="' . $e($labels['min_order']) . '"'
    . ' data-msg-unavailable="' . $e($labels['unavailable']) . '"'
    . ' data-msg-max="' . $e($labels['max']) . '"'
    . ' data-msg-error="' . $e($labels['error']) . '">';

$html .= '<div class="dneprit-loyalty-head">'
    . '<div class="dneprit-loyalty-title">'
    . '<span class="dneprit-loyalty-kicker">' . $e($labels['base']) . '</span>'
    . '<strong data-loyalty-role="level">' . $e($levelLabel) . '</strong>'
    . '</div>'
    . '<div class="dneprit-loyalty-balance">'
    . '<span>' . $e($labels['balance']) . '</span>'
    . '<strong><span data-loyalty-role="available">' . $fmt($available) . '</span> ' . $e($labels['points']) . '</strong>'
    . '</div>'
    . '</div>';

$html .= '<div class="dneprit-loyalty-empty" data-loyalty-role="empty"'
    . ($cartCost > 0 ? ' hidden' : '') . '>'
    . $e($labels['empty'])
    . '</div>';

$html .= '<div class="dneprit-loyalty-summary" data-loyalty-role="summary"'
    . ($cartCost <= 0 ? ' hidden' : '') . '>'
    . '<div><span>' . $e($labels['cart']) . '</span><strong><span data-loyalty-role="cart-cost">' . $fmt($cartCost) . '</span> грн</strong></div>'
    . '<div data-loyalty-role="discount-row"' . ($discount <= 0 ? ' hidden' : '') . '><span>' . $e($labels['discount']) . '</span><strong>-<span data-loyalty-role="discount">' . $fmt($discount) . '</span> грн</strong></div>'
    . '<div data-loyalty-role="bonus-row" hidden><span>' . $e($labels['bonuses']) . '</span><strong>-<span data-loyalty-role="selected-money">0</span> грн</strong></div>'
    . '<div class="dneprit-loyalty-total"><span>' . $e($labels['total']) . '</span><strong><span data-loyalty-role="final-cost">' . $fmt($afterDiscount) . '</span> грн</strong></div>'
    . '</div>';

$html .= '<div class="dneprit-loyalty-spend" data-loyalty-role="spend-wrap"'
    . ($cartCost <= 0 ? ' hidden' : '') . '>'
    . '<div class="dneprit-loyalty-spend-row">'
    . '<label>' . $e($labels['spend']) . '</label>'
    . '<div class="dneprit-loyalty-number">'
    . '<input type="number" min="0" max="' . $e($maxPoints) . '" step="0.01" value="0" data-loyalty-input="number" inputmode="decimal">'
    . '<span>' . $e($labels['points']) . '</span>'
    . '</div>'
    . '</div>'
    . '<input class="dneprit-loyalty-range" type="range" min="0" max="' . $e($maxPoints) . '" step="0.01" value="0" data-loyalty-input="range">'
    . '<div class="dneprit-loyalty-actions">'
    . '<button type="button" data-loyalty-action="max">' . $e($labels['max_button']) . '</button>'
    . '<button type="button" data-loyalty-action="clear">' . $e($labels['clear_button']) . '</button>'
    . '</div>'
    . '<small data-loyalty-role="notice">' . $e($labels['max']) . ' <span data-loyalty-role="max-points">' . $fmt($maxPoints) . '</span> ' . $e($labels['points']) . '</small>'
    . '</div>';

$html .= '</div>';

return $html;
