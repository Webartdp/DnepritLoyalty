<?php

class DnepritLoyaltyRulesUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey =
        'DnepritLoyaltyRule';

    /**
     * Rules use the same MODx.grid.Grid autosave transport as Levels.
     * Decode the JSON row before modObjectUpdateProcessor looks for `id`.
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
}

return 'DnepritLoyaltyRulesUpdateProcessor';
