<?php

$corePath = $modx->getOption(
    'dnepritloyalty.core_path',
    null,
    $modx->getOption('core_path') . 'components/dnepritloyalty/'
);

require_once $corePath . 'model/dnepritloyalty/dnepritloyalty.class.php';

/* DnepritLoyalty is permanently enabled. */
$modx->setOption('dnepritloyalty.enabled', true);

$loyalty = new DnepritLoyalty($modx);

switch ($modx->event->name) {
    case 'OnUserSave':
        if (
            !empty($mode) &&
            $mode === modSystemEvent::MODE_NEW &&
            !empty($user) &&
            $user instanceof modUser
        ) {
            $loyalty->awardRule(
                (int)$user->get('id'),
                'registration'
            );
        }
        break;

    case 'OnDnepritNewsletterSubscribe':
        $userId = isset($user_id)
            ? (int)$user_id
            : 0;

        if ($userId > 0) {
            $loyalty->awardRule(
                $userId,
                'newsletter'
            );
        }
        break;

    case 'msOnSubmitOrder':
        if (
            !$modx->user ||
            !(int)$modx->user->id ||
            !$loyalty->isUserAllowed(
                (int)$modx->user->id
            )
        ) {
            unset(
                $_SESSION[
                    'dnepritloyalty_checkout'
                ]
            );
            break;
        }

        $requested = 0.0;

        if (
            isset(
                $data[
                    'dneprit_loyalty_points'
                ]
            )
        ) {
            $requested = max(
                0,
                (float)$data[
                    'dneprit_loyalty_points'
                ]
            );
        } elseif (
            isset(
                $_POST[
                    'dneprit_loyalty_points'
                ]
            )
        ) {
            $requested = max(
                0,
                (float)$_POST[
                    'dneprit_loyalty_points'
                ]
            );
        }

        $_SESSION[
            'dnepritloyalty_checkout'
        ] = [
            'user_id' =>
                (int)$modx->user->id,

            'requested_points' =>
                $requested,

            'reservation_key' =>
                '',

            'discount_amount' =>
                0,

            'effective_points' =>
                0,
        ];
        break;

    case 'msOnGetOrderCost':
        /*
         * miniShop2 also calls getCost(false, true) for delivery-only cost.
         * Loyalty affects product cost only and must not reset checkout state
         * during that delivery-only calculation.
         */
        if (
            isset($with_cart) &&
            !$with_cart
        ) {
            break;
        }

        $userId = $modx->user
            ? (int)$modx->user->id
            : 0;

        if (
            $userId <= 0 ||
            !$loyalty->isUserAllowed(
                $userId
            )
        ) {
            break;
        }

        $values =
            &$modx->event->returnedValues;

        $baseCost = max(
            0,
            (float)$cost
        );

        $discount =
            $loyalty->calculateLifetimeDiscount(
                $userId,
                $baseCost
            );

        $afterDiscount = max(
            0,
            round(
                $baseCost -
                $discount,
                2
            )
        );

        $requested = 0.0;

        if (
            !empty(
                $_SESSION[
                    'dnepritloyalty_checkout'
                ]
            ) &&
            (int)$_SESSION[
                'dnepritloyalty_checkout'
            ]['user_id'] === $userId
        ) {
            $requested = max(
                0,
                (float)$_SESSION[
                    'dnepritloyalty_checkout'
                ]['requested_points']
            );
        }

        $allowed =
            $loyalty->getAllowedSpendPoints(
                $userId,
                $afterDiscount
            );

        $effective = min(
            $requested,
            $allowed
        );

        $spendMoney = min(
            $afterDiscount,
            $loyalty->pointsToMoney(
                $effective
            )
        );

        $finalCost = max(
            0,
            round(
                $afterDiscount -
                $spendMoney,
                2
            )
        );

        $values['cost'] =
            $finalCost;

        $_SESSION[
            'dnepritloyalty_checkout'
        ] = [
            'user_id' =>
                $userId,

            'requested_points' =>
                $requested,

            'reservation_key' =>
                $_SESSION[
                    'dnepritloyalty_checkout'
                ]['reservation_key'] ??
                '',

            'base_cost' =>
                $baseCost,

            'discount_amount' =>
                $discount,

            'effective_points' =>
                $effective,

            'spend_money' =>
                $spendMoney,

            'final_cost' =>
                $finalCost,
        ];
        break;

    case 'msOnBeforeCreateOrder':
        if (
            empty($msOrder) ||
            !$msOrder instanceof msOrder
        ) {
            break;
        }

        $userId = (int)$msOrder->get(
            'user_id'
        );

        $checkout =
            $_SESSION[
                'dnepritloyalty_checkout'
            ] ??
            [];

        if (
            $userId <= 0 ||
            (int)($checkout['user_id'] ?? 0) !==
                $userId
        ) {
            break;
        }

        /*
         * Persist the loyalty result into the real miniShop2 order.
         * Delivery is free on this shop, so cost equals the product total
         * after Lifetime Discount and bonus spending.
         */
        if (
            array_key_exists(
                'final_cost',
                $checkout
            )
        ) {
            $finalCost = max(
                0,
                round(
                    (float)$checkout[
                        'final_cost'
                    ],
                    2
                )
            );

            $msOrder->set(
                'cart_cost',
                $finalCost
            );

            $msOrder->set(
                'cost',
                $finalCost
            );
        }

        $points = max(
            0,
            (float)(
                $checkout[
                    'effective_points'
                ] ??
                0
            )
        );

        if ($points <= 0) {
            break;
        }

        try {
            $nonce = bin2hex(
                random_bytes(12)
            );
        } catch (Throwable $exception) {
            $nonce = sha1(
                uniqid('', true) .
                microtime(true)
            );
        }

        $reservationKey =
            'checkout:' .
            $userId .
            ':' .
            $nonce;

        if (
            !$loyalty->reservePoints(
                $userId,
                $points,
                $reservationKey
            )
        ) {
            $modx->event->output(
                'Недостатньо доступних бонусів. Оновіть сторінку та повторіть оформлення.'
            );
            return;
        }

        $_SESSION[
            'dnepritloyalty_checkout'
        ]['reservation_key'] =
            $reservationKey;
        break;

    case 'msOnCreateOrder':
        if (
            empty($msOrder) ||
            !$msOrder instanceof msOrder
        ) {
            break;
        }

        $orderId = (int)$msOrder->get(
            'id'
        );

        $checkout =
            $_SESSION[
                'dnepritloyalty_checkout'
            ] ??
            [];

        $reservationKey =
            (string)(
                $checkout[
                    'reservation_key'
                ] ??
                ''
            );

        $points =
            (float)(
                $checkout[
                    'effective_points'
                ] ??
                0
            );

        $discount =
            (float)(
                $checkout[
                    'discount_amount'
                ] ??
                0
            );

        if ($reservationKey !== '') {
            $orderReservationKey =
                'order_spend:' .
                $orderId;

            if (
                !$loyalty->renameReservation(
                    $reservationKey,
                    $orderReservationKey,
                    $orderId
                )
            ) {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[DnepritLoyalty] Could not bind reservation to order ' .
                    $orderId
                );
            } else {
                $reservationKey =
                    $orderReservationKey;
            }
        }

        $reward =
            $loyalty->createOrderReward(
                $msOrder,
                $reservationKey,
                $points,
                $discount
            );

        if (
            $reservationKey !== '' &&
            $reward
        ) {
            if (
                !$loyalty->finalizeReservation(
                    $reservationKey,
                    $orderId
                )
            ) {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[DnepritLoyalty] Could not finalize bonus reservation for order ' .
                    $orderId
                );
            }
        }

        unset(
            $_SESSION[
                'dnepritloyalty_checkout'
            ]
        );
        break;

    case 'msOnChangeOrderStatus':
        if (
            !empty($order) &&
            $order instanceof msOrder
        ) {
            $loyalty->processOrderStatus(
                $order,
                (int)$status,
                (int)$old_status
            );
        }
        break;
}
