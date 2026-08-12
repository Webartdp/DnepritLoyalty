# DnepritLoyalty release

Current prerelease:

```text
Version: 0.1.0-beta1
Package: dnepritloyalty-0.1.0-beta1.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2
```

Beta1 is intentionally conservative: order status IDs are not guessed. Configure eligible and cancellation statuses after installation.

Before production use verify:

1. authorized checkout;
2. lifetime status mapping;
3. reward status mapping;
4. cancellation status mapping;
5. reservation and release of spent bonuses;
6. reward idempotency after repeated status events;
7. fixed and percentage levels;
8. manual balance adjustment;
9. user-group restrictions.
