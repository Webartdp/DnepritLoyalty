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
    $modx->getOption('core_path') .
    'components/dnepritloyalty/'
);

require_once $corePath .
    'model/dnepritloyalty/dnepritloyalty.class.php';

$loyalty = new DnepritLoyalty(
    $modx
);

$userId = (int)$modx->user->id;

if (
    !$loyalty->isEnabled() ||
    !$loyalty->isUserAllowed($userId) ||
    !$loyalty->loadMiniShop2()
) {
    return '';
}

$miniShop2 = $modx->getService(
    'miniShop2'
);

$miniShop2->initialize(
    $modx->context->key
);

$status = $miniShop2->cart->status();

$cartCost = isset(
    $status['total_cost']
)
    ? (float)$status['total_cost']
    : 0;

$discount =
    $loyalty->calculateLifetimeDiscount(
        $userId,
        $cartCost
    );

$afterDiscount = max(
    0,
    $cartCost - $discount
);

$maxPoints =
    $loyalty->getAllowedSpendPoints(
        $userId,
        $afterDiscount
    );

$available =
    $loyalty->getAvailableBalance(
        $userId
    );

$account =
    $loyalty->getAccount(
        $userId
    );

$data = [
    'available' =>
        $available,

    'max_points' =>
        $maxPoints,

    'discount_amount' =>
        $discount,

    'discount_value' =>
        $account
            ? (float)$account->get(
                'discount_value'
            )
            : 0,

    'discount_type' =>
        $account
            ? (string)$account->get(
                'discount_type'
            )
            : 'percent',

    'cart_cost' =>
        $cartCost,
];

$tpl = $modx->getOption(
    'tpl',
    $scriptProperties,
    '',
    true
);

if ($tpl !== '') {
    return $modx->getChunk(
        $tpl,
        $data
    );
}

$html =
    '<div class="dneprit-loyalty-checkout">';

$html .=
    '<div>Бонусний баланс: <strong>' .
    number_format(
        $available,
        2,
        '.',
        ' '
    ) .
    '</strong></div>';

if ($discount > 0) {
    $html .=
        '<div>Постійна знижка: <strong>-' .
        number_format(
            $discount,
            2,
            '.',
            ' '
        ) .
        '</strong></div>';
}

if ($maxPoints > 0) {
    $html .=
        '<label>Використати бонуси' .
        '<input type="number" ' .
        'name="dneprit_loyalty_points" ' .
        'min="0" ' .
        'max="' .
        htmlspecialchars(
            (string)$maxPoints,
            ENT_QUOTES,
            'UTF-8'
        ) .
        '" ' .
        'step="0.01" ' .
        'value="0">' .
        '</label>' .
        '<small>Максимум у цьому замовленні: ' .
        number_format(
            $maxPoints,
            2,
            '.',
            ' '
        ) .
        '</small>';
}

$html .= '</div>';

return $html;
