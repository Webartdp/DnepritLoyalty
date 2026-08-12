DnepritLoyalty.grid.Transactions = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        url: DnepritLoyalty.config.connectorUrl,
        baseParams: {
            action: 'transactions/getlist'
        },
        fields: [
            'id',
            'user_id',
            'fullname',
            'email',
            'order_id',
            'amount',
            'type',
            'status',
            'description',
            'unique_key',
            'created_at'
        ],
        paging: true,
        remoteSort: true,
        columns: [
            {
                header: 'ID',
                dataIndex: 'id',
                width: 55
            },
            {
                header: 'Клієнт',
                dataIndex: 'email',
                width: 190
            },
            {
                header: 'Замовлення',
                dataIndex: 'order_id',
                width: 80
            },
            {
                header: 'Сума',
                dataIndex: 'amount',
                width: 85
            },
            {
                header: 'Тип',
                dataIndex: 'type',
                width: 120
            },
            {
                header: 'Статус',
                dataIndex: 'status',
                width: 90
            },
            {
                header: 'Опис',
                dataIndex: 'description',
                width: 240
            },
            {
                header: 'Дата',
                dataIndex: 'created_at',
                width: 140
            }
        ]
    });

    DnepritLoyalty.grid.Transactions.superclass.constructor.call(
        this,
        config
    );
};

Ext.extend(
    DnepritLoyalty.grid.Transactions,
    MODx.grid.Grid
);

Ext.reg(
    'dnepritloyalty-grid-transactions',
    DnepritLoyalty.grid.Transactions
);
