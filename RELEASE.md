# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta7
Package: dnepritloyalty-0.1.0-beta7.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
```

Beta7 fixes the request storm discovered during Select All Lifetime recalculation.

Primary beta7 changes:

1. Customers grid keeps true multi-select checkbox behavior.
2. Bulk **Recalculate purchases** sends one POST request with all selected `user_id` values.
3. New `accounts/recalculatebulk` processor recalculates customers sequentially inside one server request.
4. The processor returns requested, successful and failed counts in one response.
5. The manager no longer opens many simultaneous MODX Ajax failure dialogs when the server rejects parallel requests.
6. Single-customer Open user and Adjust balance behavior is unchanged.

For the current shop configuration use:

```text
dnepritloyalty.allowed_groups = 1000000000
dnepritloyalty.lifetime_statuses = 2,3
dnepritloyalty.reward_statuses   = 2,3
dnepritloyalty.cancel_statuses   = 4,1001
```

Keep the loyalty program and bonus spending disabled while validating historical purchase totals.

Next validation step:

1. select all synchronized Office customers;
2. run **Recalculate purchases** once;
3. verify Network shows one `accounts/recalculatebulk` POST request rather than one request per customer;
4. verify the manager reports successful/failed counts;
5. compare each Lifetime total with that customer's paid/sent miniShop2 orders.
