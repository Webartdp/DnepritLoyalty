<?php

class DnepritLoyaltyAccountsRecalculateProcessor extends modProcessor
{
    public function process()
    {
        $userId = (int)$this->getProperty(
            'user_id',
            0
        );

        if ($userId <= 0) {
            return $this->failure(
                'Не вказано клієнта.'
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

        $loyalty =
            new DnepritLoyalty(
                $this->modx
            );

        $total =
            $loyalty->recalculateLifetime(
                $userId
            );

        if ($total === false) {
            return $this->failure(
                'Не вдалося перерахувати покупки.'
            );
        }

        return $this->success(
            'Lifetime суму перераховано.',
            [
                'lifetime_total' =>
                    $total,
            ]
        );
    }
}

return 'DnepritLoyaltyAccountsRecalculateProcessor';
