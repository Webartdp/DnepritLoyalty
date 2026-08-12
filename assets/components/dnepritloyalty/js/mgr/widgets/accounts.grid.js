DnepritLoyalty.grid.Accounts = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'dnepritloyalty-grid-accounts',
        url: DnepritLoyalty.config.connectorUrl,
        baseParams: {
            action: 'accounts/getlist'
        },
        fields: [
            'id',
            'user_id',
            'fullname',
            'email',
            'username',
            'balance',
            'reserved',
            'available',
            'lifetime_total',
            'level_title',
            'discount_type',
            'discount_value',
            'updated_at'
        ],
        paging: true,
        remoteSort: true,
        autosave: false,
        columns: [
            {
                header: 'ID',
                dataIndex: 'user_id',
                width: 55
            },
            {
                header: 'Клієнт',
                dataIndex: 'fullname',
                width: 180
            },
            {
                header: 'Email',
                dataIndex: 'email',
                width: 200
            },
            {
                header: 'Баланс',
                dataIndex: 'balance',
                width: 90
            },
            {
                header: 'Резерв',
                dataIndex: 'reserved',
                width: 80
            },
            {
                header: 'Доступно',
                dataIndex: 'available',
                width: 90
            },
            {
                header: 'Покупки',
                dataIndex: 'lifetime_total',
                width: 100
            },
            {
                header: 'Рівень',
                dataIndex: 'level_title',
                width: 110
            },
            {
                header: 'Знижка',
                dataIndex: 'discount_value',
                width: 80
            },
            {
                header: 'Оновлено',
                dataIndex: 'updated_at',
                width: 135
            }
        ],
        tbar: [
            {
                xtype: 'textfield',
                id: 'dnepritloyalty-accounts-search',
                emptyText: 'Пошук...',
                listeners: {
                    change: {
                        fn: function(field) {
                            this.getStore().baseParams.query =
                                field.getValue();
                            this.getBottomToolbar().changePage(1);
                        },
                        scope: this,
                        buffer: 400
                    }
                }
            },
            '->',
            {
                text: 'Перерахувати покупки',
                handler: this.recalculate,
                scope: this
            },
            {
                text: 'Змінити баланс',
                handler: this.adjustBalance,
                scope: this
            }
        ]
    });

    DnepritLoyalty.grid.Accounts.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.grid.Accounts,
    MODx.grid.Grid,
    {
        selectedRow: function() {
            return this.getSelectionModel().getSelected();
        },

        adjustBalance: function() {
            var row = this.selectedRow();

            if (!row) {
                MODx.msg.alert(
                    'Увага',
                    'Оберіть клієнта.'
                );
                return;
            }

            var win = MODx.load({
                xtype: 'dnepritloyalty-window-adjust',
                record: row.data,
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                        },
                        scope: this
                    }
                }
            });

            win.setValues(row.data);
            win.show();
        },

        recalculate: function() {
            var row = this.selectedRow();

            if (!row) {
                MODx.msg.alert(
                    'Увага',
                    'Оберіть клієнта.'
                );
                return;
            }

            MODx.Ajax.request({
                url: DnepritLoyalty.config.connectorUrl,
                params: {
                    action: 'accounts/recalculate',
                    user_id: row.data.user_id
                },
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                        },
                        scope: this
                    }
                }
            });
        }
    }
);

Ext.reg(
    'dnepritloyalty-grid-accounts',
    DnepritLoyalty.grid.Accounts
);

DnepritLoyalty.window.Adjust = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: 'Змінити бонусний баланс',
        url: DnepritLoyalty.config.connectorUrl,
        action: 'transactions/adjust',
        fields: [
            {
                xtype: 'hidden',
                name: 'user_id'
            },
            {
                xtype: 'displayfield',
                fieldLabel: 'Клієнт',
                name: 'fullname'
            },
            {
                xtype: 'numberfield',
                fieldLabel: 'Сума (+/-)',
                name: 'amount',
                allowBlank: false,
                decimalPrecision: 2
            },
            {
                xtype: 'textarea',
                fieldLabel: 'Коментар',
                name: 'comment',
                anchor: '100%'
            }
        ]
    });

    DnepritLoyalty.window.Adjust.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.window.Adjust,
    MODx.Window
);

Ext.reg(
    'dnepritloyalty-window-adjust',
    DnepritLoyalty.window.Adjust
);
