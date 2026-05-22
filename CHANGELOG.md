# Changelog — ETechFlow Order Email Editor

All notable changes to this module. Adheres to [Semantic Versioning](https://semver.org/).

---

## [1.0.2] — 2026-05-22 — Move admin menu under eTechFlow top-level sidebar

### Changed

- **OEE admin pages relocated to a dedicated "eTechFlow" sidebar entry.** Previously the Edit History list lived under `Sales → Sales Operation`. Now it sits as an `Order Email Editor` column inside a new top-level `eTechFlow` sidebar entry (clusters with other paid-extension vendors above Magento's Stores). Matches the pattern Amasty / Magefan / MageWorx use.
- Each eTechFlow module declares the same `eTechFlow::root` + `eTechFlow::settings` + `eTechFlow::configuration` entries — Magento merges by id, so installing N modules still produces exactly one `eTechFlow` sidebar group.

### Migration

```
composer update etechflow/module-order-email-editor
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

Admin URL routes unchanged (`order_email_editor/history/index` still works). No schema or behaviour changes — pure menu-layout adjustment.

---

## [1.0.0] — 2026-05-19

### Initial commercial release

Edit the customer email on a placed order. Fix typos, handle customer requests, keep an audit trail. Admin-only. Hyvä-safe by design.

#### Added

- **Edit Email button** on every order detail page (admin → Sales → Orders → pick order). Opens a modal: current email shown, new email input, optional "also update linked customer account" checkbox (hidden for guest orders).
- **Email Change History grid** at admin → Sales → Operations → Order Email Change History. Filterable Magento UI Component grid of every change made.
- **Atomic DB update** of all the places Magento stores the order email:
  - `sales_order.customer_email`
  - `sales_order_address.email` (both billing + shipping rows)
  - `sales_order_grid` + `sales_invoice_grid` + `sales_creditmemo_grid` + `sales_shipment_grid` (via Magento's core `sales_order_save_after` reindex)
  - `customer_entity.email` (if checkbox ticked + linked customer exists)
  - `quote.customer_email` + `quote_address.email` (defensive, only if quote still exists)
- **Audit log** in `etechflow_email_change_history` — admin id, admin name, old email, new email, customer-record-updated flag, IP, timestamp.
- **Per-installation HMAC license** with bundle-key support — same as every other eTechFlow module. Unlicensed installs silently hide the Edit Email button + history menu and reject the update endpoint with 403.
- **Two ACL resources**: `edit_email` (modal + update endpoint) and `view_history` (history grid). Granular per-role permissions.
- **Profiler instrumentation** — wraps the update path in an `ETechFlow_OEE_UpdateOrderEmail` Tideways span.
- **Verify CLI** — `bin/magento etechflow:oee:verify` checks DI resolution + DB table presence.
- **Hyvä-safe** — admin-only module with zero frontend assets. Hyvä themes never touch the admin.

#### Compatibility

- Magento Open Source 2.4.4 – 2.4.8
- Adobe Commerce 2.4.4 – 2.4.8
- PHP 8.1 / 8.2 / 8.3 / 8.4
- All Hyvä child themes
