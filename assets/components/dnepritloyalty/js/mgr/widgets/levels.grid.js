DnepritLoyalty.grid.Levels = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        url: DnepritLoyalty.config.connectorUrl,
        baseParams: {
            action: 'levels/getlist'
        },
        fields: [
            'id',
            'title',
            'threshold',
            'discount_type',
            'discount_value',
            'earn_percent',
            'active',
            'rank'
        ],
        paging: true,
        autosave: true,
        save_action: 'levels/update',
        columns: [
            {
                header: 'Назва',
                dataIndex: 'title',
                width: 160,
                editor: {
                    xtype: 'textfield'
                }
            },
            {
                header: 'Поріг',
                dataIndex: 'threshold',
                width: 100,
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2
                }
            },
            {
                header: 'Тип',
                dataIndex: 'discount_type',
                width: 90,
                editor: {
                    xtype: 'combo',
                    store: [
                        'percent',
                        'fixed'
                    ],
                    mode: 'local',
                    triggerAction: 'all'
                }
            },
            {
                header: 'Знижка',
                dataIndex: 'discount_value',
                width: 90,
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2
                }
            },
            {
                header: '% бонусів',
                dataIndex: 'earn_percent',
                width: 90,
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 3
                }
            },
            {
                header: 'Активний',
                dataIndex: 'active',
                width: 75,
                editor: {
                    xtype: 'combo-boolean'
                },
                renderer: function(v) {
                    return v ? 'Так' : 'Ні';
                }
            },
            {
                header: 'Порядок',
                dataIndex: 'rank',
                width: 75,
                editor: {
                    xtype: 'numberfield'
                }
            }
        ],
        tbar: [
            {
                text: 'Додати рівень',
                handler: this.createLevel,
                scope: this
            }
        ]
    });

    DnepritLoyalty.grid.Levels.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.grid.Levels,
    MODx.grid.Grid,
    {
        createLevel: function() {
            var win = MODx.load({
                xtype: 'dnepritloyalty-window-level-create',
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                        },
                        scope: this
                    }
                }
            });

            win.show();
        }
    }
);

Ext.reg(
    'dnepritloyalty-grid-levels',
    DnepritLoyalty.grid.Levels
);

DnepritLoyalty.window.LevelCreate = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: 'Новий рівень',
        url: DnepritLoyalty.config.connectorUrl,
        action: 'levels/create',
        fields: [
            {
                xtype: 'textfield',
                name: 'title',
                fieldLabel: 'Назва',
                allowBlank: false,
                anchor: '100%'
            },
            {
                xtype: 'numberfield',
                name: 'threshold',
                fieldLabel: 'Поріг',
                decimalPrecision: 2,
                value: 0
            },
            {
                xtype: 'combo',
                name: 'discount_type',
                hiddenName: 'discount_type',
                fieldLabel: 'Тип',
                store: [
                    'percent',
                    'fixed'
                ],
                mode: 'local',
                triggerAction: 'all',
                value: 'percent'
            },
            {
                xtype: 'numberfield',
                name: 'discount_value',
                fieldLabel: 'Знижка',
                decimalPrecision: 2,
                value: 0
            },
            {
                xtype: 'numberfield',
                name: 'earn_percent',
                fieldLabel: '% бонусів',
                decimalPrecision: 3,
                value: 0
            },
            {
                xtype: 'combo-boolean',
                name: 'active',
                hiddenName: 'active',
                fieldLabel: 'Активний',
                value: 1
            }
        ]
    });

    DnepritLoyalty.window.LevelCreate.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.window.LevelCreate,
    MODx.Window
);

Ext.reg(
    'dnepritloyalty-window-level-create',
    DnepritLoyalty.window.LevelCreate
);
