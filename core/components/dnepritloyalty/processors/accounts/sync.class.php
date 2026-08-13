<?php

class DnepritLoyaltyAccountsSyncProcessor extends modProcessor
{
    public $languageTopics = [
        'dnepritloyalty:default',
    ];

    public function process()
    {
        $corePath = $this->modx->getOption(
            'dnepritloyalty.core_path',
            null,
            $this->modx->getOption('core_path') .
            'components/dnepritloyalty/'
        );

        require_once $corePath .
            'model/dnepritloyalty/dnepritloyalty.class.php';

        $loyalty = new DnepritLoyalty(
            $this->modx
        );

        $groups = $loyalty->parseIds(
            $this->modx->getOption(
                'dnepritloyalty.allowed_groups',
                null,
                ''
            )
        );

        if (!$groups) {
            return $this->failure(
                $this->modx->lexicon(
                    'dnepritloyalty_sync_groups_required'
                )
            );
        }

        $query = $this->modx->newQuery(
            'modUserGroupMember'
        );

        $query->where([
            'user_group:IN' => $groups,
        ]);

        $query->select([
            'member',
        ]);

        $query->groupby(
            'member'
        );

        $statement = $query->prepare();

        if (
            !$statement ||
            !$statement->execute()
        ) {
            return $this->failure(
                $this->modx->lexicon(
                    'dnepritloyalty_sync_failed'
                )
            );
        }

        $userIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $statement->fetchAll(
                            PDO::FETCH_COLUMN
                        )
                    ),
                    function ($userId) {
                        return $userId > 0;
                    }
                )
            )
        );

        $matched = count($userIds);
        $created = 0;
        $existing = 0;
        $failed = 0;

        foreach ($userIds as $userId) {
            $user = $this->modx->getObject(
                'modUser',
                $userId
            );

            if (!$user) {
                $failed++;
                continue;
            }

            $account = $loyalty->getAccount(
                $userId,
                false
            );

            if ($account) {
                $existing++;
                continue;
            }

            $account = $loyalty->getAccount(
                $userId,
                true
            );

            if ($account) {
                $created++;
            } else {
                $failed++;
            }
        }

        return $this->success(
            $this->modx->lexicon(
                'dnepritloyalty_sync_success',
                [
                    'matched' => $matched,
                    'created' => $created,
                    'existing' => $existing,
                    'failed' => $failed,
                ]
            ),
            [
                'groups' => $groups,
                'matched' => $matched,
                'created' => $created,
                'existing' => $existing,
                'failed' => $failed,
            ]
        );
    }
}

return 'DnepritLoyaltyAccountsSyncProcessor';
