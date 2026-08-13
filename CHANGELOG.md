# Changelog

## [0.1.0-beta4] - 2026-08-13

### Fixed

- Added an explicit checkbox selection column to the Customers grid.
- Added **Open user** action for the selected Office/modUser customer.
- Added right-click context menu with Open user, Recalculate purchases and Adjust balance actions.
- Double-clicking a customer opens the corresponding MODX user profile.
- Synchronization and Lifetime recalculation failures now show a visible manager error instead of failing silently.
- Customer action buttons now explain when no customer has been selected.

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
