<?php

class DnepritLoyaltyAccountsRecalculateBulkProcessor extends modProcessor
{
    public function process()
    {
        $raw = $this->getProperty(
            'user_ids',
            ''
        );

        $userIds = [];

        if (is_array($raw)) {
            $userIds = $raw;
        } else {
            $decoded = json_decode(
                (string)$raw,
                true
            );

            if (is_array($decoded)) {
                $userIds = $decoded;
            } else {
                $userIds = preg_split(
                    '/[\s,;]+/',
                    trim((string)$raw),
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );
            }
        }

        $userIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $userIds
                    ),
                    function ($userId) {
                        return $userId > 0;
                    }
                )
            )
        );

        if (!$userIds) {
            return $this->failure(
                'Не вказано клієнтів для перерахунку.'
            );
        }

        if (count($userIds) > 200) {
            return $this->failure(
                'За один раз можна перерахувати не більше 200 клієнтів.'
            );
        }

        $corePath = $this->modx->getOption(
            'dnepritloyalty.core_path',
            null,
            $this->modx->getOption('core_path') .
            'components/dnepritloyalty/'
        );

        require_once $corePath .
            'model/dnepritloyalty/dnepritloyalty.class.php';

        $loyalty = new DnepritLoyalty(
            $this->modx
        );

        $successCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($userIds as $userId) {
            try {
                /*
                 * Manual Lifetime recalculation is an administrative action.
                 * It must not be blocked by allowed_groups: group restrictions
                 * apply to earning/spending bonuses, not to reading order history.
                 */
                $total = $loyalty->recalculateLifetime(
                    $userId
                );

                if ($total === false) {
                    $failedCount++;
                    $results[] = [
                        'user_id' => $userId,
                        'success' => false,
                        'message' => 'Не вдалося перерахувати покупки.',
                    ];
                    continue;
                }

                $successCount++;
                $results[] = [
                    'user_id' => $userId,
                    'success' => true,
                    'lifetime_total' => round(
                        (float)$total,
                        2
                    ),
                ];
            } catch (Throwable $exception) {
                $failedCount++;

                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[DnepritLoyalty] Bulk lifetime recalculation failed for user ' .
                    $userId .
                    ': ' .
                    $exception->getMessage()
                );

                $results[] = [
                    'user_id' => $userId,
                    'success' => false,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $message =
            'Перерахунок завершено. Успішно: ' .
            $successCount .
            '. Помилок: ' .
            $failedCount .
            '.';

        return $this->success(
            $message,
            [
                'requested_count' => count($userIds),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ]
        );
    }
}

return 'DnepritLoyaltyAccountsRecalculateBulkProcessor';
