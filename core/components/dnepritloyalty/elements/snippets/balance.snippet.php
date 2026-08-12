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

$account = $loyalty->getAccount(
    (int)$modx->user->id
);

if (!$account) {
    return '';
}

$data = [
    'balance' =>
        number_format(
            (float)$account->get('balance'),
            2,
            '.',
            ''
        ),

    'reserved' =>
        number_format(
            (float)$account->get('reserved'),
            2,
            '.',
            ''
        ),

    'available' =>
        number_format(
            $loyalty->getAvailableBalance(
                (int)$modx->user->id
            ),
            2,
            '.',
            ''
        ),

    'lifetime_total' =>
        number_format(
            (float)$account->get(
                'lifetime_total'
            ),
            2,
            '.',
            ''
        ),

    'discount_value' =>
        number_format(
            (float)$account->get(
                'discount_value'
            ),
            2,
            '.',
            ''
        ),

    'discount_type' =>
        (string)$account->get(
            'discount_type'
        ),
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

return $data['available'];
