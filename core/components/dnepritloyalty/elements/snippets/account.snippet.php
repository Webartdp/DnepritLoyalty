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

$account = $loyalty->getAccount(
    $userId
);

if (!$account) {
    return '';
}

$limit = max(
    1,
    min(
        100,
        (int)$modx->getOption(
            'limit',
            $scriptProperties,
            20
        )
    )
);

$query = $modx->newQuery(
    'DnepritLoyaltyTransaction'
);

$query->where([
    'user_id' => $userId,
]);

$query->sortby(
    'created_at',
    'DESC'
);

$query->limit(
    $limit
);

$transactions = [];

foreach (
    $modx->getCollection(
        'DnepritLoyaltyTransaction',
        $query
    ) as $transaction
) {
    $transactions[] =
        $transaction->toArray();
}

$levelTitle = '';

if (
    (int)$account->get('level_id') > 0 &&
    $level = $account->getOne('Level')
) {
    $levelTitle =
        (string)$level->get('title');
}

$data = [
    'balance' =>
        (float)$account->get('balance'),

    'reserved' =>
        (float)$account->get('reserved'),

    'available' =>
        $loyalty->getAvailableBalance(
            $userId
        ),

    'lifetime_total' =>
        (float)$account->get(
            'lifetime_total'
        ),

    'discount_type' =>
        (string)$account->get(
            'discount_type'
        ),

    'discount_value' =>
        (float)$account->get(
            'discount_value'
        ),

    'level_title' =>
        $levelTitle,

    'transactions' =>
        $transactions,
];

$tpl = $modx->getOption(
    'tpl',
    $scriptProperties,
    '',
    true
);

if ($tpl !== '') {
    $data['transactions_json'] =
        $modx->toJSON(
            $transactions
        );

    return $modx->getChunk(
        $tpl,
        $data
    );
}

$rows = '';

foreach ($transactions as $row) {
    $amount = (float)$row['amount'];

    $rows .=
        '<tr>' .
        '<td>' .
        htmlspecialchars(
            (string)$row['created_at'],
            ENT_QUOTES,
            'UTF-8'
        ) .
        '</td>' .
        '<td>' .
        htmlspecialchars(
            (string)$row['description'],
            ENT_QUOTES,
            'UTF-8'
        ) .
        '</td>' .
        '<td>' .
        (
            $amount > 0
                ? '+'
                : ''
        ) .
        number_format(
            $amount,
            2,
            '.',
            ' '
        ) .
        '</td>' .
        '</tr>';
}

return
    '<div class="dneprit-loyalty-account">' .
    '<div><strong>Бонуси:</strong> ' .
    number_format(
        $data['available'],
        2,
        '.',
        ' '
    ) .
    '</div>' .
    '<div><strong>Зарезервовано:</strong> ' .
    number_format(
        $data['reserved'],
        2,
        '.',
        ' '
    ) .
    '</div>' .
    '<div><strong>Покупки:</strong> ' .
    number_format(
        $data['lifetime_total'],
        2,
        '.',
        ' '
    ) .
    '</div>' .
    '<div><strong>Рівень:</strong> ' .
    htmlspecialchars(
        $levelTitle,
        ENT_QUOTES,
        'UTF-8'
    ) .
    '</div>' .
    '<div><strong>Постійна знижка:</strong> ' .
    number_format(
        $data['discount_value'],
        2,
        '.',
        ' '
    ) .
    (
        $data['discount_type'] ===
        'percent'
            ? '%'
            : ''
    ) .
    '</div>' .
    '<table class="dneprit-loyalty-history"><tbody>' .
    $rows .
    '</tbody></table>' .
    '</div>';
