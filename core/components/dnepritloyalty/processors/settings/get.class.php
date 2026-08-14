<?php

class DnepritLoyaltySettingsGetProcessor extends modProcessor
{
    public function process()
    {
        $keys = [
            'point_value',
            'order_reward_percent',
            'min_order_for_reward',
            'spend_enabled',
            'max_spend_percent',
            'min_spend_points',
            'min_order_for_spend',
            'discount_min_order',
            'lifetime_statuses',
            'reward_statuses',
            'cancel_statuses',
            'lifetime_from',
            'lifetime_to',
            'allowed_groups',
            'sort_order',
        ];

        $data = [];

        foreach ($keys as $key) {
            $data[$key] =
                $this->modx->getOption(
                    'dnepritloyalty.' .
                    $key,
                    null,
                    ''
                );
        }

        return $this->success(
            '',
            $data
        );
    }
}

return 'DnepritLoyaltySettingsGetProcessor';
