# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta6
Package: dnepritloyalty-0.1.0-beta6.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
```

Beta6 fixes bulk Lifetime recalculation for selected Office customers.

Primary beta6 changes:

1. Customers grid keeps true multi-select checkbox behavior.
2. Bulk **Recalculate purchases** normalizes selected Ext records before reading `user_id`.
3. Raw context-menu records and normal Ext Records are both supported.
4. Hidden stale context-menu records no longer override the toolbar checkbox selection.
5. Invalid rows are counted as failed instead of crashing the manager JavaScript.
6. The grid refreshes after the whole batch finishes.

For the current shop configuration use:

```text
dnepritloyalty.allowed_groups = 1000000000
dnepritloyalty.lifetime_statuses = 2,3
dnepritloyalty.reward_statuses   = 2,3
dnepritloyalty.cancel_statuses   = 4,1001
```

Keep the loyalty program and bonus spending disabled while validating historical purchase totals.

Next validation step:

1. select one or all synchronized Office customers;
2. run **Recalculate purchases**;
3. verify the manager reports how many customers were recalculated;
4. compare each Lifetime total with that customer's paid/sent miniShop2 orders.
