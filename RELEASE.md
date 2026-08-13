# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta3
Package: dnepritloyalty-0.1.0-beta3.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
```

Beta3 adds the first explicit Office integration step: synchronization of existing shop customers from configured MODX user groups into loyalty accounts.

Primary beta3 changes:

1. Customers tab has a **Synchronize Office customers** action.
2. Synchronization reads `dnepritloyalty.allowed_groups` and imports only members of those groups.
3. Existing loyalty accounts are preserved; repeated synchronization is idempotent.
4. Synchronization creates zero-balance loyalty accounts only. It does not award bonuses or recalculate old orders yet.
5. The manager reports how many users were found, created, already existed or failed.

For the current shop configuration use:

```text
dnepritloyalty.allowed_groups = 1000000000
```

Configured miniShop2 statuses for this shop:

```text
dnepritloyalty.lifetime_statuses = 2,3
dnepritloyalty.reward_statuses   = 2,3
dnepritloyalty.cancel_statuses   = 4,1001
```

Keep the loyalty program and bonus spending disabled while validating customer synchronization.

Before moving to the next stage verify:

1. only Office customers from the configured group are synchronized;
2. MODX administrators are not imported unless they belong to that group;
3. customer name/email are displayed from `modUserProfile`;
4. repeated synchronization creates no duplicate accounts;
5. all synchronized customers have zero balance and zero Lifetime total before the historical-order recalculation stage.
