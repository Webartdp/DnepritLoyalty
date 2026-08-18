# Changelog

## [0.1.0-beta25] - 2026-08-18

### Fixed

- Fixed `object_err_ns` when editing Lifetime level fields directly in the Levels grid.
- MODX grid autosave sends the edited row as JSON in the `data` parameter; the level update processor now decodes that payload before `modObjectUpdateProcessor` resolves the object by `id`.
- Applied the same fix to Rules grid inline editing because it uses the same autosave mechanism.
- No checkout, bonus calculation, Lifetime calculation, order status or cancellation behavior was changed from beta23.

### Status

- beta23 remains the previously validated functional baseline.
- beta25 is beta23 plus the manager inline-edit bugfix and should replace beta23 after the level threshold edit is verified on the shop.
- beta24 experimental reward-range work was not adopted and is not part of beta25.

## [0.1.0-beta23] - 2026-08-17

### Validated stable baseline

- Customer synchronization and manager customer list are working.
- Lifetime purchase recalculation is working against configured miniShop2 statuses.
- Automatic loyalty levels and discounts are working.
- Customer editing inside the DnepritLoyalty manager is working.
- Direct bonus balance adjustment remains transaction-based to preserve audit history.
- Bonus spending in checkout is working.
- Checkout totals update live when bonus points are selected.
- The final miniShop2 order cost is stored with the loyalty discount and bonus spend applied.
- Bonus earning after the configured paid/order status is working.
- Bonus spend is returned correctly when an order is moved to a configured cancellation status.
- Manager JavaScript namespace initialization was hardened with `DnepritLoyalty.combo` support so the customer edit window and level combo register reliably.

### Notes

- beta23 is the tested baseline for further development.
- Deleting an order is not treated as the same workflow as changing an order to a cancellation status; cancellation-status handling is the validated refund path.

## [0.1.0-beta7] - 2026-08-13

### Fixed

- Bulk **Recalculate purchases** now sends one manager request for all selected customers instead of one parallel request per row.
- Added `accounts/recalculatebulk` processor that recalculates selected customers sequentially inside one PHP request.
- Bulk recalculation returns one summary with requested, successful and failed customer counts.
- The manager no longer floods the MODX connector with simultaneous requests, preventing the 508/error-dialog cascade seen during Select All recalculation.

## [0.1.0-beta6] - 2026-08-13

### Fixed

- Bulk **Recalculate purchases** no longer assumes every selected row has a `row.data` object.
- Customer rows are normalized before reading `user_id`, so Ext Records and raw context-menu records are both supported.
- Toolbar actions ignore stale hidden context-menu records and use the actual checkbox selection.
- Invalid selected rows are counted as failures instead of throwing a JavaScript exception.
- Selection is cleared after a completed bulk recalculation and the grid is refreshed once.

## [0.1.0-beta5] - 2026-08-13

### Fixed

- Customer checkbox selection is now true multi-select instead of `singleSelect` mode.
- The header checkbox now selects and clears all customer rows on the current grid page.
- **Recalculate purchases** works for all selected customers, including a Select All selection.
- **Open user** and **Adjust balance** require exactly one selected customer and show a clear warning otherwise.

## [0.1.0-beta4] - 2026-08-13

### Fixed

- Added an explicit checkbox selection column to the Customers grid.
- Added **Open user** action for the selected Office/modUser customer.
- Added right-click context menu with Open user, Recalculate purchases and Adjust balance actions.
- Double-clicking a customer opens the MODX user edit page.
- Synchronization and Lifetime recalculation failures are shown visibly instead of failing silently.
- Customer action buttons explain when no customer has been selected.
- Package upgrades no longer overwrite existing `dnepritloyalty.*` system-setting values saved for the shop.

## [0.1.0-beta3] - 2026-08-13

### Added

- Office customer synchronization from configured MODX user groups.
- New **Synchronize Office customers** action in the Customers tab.
- Idempotent account creation: existing `DnepritLoyaltyAccount` rows are preserved and never duplicated.
- Synchronization summary with matched, created, existing and failed counters.
- Russian and Ukrainian manager lexicon strings for the synchronization flow.
- CI regression checks for the Office synchronization processor and manager action.

### Changed

- Customer synchronization uses `dnepritloyalty.allowed_groups` as the source of truth, so administrators and unrelated MODX users are not imported unless their group is explicitly configured.
- Synchronization only creates loyalty accounts. It does not recalculate Lifetime totals, award bonuses or modify existing balances.

## [0.1.0-beta2] - 2026-08-12

### Fixed

- Rebuilt the Settings tab as a real `MODx.FormPanel` instead of a nested form that could collapse to zero height in the manager.
- Settings now load visibly and can be saved from a persistent top toolbar.
- Added explicit vertical scrolling for long settings forms.
- Added responsive form width so fields no longer stretch across the entire manager viewport.
- Added normal spacing between labels, fields, sections and action buttons.
- Replaced boolean combo boxes with manager checkboxes for clearer on/off controls.
- Added grouped sections for general settings, bonus earning, bonus spending, Lifetime Discount and order statuses.
- Added Russian and Ukrainian manager lexicon strings for the settings screen.
- Added CI regression checks for the settings panel structure and manager CSS.

## [0.1.0-beta1] - 2026-08-12

### Added

- Bonus accounts with available and reserved balance.
- Immutable loyalty transaction history with idempotent unique keys.
- Lifetime purchase total recalculation from selected miniShop2 statuses and date range.
- Percentage and fixed Lifetime Discount levels.
- Bonus earning from paid orders.
- One-time registration and first-order rules.
- Atomic bonus reservation during order creation and release on cancelled orders.
- Configurable maximum bonus-payment percentage and minimum order values.
- Customer-group restrictions.
- Manager CMP for customers, transactions, levels, rules and settings.
- Public account, balance and checkout snippets.
- miniShop2 event integration and prepared DnepritNewsletter subscription hook.
