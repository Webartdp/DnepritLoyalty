<?php

class DnepritLoyaltyLevelsUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey =
        'DnepritLoyaltyLevel';

    /**
     * MODx.grid.Grid autosave sends the edited row as JSON in `data`.
     * Unpack it before the parent processor tries to load the object by id.
     */
    public function initialize()
    {
        $data = $this->getProperty('data');

        if ($data !== null && $data !== '') {
            $properties = is_array($data)
                ? $data
                : $this->modx->fromJSON((string)$data);

            if (!is_array($properties)) {
                return $this->modx->lexicon('invalid_data');
            }

            $this->setProperties($properties);
            $this->unsetProperty('data');
        }

        return parent::initialize();
    }

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
