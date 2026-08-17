# DnepritLoyalty release

Current validated prerelease:

```text
Version: 0.1.0-beta23
Package: dnepritloyalty-0.1.0-beta23.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
Status: tested on the shop and accepted as the stable development baseline
```

## Verified end-to-end

- Office/modUser customer synchronization.
- Lifetime purchase recalculation from configured miniShop2 statuses.
- Loyalty levels and automatic discounts.
- Customer editing inside the DnepritLoyalty CMP.
- Transaction-based manual bonus balance adjustments.
- Bonus spending in checkout.
- Live checkout total updates while selecting bonus points.
- Persisting the reduced final cost into the created miniShop2 order.
- Bonus earning after the configured order status is reached.
- Returning spent bonus points when an order is moved to a configured cancellation status.

## Manager fix included in beta23

The manager bootstrap now initializes the combo namespace:

```javascript
DnepritLoyalty.combo = DnepritLoyalty.combo || {};
```

This prevents the level combo definition from aborting `accounts.grid.js` and ensures the DnepritLoyalty customer edit window is registered correctly.

## Development rule

Use beta23 as the known-good baseline for further changes. New work should preserve the validated order lifecycle and be tested against it before replacing this baseline.

Cancellation by status is the verified refund workflow. Physical deletion of a miniShop2 order is a separate case and must not be assumed to trigger the same refund logic.
