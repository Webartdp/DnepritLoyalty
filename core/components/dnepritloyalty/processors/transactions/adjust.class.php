<?php

class DnepritLoyaltyTransactionsAdjustProcessor extends modProcessor
{
    public function process()
    {
        $userId = (int)$this->getProperty(
            'user_id',
            0
        );

        $amount = round(
            (float)$this->getProperty(
                'amount',
                0
            ),
            2
        );

        $comment = trim(
            (string)$this->getProperty(
                'comment',
                ''
            )
        );

        if (
            $userId <= 0 ||
            abs($amount) < 0.00001
        ) {
            return $this->failure(
                'Вкажіть клієнта та ненульову суму.'
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

        try {
            $nonce = bin2hex(
                random_bytes(8)
            );
        } catch (Throwable $exception) {
            $nonce = sha1(
                uniqid('', true)
            );
        }

        $key =
            'manual:' .
            $userId .
            ':' .
            $nonce;

        $result =
            $loyalty->addTransaction(
                $userId,
                $amount,
                'manual',
                $key,
                [
                    'description' =>
                        $comment !== ''
                            ? $comment
                            : 'Ручне коригування',

                    'manager_id' =>
                        (int)$this->modx->user->id,
                ]
            );

        return $result
            ? $this->success(
                'Баланс оновлено.'
            )
            : $this->failure(
                'Не вдалося змінити баланс.'
            );
    }
}

return 'DnepritLoyaltyTransactionsAdjustProcessor';
