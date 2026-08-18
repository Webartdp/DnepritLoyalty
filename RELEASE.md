# DnepritLoyalty release

Current bugfix candidate:

```text
Version: 0.1.0-beta25
Package: dnepritloyalty-0.1.0-beta25.transport.zip
Target: MODX Revolution 2.8.1 / PHP 7.4+ / miniShop2 / Office-compatible modUser accounts
Base: validated beta23
```

## beta25 scope

beta25 is intentionally narrow. It keeps the validated beta23 loyalty behavior and fixes manager inline editing.

- Levels grid updates now decode MODX autosave JSON before resolving the level `id`.
- Editing `threshold`, discount value/type, active state and rank no longer fails with `object_err_ns` for the autosave transport format.
- Rules grid receives the same fix because it uses the same MODX autosave mechanism.
- No bonus earning formula, checkout calculation, Lifetime calculation, order status handling or cancellation refund logic was changed.

## Baseline retained from beta23

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

## Version note

The experimental beta24 reward-range idea was discussed but not adopted. It is not included in beta25.

After installing beta25, verify one simple manager action: change a Lifetime level threshold (for example Bronze), leave the cell, and confirm the value persists after grid refresh. If that passes, beta25 becomes the new working baseline.
