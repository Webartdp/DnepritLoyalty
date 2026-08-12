# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta2
Package: dnepritloyalty-0.1.0-beta2.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2
```

Beta2 is the first manager-UI correction release after installing beta1 on a real MODX shop.

Primary beta2 changes:

1. Settings tab is a visible `MODx.FormPanel` and no longer collapses inside the tab.
2. Save and reload actions are always visible in the top toolbar.
3. Settings are grouped into logical fieldsets.
4. Form width is capped for readable desktop layouts instead of stretching fields across the full viewport.
5. Labels use top alignment with normal spacing between controls.
6. Long settings pages have explicit vertical scrolling.
7. Russian and Ukrainian settings lexicons are included.

Order status IDs are still intentionally not guessed. After installation configure:

```text
dnepritloyalty.lifetime_statuses
dnepritloyalty.reward_statuses
dnepritloyalty.cancel_statuses
```

Before production use verify:

1. authorized checkout;
2. lifetime status mapping;
3. reward status mapping;
4. cancellation status mapping;
5. reservation and release of spent bonuses;
6. reward idempotency after repeated status events;
7. fixed and percentage levels;
8. manual balance adjustment;
9. user-group restrictions;
10. settings loading and saving from the CMP.
