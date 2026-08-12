DnepritLoyalty.panel.Home = function(config) {
    config = config || {};

    Ext.apply(
        config,
        {
            border: false,
            baseCls: 'modx-formpanel',
            cls: 'container dnepritloyalty-home',
            items: [
                {
                    html:
                        '<h2>' + _('dnepritloyalty') + '</h2>' +
                        '<p>' + _('dnepritloyalty_menu_desc') + '</p>',
                    border: false,
                    cls: 'modx-page-header'
                },
                {
                    xtype: 'modx-tabs',
                    id: 'dnepritloyalty-main-tabs',
                    deferredRender: false,
                    border: true,
                    cls: 'dnepritloyalty-tabs',
                    activeTab: 0,
                    defaults: {
                        layout: 'fit',
                        autoHeight: false,
                        border: false
                    },
                    items: [
                        {
                            title: _('dnepritloyalty_accounts'),
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-accounts'
                                }
                            ]
                        },
                        {
                            title: _('dnepritloyalty_transactions'),
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-transactions'
                                }
                            ]
                        },
                        {
                            title: _('dnepritloyalty_levels'),
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-levels'
                                }
                            ]
                        },
                        {
                            title: _('dnepritloyalty_rules'),
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-rules'
                                }
                            ]
                        },
                        {
                            title: _('dnepritloyalty_settings'),
                            items: [
                                {
                                    xtype: 'dnepritloyalty-panel-settings'
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    );

    DnepritLoyalty.panel.Home.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.panel.Home,
    MODx.Panel
);

Ext.reg(
    'dnepritloyalty-panel-home',
    DnepritLoyalty.panel.Home
);
