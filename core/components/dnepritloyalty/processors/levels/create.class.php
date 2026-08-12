<?php

class DnepritLoyaltyLevelsCreateProcessor extends modObjectCreateProcessor
{
    public $classKey =
        'DnepritLoyaltyLevel';

    public function beforeSave()
    {
        if (
            trim(
                (string)$this->getProperty(
                    'title'
                )
            ) === ''
        ) {
            $this->addFieldError(
                'title',
                'Вкажіть назву.'
            );
        }

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

        return !$this->hasErrors();
    }
}

return 'DnepritLoyaltyLevelsCreateProcessor';
