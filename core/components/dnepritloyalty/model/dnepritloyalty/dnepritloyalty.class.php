<?php

class DnepritLoyalty
{
    /** @var modX */
    public $modx;

    /** @var array */
    public $config = [];

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $corePath = $modx->getOption(
            'dnepritloyalty.core_path',
            $config,
            $modx->getOption('core_path') . 'components/dnepritloyalty/'
        );
        $assetsUrl = $modx->getOption(
            'dnepritloyalty.assets_url',
            $config,
            $modx->getOption('assets_url') . 'components/dnepritloyalty/'
        );

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'templatesPath' => $corePath . 'elements/templates/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connector.php',
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage(
            'dnepritloyalty',
            $this->config['modelPath'],
            $this->modx->getOption('table_prefix')
        );
    }

    public function isEnabled()
    {
        return (bool)$this->modx->getOption('dnepritloyalty.enabled', null, true);
    }

    public function now()
    {
        return date('Y-m-d H:i:s');
    }

    public function parseIds($value)
    {
        $ids = preg_split('/[\s,;]+/', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
    }

    public function isUserAllowed($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }

        $groups = $this->parseIds(
            $this->modx->getOption('dnepritloyalty.allowed_groups', null, '')
        );

        if (!$groups) {
            return true;
        }

        $query = $this->modx->newQuery('modUserGroupMember');
        $query->where([
            'member' => $userId,
            'user_group:IN' => $groups,
        ]);

        return $this->modx->getCount('modUserGroupMember', $query) > 0;
    }

    public function getAccount($userId, $create = true)
    {
        $userId = (int)$userId;

        if ($userId <= 0) {
            return null;
        }

        $account = $this->modx->getObject(
            'DnepritLoyaltyAccount',
            ['user_id' => $userId]
        );

        if ($account || !$create) {
            return $account;
        }

        $now = $this->now();

        $account = $this->modx->newObject('DnepritLoyaltyAccount');
        $account->fromArray([
            'user_id' => $userId,
            'balance' => 0,
            'reserved' => 0,
            'lifetime_total' => 0,
            'level_id' => 0,
            'discount_type' => 'percent',
            'discount_value' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], '', true, true);

        if ($account->save()) {
            return $account;
        }

        /*
         * Another request may have created the same unique user_id
         * at the same moment. Re-read instead of failing.
         */
        return $this->modx->getObject(
            'DnepritLoyaltyAccount',
            ['user_id' => $userId]
        );
    }

    public function getAvailableBalance($userId)
    {
        $account = $this->getAccount($userId);

        if (!$account) {
            return 0.0;
        }

        return max(
            0,
            round(
                (float)$account->get('balance') -
                (float)$account->get('reserved'),
                2
            )
        );
    }

    public function getRule($key)
    {
        return $this->modx->getObject(
            'DnepritLoyaltyRule',
            ['key' => trim((string)$key)]
        );
    }

    /**
     * Add a posted or reserved ledger entry.
     *
     * Account row is locked with SELECT ... FOR UPDATE. This is the
     * protection that prevents two parallel checkouts from reserving
     * the same bonus balance.
     */
    public function addTransaction(
        $userId,
        $amount,
        $type,
        $uniqueKey,
        array $options = []
    ) {
        $userId = (int)$userId;
        $amount = round((float)$amount, 2);
        $uniqueKey = trim((string)$uniqueKey);

        if (
            $userId <= 0 ||
            abs($amount) < 0.00001 ||
            $uniqueKey === ''
        ) {
            return false;
        }

        $existing = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            ['unique_key' => $uniqueKey]
        );

        if ($existing) {
            return $existing;
        }

        $account = $this->getAccount($userId);

        if (!$account) {
            return false;
        }

        $status = isset($options['status'])
            ? (string)$options['status']
            : 'posted';

        $now = $this->now();

        $this->modx->beginTransaction();

        try {
            /*
             * Serialize all balance mutations for this user.
             */
            $table = $this->modx->getTableName(
                'DnepritLoyaltyAccount'
            );

            $statement = $this->modx->prepare(
                'SELECT id, balance, reserved FROM ' .
                $table .
                ' WHERE user_id = ? FOR UPDATE'
            );

            if (
                !$statement ||
                !$statement->execute([$userId])
            ) {
                throw new RuntimeException(
                    'Could not lock loyalty account.'
                );
            }

            $locked = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$locked) {
                throw new RuntimeException(
                    'Loyalty account disappeared during update.'
                );
            }

            /*
             * A second idempotency check is required after obtaining
             * the account lock.
             */
            $existing = $this->modx->getObject(
                'DnepritLoyaltyTransaction',
                ['unique_key' => $uniqueKey]
            );

            if ($existing) {
                $this->modx->commit();
                return $existing;
            }

            $balance = round(
                (float)$locked['balance'],
                2
            );

            $reserved = round(
                (float)$locked['reserved'],
                2
            );

            if ($status === 'posted') {
                $newBalance = round(
                    $balance + $amount,
                    2
                );

                if (
                    $newBalance < -0.00001 &&
                    empty($options['allow_negative'])
                ) {
                    $this->modx->rollBack();
                    return false;
                }

                $account->set(
                    'balance',
                    $newBalance
                );
            } elseif ($status === 'reserved') {
                $points = abs($amount);

                $available = round(
                    $balance - $reserved,
                    2
                );

                if (
                    $amount >= 0 ||
                    $available + 0.00001 < $points
                ) {
                    $this->modx->rollBack();
                    return false;
                }

                $account->set(
                    'reserved',
                    round(
                        $reserved + $points,
                        2
                    )
                );
            } else {
                throw new InvalidArgumentException(
                    'Unsupported transaction status.'
                );
            }

            $account->set(
                'updated_at',
                $now
            );

            if (!$account->save()) {
                throw new RuntimeException(
                    'Could not save loyalty account.'
                );
            }

            $transaction = $this->modx->newObject(
                'DnepritLoyaltyTransaction'
            );

            $transaction->fromArray([
                'account_id' =>
                    (int)$account->get('id'),

                'user_id' =>
                    $userId,

                'order_id' =>
                    (int)($options['order_id'] ?? 0),

                'amount' =>
                    $amount,

                'type' =>
                    (string)$type,

                'status' =>
                    $status,

                'description' =>
                    (string)($options['description'] ?? ''),

                'unique_key' =>
                    $uniqueKey,

                'expires_at' =>
                    $options['expires_at'] ?? null,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,

                'manager_id' =>
                    (int)($options['manager_id'] ?? 0),
            ], '', true, true);

            if (!$transaction->save()) {
                throw new RuntimeException(
                    'Could not save loyalty transaction.'
                );
            }

            $this->modx->commit();

            return $transaction;
        } catch (Throwable $exception) {
            $this->modx->rollBack();

            /*
             * If the only problem was a unique-key race, treat it as
             * a successful idempotent replay.
             */
            $existing = $this->modx->getObject(
                'DnepritLoyaltyTransaction',
                ['unique_key' => $uniqueKey]
            );

            if ($existing) {
                return $existing;
            }

            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[DnepritLoyalty] ' .
                $exception->getMessage()
            );

            return false;
        }
    }

    public function reservePoints(
        $userId,
        $points,
        $reservationKey,
        $expiresAt = null
    ) {
        $points = round(
            max(0, (float)$points),
            2
        );

        if ($points <= 0) {
            return true;
        }

        return $this->addTransaction(
            $userId,
            -$points,
            'order_spend',
            $reservationKey,
            [
                'status' => 'reserved',

                'expires_at' =>
                    $expiresAt ?:
                    date(
                        'Y-m-d H:i:s',
                        time() + 1800
                    ),

                'description' =>
                    'Резерв бонусів під час оформлення замовлення',
            ]
        );
    }

    public function renameReservation(
        $oldKey,
        $newKey,
        $orderId
    ) {
        $transaction = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            [
                'unique_key' => (string)$oldKey,
                'status' => 'reserved',
            ]
        );

        if (!$transaction) {
            return false;
        }

        if (
            $this->modx->getObject(
                'DnepritLoyaltyTransaction',
                ['unique_key' => (string)$newKey]
            )
        ) {
            return false;
        }

        $transaction->set(
            'unique_key',
            (string)$newKey
        );

        $transaction->set(
            'order_id',
            (int)$orderId
        );

        $transaction->set(
            'updated_at',
            $this->now()
        );

        return $transaction->save();
    }

    public function finalizeReservation(
        $reservationKey,
        $orderId
    ) {
        $transaction = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            [
                'unique_key' =>
                    (string)$reservationKey,

                'status' =>
                    'reserved',
            ]
        );

        if (!$transaction) {
            /*
             * Idempotent replay: already finalized.
             */
            $posted = $this->modx->getObject(
                'DnepritLoyaltyTransaction',
                [
                    'unique_key' =>
                        (string)$reservationKey,

                    'status' =>
                        'posted',
                ]
            );

            return (bool)$posted;
        }

        $userId = (int)$transaction->get(
            'user_id'
        );

        $points = abs(
            (float)$transaction->get('amount')
        );

        $this->modx->beginTransaction();

        try {
            $accountTable = $this->modx->getTableName(
                'DnepritLoyaltyAccount'
            );

            $statement = $this->modx->prepare(
                'SELECT id, balance, reserved FROM ' .
                $accountTable .
                ' WHERE user_id = ? FOR UPDATE'
            );

            if (
                !$statement ||
                !$statement->execute([$userId])
            ) {
                throw new RuntimeException(
                    'Could not lock loyalty account while finalizing.'
                );
            }

            $locked = $statement->fetch(
                PDO::FETCH_ASSOC
            );

            if (!$locked) {
                throw new RuntimeException(
                    'Could not read locked account.'
                );
            }

            $account = $this->modx->getObject(
                'DnepritLoyaltyAccount',
                (int)$locked['id']
            );

            if (!$account) {
                throw new RuntimeException(
                    'Could not load loyalty account.'
                );
            }

            $account->set(
                'reserved',
                max(
                    0,
                    round(
                        (float)$locked['reserved'] -
                        $points,
                        2
                    )
                )
            );

            $account->set(
                'balance',
                round(
                    (float)$locked['balance'] -
                    $points,
                    2
                )
            );

            $account->set(
                'updated_at',
                $this->now()
            );

            if (!$account->save()) {
                throw new RuntimeException(
                    'Could not finalize reserved balance.'
                );
            }

            $transaction->set(
                'status',
                'posted'
            );

            $transaction->set(
                'order_id',
                (int)$orderId
            );

            $transaction->set(
                'expires_at',
                null
            );

            $transaction->set(
                'updated_at',
                $this->now()
            );

            if (!$transaction->save()) {
                throw new RuntimeException(
                    'Could not finalize reservation transaction.'
                );
            }

            $this->modx->commit();

            return true;
        } catch (Throwable $exception) {
            $this->modx->rollBack();

            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[DnepritLoyalty] ' .
                $exception->getMessage()
            );

            return false;
        }
    }

    public function releaseReservation(
        $reservationKey,
        $description = 'Резерв бонусів скасовано'
    ) {
        $transaction = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            [
                'unique_key' =>
                    (string)$reservationKey,

                'status' =>
                    'reserved',
            ]
        );

        if (!$transaction) {
            return true;
        }

        $userId = (int)$transaction->get(
            'user_id'
        );

        $points = abs(
            (float)$transaction->get('amount')
        );

        $this->modx->beginTransaction();

        try {
            $accountTable = $this->modx->getTableName(
                'DnepritLoyaltyAccount'
            );

            $statement = $this->modx->prepare(
                'SELECT id, reserved FROM ' .
                $accountTable .
                ' WHERE user_id = ? FOR UPDATE'
            );

            if (
                !$statement ||
                !$statement->execute([$userId])
            ) {
                throw new RuntimeException(
                    'Could not lock account while releasing reservation.'
                );
            }

            $locked = $statement->fetch(
                PDO::FETCH_ASSOC
            );

            if (!$locked) {
                throw new RuntimeException(
                    'Could not load locked reservation account.'
                );
            }

            $account = $this->modx->getObject(
                'DnepritLoyaltyAccount',
                (int)$locked['id']
            );

            if (!$account) {
                throw new RuntimeException(
                    'Could not load reservation account.'
                );
            }

            $account->set(
                'reserved',
                max(
                    0,
                    round(
                        (float)$locked['reserved'] -
                        $points,
                        2
                    )
                )
            );

            $account->set(
                'updated_at',
                $this->now()
            );

            if (!$account->save()) {
                throw new RuntimeException(
                    'Could not release reserved balance.'
                );
            }

            $transaction->set(
                'status',
                'reversed'
            );

            $transaction->set(
                'description',
                (string)$description
            );

            $transaction->set(
                'updated_at',
                $this->now()
            );

            if (!$transaction->save()) {
                throw new RuntimeException(
                    'Could not reverse reservation transaction.'
                );
            }

            $this->modx->commit();

            return true;
        } catch (Throwable $exception) {
            $this->modx->rollBack();

            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[DnepritLoyalty] ' .
                $exception->getMessage()
            );

            return false;
        }
    }

    public function reversePostedTransaction(
        $uniqueKey,
        $reverseKey,
        $description
    ) {
        $existing = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            ['unique_key' => $reverseKey]
        );

        if ($existing) {
            return $existing;
        }

        $original = $this->modx->getObject(
            'DnepritLoyaltyTransaction',
            [
                'unique_key' =>
                    (string)$uniqueKey,

                'status' =>
                    'posted',
            ]
        );

        if (!$original) {
            return true;
        }

        return $this->addTransaction(
            (int)$original->get('user_id'),
            -(float)$original->get('amount'),
            'reversal',
            $reverseKey,
            [
                'order_id' =>
                    (int)$original->get('order_id'),

                'description' =>
                    (string)$description,

                /*
                 * A cancellation may legitimately create a
                 * temporary negative bonus balance if the user has
                 * already spent earned points elsewhere.
                 */
                'allow_negative' =>
                    true,
            ]
        );
    }

    public function awardRule(
        $userId,
        $ruleKey,
        $uniqueSuffix = ''
    ) {
        $rule = $this->getRule($ruleKey);

        if (
            !$rule ||
            !(bool)$rule->get('enabled')
        ) {
            return true;
        }

        $amount = round(
            (float)$rule->get('amount'),
            2
        );

        if ($amount <= 0) {
            return true;
        }

        $key =
            'rule:' .
            $ruleKey .
            ':' .
            (int)$userId;

        if (
            !(bool)$rule->get('once_only') &&
            $uniqueSuffix !== ''
        ) {
            $key .= ':' .
                preg_replace(
                    '/[^a-zA-Z0-9._-]+/',
                    '-',
                    (string)$uniqueSuffix
                );
        }

        return $this->addTransaction(
            $userId,
            $amount,
            $ruleKey,
            $key,
            [
                'description' =>
                    (string)$rule->get('title'),
            ]
        );
    }

    public function getLevelForTotal($total)
    {
        $query = $this->modx->newQuery(
            'DnepritLoyaltyLevel'
        );

        $query->where([
            'active' => 1,
            'threshold:<=' =>
                round((float)$total, 2),
        ]);

        $query->sortby(
            'threshold',
            'DESC'
        );

        $query->sortby(
            'rank',
            'DESC'
        );

        $query->limit(1);

        return $this->modx->getObject(
            'DnepritLoyaltyLevel',
            $query
        );
    }

    public function applyLevel(
        $userId,
        $lifetimeTotal
    ) {
        $account = $this->getAccount($userId);

        if (!$account) {
            return false;
        }

        $level = $this->getLevelForTotal(
            $lifetimeTotal
        );

        $account->set(
            'lifetime_total',
            round((float)$lifetimeTotal, 2)
        );

        $account->set(
            'level_id',
            $level
                ? (int)$level->get('id')
                : 0
        );

        $account->set(
            'discount_type',
            $level
                ? (string)$level->get('discount_type')
                : 'percent'
        );

        $account->set(
            'discount_value',
            $level
                ? (float)$level->get('discount_value')
                : 0
        );

        $account->set(
            'updated_at',
            $this->now()
        );

        return $account->save();
    }

    public function recalculateLifetime($userId)
    {
        $userId = (int)$userId;

        $statuses = $this->parseIds(
            $this->modx->getOption(
                'dnepritloyalty.lifetime_statuses',
                null,
                ''
            )
        );

        if ($userId <= 0) {
            return false;
        }

        /*
         * No configured status means "program not configured yet",
         * not "all orders".
         */
        if (!$statuses) {
            return (float)$this->getAccount(
                $userId
            )->get('lifetime_total');
        }

        if (!$this->loadMiniShop2()) {
            return false;
        }

        $query = $this->modx->newQuery(
            'msOrder'
        );

        $query->where([
            'user_id' =>
                $userId,

            'status:IN' =>
                $statuses,
        ]);

        $from = trim(
            (string)$this->modx->getOption(
                'dnepritloyalty.lifetime_from',
                null,
                ''
            )
        );

        $to = trim(
            (string)$this->modx->getOption(
                'dnepritloyalty.lifetime_to',
                null,
                ''
            )
        );

        if ($from !== '') {
            $query->where([
                'createdon:>=' =>
                    $from .
                    (
                        strlen($from) === 10
                            ? ' 00:00:00'
                            : ''
                    ),
            ]);
        }

        if ($to !== '') {
            $query->where([
                'createdon:<=' =>
                    $to .
                    (
                        strlen($to) === 10
                            ? ' 23:59:59'
                            : ''
                    ),
            ]);
        }

        $query->select([
            'total' =>
                'COALESCE(SUM(cost), 0)',
        ]);

        $statement = $query->prepare();

        $total = 0.0;

        if (
            $statement &&
            $statement->execute()
        ) {
            $total = round(
                (float)$statement->fetchColumn(),
                2
            );
        }

        $this->applyLevel(
            $userId,
            $total
        );

        return $total;
    }

    public function calculateLifetimeDiscount(
        $userId,
        $orderCost
    ) {
        $orderCost = max(
            0,
            round((float)$orderCost, 2)
        );

        $minimum = max(
            0,
            (float)$this->modx->getOption(
                'dnepritloyalty.discount_min_order',
                null,
                0
            )
        );

        if (
            !$this->isEnabled() ||
            !$this->isUserAllowed($userId) ||
            $orderCost < $minimum
        ) {
            return 0.0;
        }

        $account = $this->getAccount($userId);

        if (!$account) {
            return 0.0;
        }

        $value = max(
            0,
            (float)$account->get(
                'discount_value'
            )
        );

        if ($value <= 0) {
            return 0.0;
        }

        if (
            (string)$account->get(
                'discount_type'
            ) === 'fixed'
        ) {
            return min(
                $orderCost,
                round($value, 2)
            );
        }

        return min(
            $orderCost,
            round(
                $orderCost *
                min(100, $value) /
                100,
                2
            )
        );
    }

    public function getAllowedSpendPoints(
        $userId,
        $orderCostAfterDiscount
    ) {
        if (
            !(bool)$this->modx->getOption(
                'dnepritloyalty.spend_enabled',
                null,
                true
            )
        ) {
            return 0.0;
        }

        $cost = max(
            0,
            (float)$orderCostAfterDiscount
        );

        $minimumOrder = max(
            0,
            (float)$this->modx->getOption(
                'dnepritloyalty.min_order_for_spend',
                null,
                0
            )
        );

        if (
            $cost < $minimumOrder ||
            !$this->isUserAllowed($userId)
        ) {
            return 0.0;
        }

        $available = $this->getAvailableBalance(
            $userId
        );

        $minimumPoints = max(
            0,
            (float)$this->modx->getOption(
                'dnepritloyalty.min_spend_points',
                null,
                0
            )
        );

        if ($available < $minimumPoints) {
            return 0.0;
        }

        $pointValue = max(
            0.000001,
            (float)$this->modx->getOption(
                'dnepritloyalty.point_value',
                null,
                1
            )
        );

        $maxPercent = min(
            100,
            max(
                0,
                (float)$this->modx->getOption(
                    'dnepritloyalty.max_spend_percent',
                    null,
                    30
                )
            )
        );

        $maxMoney =
            $cost *
            $maxPercent /
            100;

        $maxPointsByOrder =
            floor(
                (
                    $maxMoney /
                    $pointValue
                ) *
                100
            ) /
            100;

        return max(
            0,
            min(
                $available,
                $maxPointsByOrder
            )
        );
    }

    public function pointsToMoney($points)
    {
        $pointValue = max(
            0.000001,
            (float)$this->modx->getOption(
                'dnepritloyalty.point_value',
                null,
                1
            )
        );

        return round(
            max(0, (float)$points) *
            $pointValue,
            2
        );
    }

    public function calculateOrderReward(
        $userId,
        $orderCost
    ) {
        $account = $this->getAccount($userId);

        $percent = max(
            0,
            (float)$this->modx->getOption(
                'dnepritloyalty.order_reward_percent',
                null,
                0
            )
        );

        /*
         * Level-specific earn percent overrides global earn percent
         * only when it is greater than zero.
         */
        if (
            $account &&
            (int)$account->get('level_id') > 0
        ) {
            $level = $account->getOne('Level');

            if (
                $level &&
                (float)$level->get('earn_percent') > 0
            ) {
                $percent =
                    (float)$level->get(
                        'earn_percent'
                    );
            }
        }

        $minimum = max(
            0,
            (float)$this->modx->getOption(
                'dnepritloyalty.min_order_for_reward',
                null,
                0
            )
        );

        if (
            $percent <= 0 ||
            (float)$orderCost < $minimum
        ) {
            return 0.0;
        }

        $pointValue = max(
            0.000001,
            (float)$this->modx->getOption(
                'dnepritloyalty.point_value',
                null,
                1
            )
        );

        return floor(
            (
                (
                    (float)$orderCost *
                    $percent /
                    100
                ) /
                $pointValue
            ) *
            100
        ) /
        100;
    }

    public function createOrderReward(
        $order,
        $reservationKey = '',
        $pointsSpent = 0,
        $discountAmount = 0
    ) {
        $orderId = (int)$order->get('id');

        if ($orderId <= 0) {
            return null;
        }

        $existing = $this->modx->getObject(
            'DnepritLoyaltyOrderReward',
            ['order_id' => $orderId]
        );

        if ($existing) {
            return $existing;
        }

        $now = $this->now();

        $reward = $this->modx->newObject(
            'DnepritLoyaltyOrderReward'
        );

        $reward->fromArray([
            'order_id' =>
                $orderId,

            'user_id' =>
                (int)$order->get('user_id'),

            'order_total' =>
                (float)$order->get('cost'),

            'points_earned' =>
                0,

            'points_spent' =>
                round((float)$pointsSpent, 2),

            'lifetime_discount' =>
                round((float)$discountAmount, 2),

            'state' =>
                'pending',

            'reservation_key' =>
                (string)$reservationKey,

            'created_at' =>
                $now,

            'updated_at' =>
                $now,
        ], '', true, true);

        return $reward->save()
            ? $reward
            : null;
    }

    public function processOrderStatus(
        $order,
        $status,
        $oldStatus
    ) {
        if (
            !$this->isEnabled() ||
            !$order
        ) {
            return;
        }

        $userId = (int)$order->get('user_id');
        $orderId = (int)$order->get('id');

        if (
            $userId <= 0 ||
            $orderId <= 0
        ) {
            return;
        }

        $status = (int)$status;

        $rewardStatuses = $this->parseIds(
            $this->modx->getOption(
                'dnepritloyalty.reward_statuses',
                null,
                ''
            )
        );

        $cancelStatuses = $this->parseIds(
            $this->modx->getOption(
                'dnepritloyalty.cancel_statuses',
                null,
                ''
            )
        );

        $reward = $this->modx->getObject(
            'DnepritLoyaltyOrderReward',
            ['order_id' => $orderId]
        );

        if (
            in_array(
                $status,
                $rewardStatuses,
                true
            )
        ) {
            if (!$reward) {
                $reward =
                    $this->createOrderReward(
                        $order
                    );
            }

            if (
                $reward &&
                (string)$reward->get('state') !==
                    'posted'
            ) {
                $points =
                    $this->calculateOrderReward(
                        $userId,
                        (float)$order->get('cost')
                    );

                if ($points > 0) {
                    $this->addTransaction(
                        $userId,
                        $points,
                        'order_reward',
                        'order_reward:' . $orderId,
                        [
                            'order_id' =>
                                $orderId,

                            'description' =>
                                'Бонуси за замовлення №' .
                                $orderId,
                        ]
                    );
                }

                $reward->set(
                    'points_earned',
                    $points
                );

                $reward->set(
                    'state',
                    'posted'
                );

                $reward->set(
                    'updated_at',
                    $this->now()
                );

                $reward->save();

                $this->awardRule(
                    $userId,
                    'first_order'
                );
            }

            $this->recalculateLifetime(
                $userId
            );

            return;
        }

        if (
            in_array(
                $status,
                $cancelStatuses,
                true
            )
        ) {
            if ($reward) {
                $this->reversePostedTransaction(
                    'order_reward:' . $orderId,
                    'order_reward_reversal:' . $orderId,
                    'Скасування бонусів за замовлення №' .
                    $orderId
                );

                $this->reversePostedTransaction(
                    'order_spend:' . $orderId,
                    'order_spend_refund:' . $orderId,
                    'Повернення бонусів за скасоване замовлення №' .
                    $orderId
                );

                $reservationKey =
                    (string)$reward->get(
                        'reservation_key'
                    );

                if ($reservationKey !== '') {
                    $this->releaseReservation(
                        $reservationKey,
                        'Замовлення №' .
                        $orderId .
                        ' скасовано'
                    );
                }

                $reward->set(
                    'state',
                    'reversed'
                );

                $reward->set(
                    'updated_at',
                    $this->now()
                );

                $reward->save();
            }

            $this->recalculateLifetime(
                $userId
            );
        }
    }

    public function releaseExpiredReservations()
    {
        $query = $this->modx->newQuery(
            'DnepritLoyaltyTransaction'
        );

        $query->where([
            'status' =>
                'reserved',

            'expires_at:<=' =>
                $this->now(),
        ]);

        $rows = $this->modx->getCollection(
            'DnepritLoyaltyTransaction',
            $query
        );

        $count = 0;

        foreach ($rows as $transaction) {
            if (
                $this->releaseReservation(
                    (string)$transaction->get(
                        'unique_key'
                    ),
                    'Час резерву бонусів минув'
                )
            ) {
                $count++;
            }
        }

        return $count;
    }

    public function loadMiniShop2()
    {
        if (
            $this->modx->getService(
                'miniShop2'
            )
        ) {
            return true;
        }

        $corePath = $this->modx->getOption(
            'minishop2.core_path',
            null,
            $this->modx->getOption(
                'core_path'
            ) .
            'components/minishop2/'
        );

        return $this->modx->addPackage(
            'minishop2',
            $corePath . 'model/'
        );
    }
}
