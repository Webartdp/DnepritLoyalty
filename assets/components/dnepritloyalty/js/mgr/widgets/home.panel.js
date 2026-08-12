DnepritLoyalty.panel.Home = function(config) {
    config = config || {};

    Ext.apply(
        config,
        {
            border: false,
            baseCls: 'modx-formpanel',
            cls: 'container',
            items: [
                {
                    html:
                        '<h2>DnepritLoyalty</h2>' +
                        '<p>Бонусні рахунки та Lifetime Discount</p>',
                    border: false,
                    cls: 'modx-page-header'
                },
                {
                    xtype: 'modx-tabs',
                    deferredRender: false,
                    border: true,
                    defaults: {
                        layout: 'fit',
                        autoHeight: false
                    },
                    items: [
                        {
                            title: 'Клієнти',
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-accounts'
                                }
                            ]
                        },
                        {
                            title: 'Операції',
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-transactions'
                                }
                            ]
                        },
                        {
                            title: 'Рівні',
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-levels'
                                }
                            ]
                        },
                        {
                            title: 'Правила',
                            items: [
                                {
                                    xtype: 'dnepritloyalty-grid-rules'
                                }
                            ]
                        },
                        {
                            title: 'Налаштування',
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
