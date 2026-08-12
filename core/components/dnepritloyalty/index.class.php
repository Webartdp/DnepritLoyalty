<?php

require_once
    MODX_CORE_PATH .
    'components/dnepritloyalty/controllers/mgr/home.class.php';

class DnepritloyaltyIndexManagerController extends DnepritLoyaltyHomeManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}
