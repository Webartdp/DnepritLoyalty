<?php

class DnepritLoyaltyTransactionsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey =
        'DnepritLoyaltyTransaction';

    public $languageTopics = [
        'dnepritloyalty:default',
    ];

    public $defaultSortField =
        'created_at';

    public $defaultSortDirection =
        'DESC';

    public function prepareQueryBeforeCount(
        xPDOQuery $c
    ) {
        $userId = (int)$this->getProperty(
            'user_id',
            0
        );

        $query = trim(
            (string)$this->getProperty(
                'query',
                ''
            )
        );

        $c->leftJoin(
            'modUserProfile',
            'Profile',
            'Profile.internalKey = DnepritLoyaltyTransaction.user_id'
        );

        if ($userId > 0) {
            $c->where([
                'DnepritLoyaltyTransaction.user_id' =>
                    $userId,
            ]);
        }

        if ($query !== '') {
            $c->where([
                'Profile.email:LIKE' =>
                    '%' . $query . '%',

                'OR:DnepritLoyaltyTransaction.description:LIKE' =>
                    '%' . $query . '%',

                'OR:DnepritLoyaltyTransaction.unique_key:LIKE' =>
                    '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareQueryAfterCount(
        xPDOQuery $c
    ) {
        $c->select(
            $this->modx->getSelectColumns(
                'DnepritLoyaltyTransaction',
                'DnepritLoyaltyTransaction'
            )
        );

        $c->select([
            'email' =>
                'Profile.email',

            'fullname' =>
                'Profile.fullname',
        ]);

        return $c;
    }
}

return 'DnepritLoyaltyTransactionsGetListProcessor';
