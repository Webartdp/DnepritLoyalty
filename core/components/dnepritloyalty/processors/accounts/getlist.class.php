<?php

class DnepritLoyaltyAccountsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey =
        'DnepritLoyaltyAccount';

    public $languageTopics = [
        'dnepritloyalty:default',
    ];

    public $defaultSortField =
        'updated_at';

    public $defaultSortDirection =
        'DESC';

    public function prepareQueryBeforeCount(
        xPDOQuery $c
    ) {
        $query = trim(
            (string)$this->getProperty(
                'query',
                ''
            )
        );

        $c->leftJoin(
            'modUser',
            'User',
            'User.id = DnepritLoyaltyAccount.user_id'
        );

        $c->leftJoin(
            'modUserProfile',
            'Profile',
            'Profile.internalKey = DnepritLoyaltyAccount.user_id'
        );

        $c->leftJoin(
            'DnepritLoyaltyLevel',
            'Level',
            'Level.id = DnepritLoyaltyAccount.level_id'
        );

        if ($query !== '') {
            $c->where([
                'Profile.fullname:LIKE' =>
                    '%' . $query . '%',

                'OR:Profile.email:LIKE' =>
                    '%' . $query . '%',

                'OR:User.username:LIKE' =>
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
                'DnepritLoyaltyAccount',
                'DnepritLoyaltyAccount'
            )
        );

        $c->select([
            'fullname' =>
                'Profile.fullname',

            'email' =>
                'Profile.email',

            'username' =>
                'User.username',

            'level_title' =>
                'Level.title',

            'available' =>
                '(DnepritLoyaltyAccount.balance - DnepritLoyaltyAccount.reserved)',
        ]);

        return $c;
    }
}

return 'DnepritLoyaltyAccountsGetListProcessor';
