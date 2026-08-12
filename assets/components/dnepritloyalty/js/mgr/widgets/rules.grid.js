DnepritLoyalty.grid.Rules = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        url: DnepritLoyalty.config.connectorUrl,
        baseParams: {
            action: 'rules/getlist'
        },
        fields: [
            'id',
            'key',
            'title',
            'enabled',
            'amount',
            'once_only',
            'description'
        ],
        paging: false,
        autosave: true,
        save_action: 'rules/update',
        columns: [
            {
                header: 'Ключ',
                dataIndex: 'key',
                width: 120
            },
            {
                header: 'Правило',
                dataIndex: 'title',
                width: 190
            },
            {
                header: 'Увімкнено',
                dataIndex: 'enabled',
                width: 85,
                editor: {
                    xtype: 'combo-boolean'
                },
                renderer: function(v) {
                    return v ? 'Так' : 'Ні';
                }
            },
            {
                header: 'Бонуси',
                dataIndex: 'amount',
                width: 90,
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2
                }
            },
            {
                header: 'Один раз',
                dataIndex: 'once_only',
                width: 85,
                editor: {
                    xtype: 'combo-boolean'
                },
                renderer: function(v) {
                    return v ? 'Так' : 'Ні';
                }
            },
            {
                header: 'Опис',
                dataIndex: 'description',
                width: 260
            }
        ]
    });

    DnepritLoyalty.grid.Rules.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.grid.Rules,
    MODx.grid.Grid
);

Ext.reg(
    'dnepritloyalty-grid-rules',
    DnepritLoyalty.grid.Rules
);
