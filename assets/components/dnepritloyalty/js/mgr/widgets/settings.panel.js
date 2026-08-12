DnepritLoyalty.panel.Settings = function(config) {
    config = config || {};

    var viewport = Ext.getBody().getViewSize();
    var panelHeight = Math.max(440, viewport.height - 255);
    var formWidth = Math.max(620, Math.min(1040, viewport.width - 170));

    Ext.applyIf(config, {
        id: 'dnepritloyalty-panel-settings',
        url: DnepritLoyalty.config.connectorUrl,
        baseParams: {
            action: 'settings/save'
        },
        border: false,
        autoHeight: false,
        autoScroll: true,
        height: panelHeight,
        labelAlign: 'top',
        bodyStyle: 'padding: 20px 22px 34px 22px; overflow-x: hidden; overflow-y: auto;',
        cls: 'dnepritloyalty-settings-panel',
        tbar: new Ext.Toolbar({
            height: 54,
            cls: 'dnepritloyalty-settings-toolbar',
            style: 'padding: 9px 10px; background: #ffffff;',
            items: [
                '->',
                {
                    text: _('dnepritloyalty_save'),
                    cls: 'primary-button',
                    minWidth: 155,
                    handler: function() {
                        this.submit({});
                    },
                    scope: this
                },
                {
                    xtype: 'tbspacer',
                    width: 10
                },
                {
                    text: _('dnepritloyalty_reload'),
                    minWidth: 130,
                    handler: function() {
                        this.loadSettings();
                    },
                    scope: this
                },
                {
                    xtype: 'tbspacer',
                    width: 6
                }
            ]
        }),
        items: [
            {
                xtype: 'panel',
                border: false,
                width: formWidth,
                cls: 'dnepritloyalty-settings-intro',
                html:
                    '<div class="dnepritloyalty-settings-intro-box">' +
                        '<strong>' + _('dnepritloyalty_settings_title') + '</strong>' +
                        '<div>' + _('dnepritloyalty_settings_intro') + '</div>' +
                    '</div>'
            },
            {
                xtype: 'fieldset',
                title: _('dnepritloyalty_settings_general'),
                width: formWidth,
                cls: 'dnepritloyalty-settings-fieldset',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'xcheckbox',
                        name: 'enabled',
                        inputValue: 1,
                        uncheckedValue: 0,
                        boxLabel: _('dnepritloyalty_settings_enabled')
                    },
                    {
                        xtype: 'numberfield',
                        name: 'point_value',
                        fieldLabel: _('dnepritloyalty_settings_point_value'),
                        decimalPrecision: 4,
                        allowNegative: false,
                        minValue: 0.0001
                    },
                    {
                        xtype: 'textfield',
                        name: 'allowed_groups',
                        fieldLabel: _('dnepritloyalty_settings_allowed_groups'),
                        emptyText: _('dnepritloyalty_settings_ids_hint')
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: _('dnepritloyalty_settings_earning'),
                width: formWidth,
                cls: 'dnepritloyalty-settings-fieldset',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'numberfield',
                        name: 'order_reward_percent',
                        fieldLabel: _('dnepritloyalty_settings_order_reward_percent'),
                        decimalPrecision: 3,
                        allowNegative: false,
                        minValue: 0,
                        maxValue: 100
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_order_for_reward',
                        fieldLabel: _('dnepritloyalty_settings_min_order_for_reward'),
                        decimalPrecision: 2,
                        allowNegative: false,
                        minValue: 0
                    },
                    {
                        xtype: 'textfield',
                        name: 'reward_statuses',
                        fieldLabel: _('dnepritloyalty_settings_reward_statuses'),
                        emptyText: _('dnepritloyalty_settings_ids_hint')
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: _('dnepritloyalty_settings_spending'),
                width: formWidth,
                cls: 'dnepritloyalty-settings-fieldset',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'xcheckbox',
                        name: 'spend_enabled',
                        inputValue: 1,
                        uncheckedValue: 0,
                        boxLabel: _('dnepritloyalty_settings_spend_enabled')
                    },
                    {
                        xtype: 'numberfield',
                        name: 'max_spend_percent',
                        fieldLabel: _('dnepritloyalty_settings_max_spend_percent'),
                        decimalPrecision: 2,
                        allowNegative: false,
                        minValue: 0,
                        maxValue: 100
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_spend_points',
                        fieldLabel: _('dnepritloyalty_settings_min_spend_points'),
                        decimalPrecision: 2,
                        allowNegative: false,
                        minValue: 0
                    },
                    {
                        xtype: 'numberfield',
                        name: 'min_order_for_spend',
                        fieldLabel: _('dnepritloyalty_settings_min_order_for_spend'),
                        decimalPrecision: 2,
                        allowNegative: false,
                        minValue: 0
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: _('dnepritloyalty_settings_lifetime'),
                width: formWidth,
                cls: 'dnepritloyalty-settings-fieldset',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'numberfield',
                        name: 'discount_min_order',
                        fieldLabel: _('dnepritloyalty_settings_discount_min_order'),
                        decimalPrecision: 2,
                        allowNegative: false,
                        minValue: 0
                    },
                    {
                        xtype: 'textfield',
                        name: 'lifetime_statuses',
                        fieldLabel: _('dnepritloyalty_settings_lifetime_statuses'),
                        emptyText: _('dnepritloyalty_settings_ids_hint')
                    },
                    {
                        xtype: 'textfield',
                        name: 'lifetime_from',
                        fieldLabel: _('dnepritloyalty_settings_lifetime_from'),
                        emptyText: 'YYYY-MM-DD'
                    },
                    {
                        xtype: 'textfield',
                        name: 'lifetime_to',
                        fieldLabel: _('dnepritloyalty_settings_lifetime_to'),
                        emptyText: 'YYYY-MM-DD'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'sort_order',
                        fieldLabel: _('dnepritloyalty_settings_sort_order'),
                        allowDecimals: false,
                        allowNegative: false,
                        minValue: 0
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: _('dnepritloyalty_settings_orders'),
                width: formWidth,
                cls: 'dnepritloyalty-settings-fieldset',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    {
                        xtype: 'textfield',
                        name: 'cancel_statuses',
                        fieldLabel: _('dnepritloyalty_settings_cancel_statuses'),
                        emptyText: _('dnepritloyalty_settings_ids_hint')
                    },
                    {
                        xtype: 'displayfield',
                        cls: 'dnepritloyalty-settings-help',
                        value: _('dnepritloyalty_settings_status_help')
                    }
                ]
            }
        ],
        listeners: {
            afterrender: {
                fn: function() {
                    this.syncPanelHeight();
                    this.loadSettings();
                },
                scope: this,
                single: true
            },
            success: {
                fn: function(response) {
                    MODx.msg.status({
                        title: _('success'),
                        message: response.message || _('dnepritloyalty_settings_saved')
                    });
                    this.loadSettings();
                },
                scope: this
            }
        }
    });

    DnepritLoyalty.panel.Settings.superclass.constructor.call(this, config);
};

Ext.extend(
    DnepritLoyalty.panel.Settings,
    MODx.FormPanel,
    {
        syncPanelHeight: function() {
            var viewport = Ext.getBody().getViewSize();
            var height = Math.max(440, viewport.height - 255);

            this.setHeight(height);

            if (this.body) {
                this.body.setStyle('overflow-y', 'auto');
                this.body.setStyle('overflow-x', 'hidden');
            }

            this.doLayout();
        },

        loadSettings: function() {
            MODx.Ajax.request({
                url: DnepritLoyalty.config.connectorUrl,
                params: {
                    action: 'settings/get'
                },
                listeners: {
                    success: {
                        fn: function(response) {
                            this.getForm().setValues(
                                this.getResponseObject(response)
                            );
                        },
                        scope: this
                    },
                    failure: {
                        fn: function(response) {
                            MODx.msg.alert(
                                _('error'),
                                response.message || _('dnepritloyalty_settings_load_error')
                            );
                        },
                        scope: this
                    }
                }
            });
        },

        getResponseObject: function(response) {
            if (!response) {
                return {};
            }

            if (response.object) {
                return response.object;
            }

            if (response.result && response.result.object) {
                return response.result.object;
            }

            if (response.responseText) {
                try {
                    var decoded = Ext.decode(response.responseText);

                    return decoded.object ||
                        (decoded.result && decoded.result.object) ||
                        decoded;
                } catch (e) {
                    return {};
                }
            }

            return {};
        }
    }
);

Ext.reg(
    'dnepritloyalty-panel-settings',
    DnepritLoyalty.panel.Settings
);
