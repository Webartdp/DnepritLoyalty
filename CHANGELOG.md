# Changelog

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
