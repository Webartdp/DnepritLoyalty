<?php

class DnepritLoyaltyLevelsUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey =
        'DnepritLoyaltyLevel';

    public function beforeSave()
    {
        $type = (string)$this->getProperty(
            'discount_type',
            'percent'
        );

        if (
            !in_array(
                $type,
                [
                    'percent',
                    'fixed',
                ],
                true
            )
        ) {
            $this->setProperty(
                'discount_type',
                'percent'
            );
        }

        return parent::beforeSave();
    }
}

return 'DnepritLoyaltyLevelsUpdateProcessor';
