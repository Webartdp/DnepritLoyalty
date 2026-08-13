# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta4
Package: dnepritloyalty-0.1.0-beta4.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
```

Beta4 completes the customer-selection UI needed for the Office integration stage.

Primary beta4 changes:

1. Customers grid now has a visible checkbox selection column.
2. Added **Open user** action for the selected MODX/Office customer.
3. Right-click context menu provides Open user, Recalculate purchases and Adjust balance actions.
4. Double-clicking a customer opens the MODX user edit page.
5. Synchronization and Lifetime recalculation failures are shown visibly instead of failing silently.
6. Customer actions explain when no row is selected.

For the current shop configuration use:

```text
dnepritloyalty.allowed_groups = 1000000000
dnepritloyalty.lifetime_statuses = 2,3
dnepritloyalty.reward_statuses   = 2,3
dnepritloyalty.cancel_statuses   = 4,1001
```

Keep the loyalty program and bonus spending disabled while validating historical purchase totals.

Next validation step:

1. select one synchronized Office customer;
2. open the MODX user profile and verify the identity;
3. run **Recalculate purchases**;
4. compare the resulting Lifetime total with that customer's paid/sent miniShop2 orders.
