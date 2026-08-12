<?php

class DnepritLoyaltyHomeManagerController extends modExtraManagerController
{
    /** @var DnepritLoyalty */
    public $dnepritLoyalty;

    public function initialize()
    {
        $corePath = $this->modx->getOption(
            'dnepritloyalty.core_path',
            null,
            $this->modx->getOption('core_path') .
            'components/dnepritloyalty/'
        );

        require_once $corePath .
            'model/dnepritloyalty/dnepritloyalty.class.php';

        $this->dnepritLoyalty =
            new DnepritLoyalty(
                $this->modx
            );

        $this->addJavascript(
            $this->dnepritLoyalty->config[
                'jsUrl'
            ] .
            'mgr/dnepritloyalty.js'
        );

        $this->addHtml(
            '<script>Ext.onReady(function(){' .
            'DnepritLoyalty.config=' .
            $this->modx->toJSON(
                $this->dnepritLoyalty->config
            ) .
            ';});</script>'
        );

        return parent::initialize();
    }

    public function getLanguageTopics()
    {
        return [
            'dnepritloyalty:default',
        ];
    }

    public function checkPermissions()
    {
        return (bool)(
            $this->modx->user->sudo ||
            $this->modx->hasPermission(
                'dnepritloyalty_view'
            )
        );
    }

    public function process(
        array $scriptProperties = []
    ) {
        return '';
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon(
            'dnepritloyalty'
        );
    }

    public function loadCustomCssJs()
    {
        $js =
            $this->dnepritLoyalty->config[
                'jsUrl'
            ] .
            'mgr/';

        $this->addCss(
            $this->dnepritLoyalty->config[
                'cssUrl'
            ] .
            'mgr.css'
        );

        $this->addJavascript(
            $js .
            'widgets/accounts.grid.js'
        );

        $this->addJavascript(
            $js .
            'widgets/transactions.grid.js'
        );

        $this->addJavascript(
            $js .
            'widgets/levels.grid.js'
        );

        $this->addJavascript(
            $js .
            'widgets/rules.grid.js'
        );

        $this->addJavascript(
            $js .
            'widgets/settings.panel.js'
        );

        $this->addJavascript(
            $js .
            'widgets/home.panel.js'
        );

        $this->addLastJavascript(
            $js .
            'sections/home.js'
        );
    }

    public function getTemplateFile()
    {
        return
            $this->dnepritLoyalty->config[
                'templatesPath'
            ] .
            'mgr/home.tpl';
    }

    public static function getDefaultController()
    {
        return 'home';
    }
}
