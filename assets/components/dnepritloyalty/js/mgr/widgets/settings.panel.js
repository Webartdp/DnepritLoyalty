DnepritLoyalty.panel.Settings = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        border: false,
        autoScroll: true,
        bodyStyle: 'padding:15px',
        items: [
            {
                xtype: 'form',
                id: 'dnepritloyalty-settings-form',
                labelWidth: 250,
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'combo-boolean',
                        name: 'enabled',
                        hiddenName: 'enabled',
                        fieldLabel: 'Компонент увімкнено'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'point_value',
                        fieldLabel: 'Вартість 1 бонусу',
                        decimalPrecision: 4
                    },
                    {
                        xtype: 'numberfield',
                        name: 'order_reward_percent',
                        fieldLabel: 'Бонуси за замовлення, %',
                        decimalPrecision: 3
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_order_for_reward',
                        fieldLabel: 'Мін. замовлення для нарахування',
                        decimalPrecision: 2
                    },
                    {
                        xtype: 'combo-boolean',
                        name: 'spend_enabled',
                        hiddenName: 'spend_enabled',
                        fieldLabel: 'Дозволити оплату бонусами'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'max_spend_percent',
                        fieldLabel: 'Макс. частка оплати бонусами, %',
                        decimalPrecision: 2
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_spend_points',
                        fieldLabel: 'Мін. бонусів для списання',
                        decimalPrecision: 2
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_order_for_spend',
                        fieldLabel: 'Мін. замовлення для списання',
                        decimalPrecision: 2
                    },
                    {
                        xtype: 'numberfield',
                        name: 'discount_min_order',
                        fieldLabel: 'Мін. замовлення для Lifetime Discount',
                        decimalPrecision: 2
                    },
                    {
                        xtype: 'textfield',
                        name: 'lifetime_statuses',
                        fieldLabel: 'Lifetime статуси miniShop2 (ID через кому)'
                    },
                    {
                        xtype: 'textfield',
                        name: 'reward_statuses',
                        fieldLabel: 'Статуси нарахування (ID через кому)'
                    },
                    {
                        xtype: 'textfield',
                        name: 'cancel_statuses',
                        fieldLabel: 'Статуси скасування (ID через кому)'
                    },
                    {
                        xtype: 'datefield',
                        format: 'Y-m-d',
                        name: 'lifetime_from',
                        fieldLabel: 'Lifetime від'
                    },
                    {
                        xtype: 'datefield',
                        format: 'Y-m-d',
                        name: 'lifetime_to',
                        fieldLabel: 'Lifetime до'
                    },
                    {
                        xtype: 'textfield',
                        name: 'allowed_groups',
                        fieldLabel: 'Групи користувачів (ID через кому)'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'sort_order',
                        fieldLabel: 'Порядок рядка у підсумках'
                    }
                ],
                tbar: [
                    {
                        text: 'Зберегти',
                        handler: this.saveSettings,
                        scope: this
                    }
                ]
            }
        ],
        listeners: {
            afterrender: {
                fn: this.loadSettings,
                scope: this
            }
        }
    });

    DnepritLoyalty.panel.Settings.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.panel.Settings,
    MODx.Panel,
    {
        loadSettings: function() {
            MODx.Ajax.request({
                url: DnepritLoyalty.config.connectorUrl,
                params: {
                    action: 'settings/get'
                },
                listeners: {
                    success: {
                        fn: function(response) {
                            var form =
                                Ext.getCmp(
                                    'dnepritloyalty-settings-form'
                                ).getForm();

                            form.setValues(
                                response.object || {}
                            );
                        },
                        scope: this
                    }
                }
            });
        },

        saveSettings: function() {
            var form =
                Ext.getCmp(
                    'dnepritloyalty-settings-form'
                ).getForm();

            MODx.Ajax.request({
                url: DnepritLoyalty.config.connectorUrl,
                params: Ext.apply(
                    {
                        action: 'settings/save'
                    },
                    form.getValues()
                ),
                listeners: {
                    success: {
                        fn: function(response) {
                            MODx.msg.status({
                                title: 'DnepritLoyalty',
                                message: response.message
                            });
                        },
                        scope: this
                    }
                }
            });
        }
    }
);

Ext.reg(
    'dnepritloyalty-panel-settings',
    DnepritLoyalty.panel.Settings
);
