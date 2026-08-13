DnepritLoyalty.grid.Accounts = function(config) {
    config = config || {};

    var selectionModel = new Ext.grid.CheckboxSelectionModel({
        singleSelect: false
    });

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
        sm: selectionModel,
        columns: [
            selectionModel,
            {
                header: 'ID',
                dataIndex: 'user_id',
                width: 55
            },
            {
                header: 'Клиент',
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
                header: 'Уровень',
                dataIndex: 'level_title',
                width: 110
            },
            {
                header: 'Скидка',
                dataIndex: 'discount_value',
                width: 80
            },
            {
                header: 'Обновлено',
                dataIndex: 'updated_at',
                width: 135
            }
        ],
        tbar: [
            {
                xtype: 'textfield',
                id: 'dnepritloyalty-accounts-search',
                emptyText: 'Поиск...',
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
                text: _('dnepritloyalty_sync_office_customers'),
                cls: 'primary-button',
                handler: this.syncOfficeCustomers,
                scope: this
            },
            {
                text: 'Открыть пользователя',
                handler: this.openUser,
                scope: this
            },
            {
                text: 'Пересчитать покупки',
                handler: this.recalculate,
                scope: this
            },
            {
                text: _('dnepritloyalty_adjust'),
                handler: this.adjustBalance,
                scope: this
            }
        ]
    });

    DnepritLoyalty.grid.Accounts.superclass.constructor.call(
        this,
        config
    );

    this.on(
        'rowdblclick',
        function(grid, rowIndex) {
            grid.getSelectionModel().selectRow(rowIndex, false);
            grid.openUser();
        },
        this
    );
};

Ext.extend(
    DnepritLoyalty.grid.Accounts,
    MODx.grid.Grid,
    {
        selectedRows: function() {
            if (
                this.menu &&
                this.menu.record
            ) {
                return [this.menu.record];
            }

            return this.getSelectionModel().getSelections();
        },

        selectedRow: function() {
            var rows = this.selectedRows();

            return rows.length ? rows[0] : false;
        },

        requireSingleSelectedRow: function() {
            var rows = this.selectedRows();

            if (!rows.length) {
                MODx.msg.alert(
                    'Внимание',
                    'Выберите клиента галочкой или строкой.'
                );

                return false;
            }

            if (rows.length > 1) {
                MODx.msg.alert(
                    'Внимание',
                    'Для этого действия выберите только одного клиента.'
                );

                return false;
            }

            return rows[0];
        },

        getMenu: function() {
            return [
                {
                    text: 'Открыть пользователя',
                    handler: this.openUser,
                    scope: this
                },
                '-',
                {
                    text: 'Пересчитать покупки',
                    handler: this.recalculate,
                    scope: this
                },
                {
                    text: _('dnepritloyalty_adjust'),
                    handler: this.adjustBalance,
                    scope: this
                }
            ];
        },

        syncOfficeCustomers: function() {
            MODx.Ajax.request({
                url: DnepritLoyalty.config.connectorUrl,
                params: {
                    action: 'accounts/sync'
                },
                listeners: {
                    success: {
                        fn: function(response) {
                            MODx.msg.status({
                                title: _('dnepritloyalty'),
                                message: response.message ||
                                    _('dnepritloyalty_sync_done')
                            });

                            this.refresh();
                        },
                        scope: this
                    },
                    failure: {
                        fn: function(response) {
                            MODx.msg.alert(
                                _('error'),
                                response.message ||
                                    _('dnepritloyalty_sync_failed')
                            );
                        },
                        scope: this
                    }
                }
            });
        },

        openUser: function() {
            var row = this.requireSingleSelectedRow();

            if (!row) {
                return;
            }

            MODx.loadPage(
                'security/user/update',
                'id=' + parseInt(row.data.user_id, 10)
            );
        },

        adjustBalance: function() {
            var row = this.requireSingleSelectedRow();

            if (!row) {
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
            var rows = this.selectedRows();

            if (!rows.length) {
                MODx.msg.alert(
                    'Внимание',
                    'Выберите одного или несколько клиентов.'
                );

                return;
            }

            var grid = this;
            var total = rows.length;
            var completed = 0;
            var successCount = 0;
            var failedCount = 0;

            var finish = function() {
                completed++;

                if (completed < total) {
                    return;
                }

                grid.refresh();

                if (failedCount > 0) {
                    MODx.msg.alert(
                        'Пересчёт завершён',
                        'Успешно: ' + successCount +
                            '. Ошибок: ' + failedCount + '.'
                    );
                } else {
                    MODx.msg.status({
                        title: _('dnepritloyalty'),
                        message: 'Покупки пересчитаны для клиентов: ' +
                            successCount + '.'
                    });
                }
            };

            Ext.each(
                rows,
                function(row) {
                    MODx.Ajax.request({
                        url: DnepritLoyalty.config.connectorUrl,
                        params: {
                            action: 'accounts/recalculate',
                            user_id: row.data.user_id
                        },
                        listeners: {
                            success: {
                                fn: function() {
                                    successCount++;
                                    finish();
                                },
                                scope: grid
                            },
                            failure: {
                                fn: function() {
                                    failedCount++;
                                    finish();
                                },
                                scope: grid
                            }
                        }
                    });
                }
            );
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
        title: 'Изменить бонусный баланс',
        url: DnepritLoyalty.config.connectorUrl,
        action: 'transactions/adjust',
        fields: [
            {
                xtype: 'hidden',
                name: 'user_id'
            },
            {
                xtype: 'displayfield',
                fieldLabel: 'Клиент',
                name: 'fullname'
            },
            {
                xtype: 'numberfield',
                fieldLabel: 'Сумма (+/-)',
                name: 'amount',
                allowBlank: false,
                decimalPrecision: 2
            },
            {
                xtype: 'textarea',
                fieldLabel: 'Комментарий',
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
