<?php

class DnepritLoyaltySettingsSaveProcessor extends modProcessor
{
    public function process()
    {
        $keys = [
            'enabled',
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

        foreach ($keys as $key) {
            $fullKey =
                'dnepritloyalty.' .
                $key;

            $setting =
                $this->modx->getObject(
                    'modSystemSetting',
                    [
                        'key' =>
                            $fullKey,
                    ]
                );

            if (!$setting) {
                continue;
            }

            $setting->set(
                'value',
                (string)$this->getProperty(
                    $key,
                    ''
                )
            );

            if (!$setting->save()) {
                return $this->failure(
                    'Не вдалося зберегти ' .
                    $fullKey
                );
            }
        }

        $this->modx->cacheManager->refresh([
            'system_settings' => [],
        ]);

        return $this->success(
            'Налаштування збережено.'
        );
    }
}

return 'DnepritLoyaltySettingsSaveProcessor';
