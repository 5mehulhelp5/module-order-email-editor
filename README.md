# ETechFlow_OrderEmailEditor

Edit the customer email address on a placed Magento order. Fix typos, handle customer-service requests, keep a complete audit trail of every change. Admin-only. Hyvä-safe by design.

Commercial eTechFlow module. Per-domain HMAC license or eTechFlow bundle key activates the module on your production host. Dev / staging / `*.magento.cloud` / `localhost` etc. auto-detect and bypass licensing.

## What it adds

- **Edit Email button** on every admin order detail page (Order & Account Information panel)
- A modal: current email shown, new email input, "also update the linked customer account" checkbox
- **Email Change History grid** at *Sales → Operations → Order Email Change History* — every change with admin user, IP, timestamp
- Atomic DB update of every place Magento stores the email (order, addresses, all 4 grid tables, optional customer record, defensive quote sync)

## Features

| | |
|---|---|
| Edit misspelled email on a placed order | ✓ |
| Update billing + shipping address rows on the order | ✓ |
| Auto-reindex Magento's order/invoice/shipment/creditmemo grid tables | ✓ |
| Optionally update the linked `customer_entity.email` | ✓ (disabled for guest orders) |
| Defensive quote-table sync if the original quote still exists | ✓ |
| Full audit log with admin user, IP, timestamp | ✓ |
| Standard Magento ACL — granular per-role permissions | ✓ |
| Per-domain HMAC licensing + bundle key support | ✓ |
| Tideways span instrumentation (`ETechFlow_OEE_UpdateOrderEmail`) | ✓ |
| Verify CLI (`etechflow:oee:verify`) | ✓ |
| No frontend dependencies (admin-only module) | ✓ |

## Compatibility

| Platform | Status |
|---|---|
| Magento Open Source 2.4.4 – 2.4.8 | ✓ |
| Adobe Commerce 2.4.4 – 2.4.8 | ✓ |
| Hyvä themes (any version) | ✓ (admin-only — Hyvä re-skins the storefront only) |
| PHP 8.1 / 8.2 / 8.3 / 8.4 | ✓ |
| MySQL 8 / MariaDB 10.6+ | ✓ |

## Installation

```bash
# Option A — Composer
composer require etechflow/module-order-email-editor:^1.0
bin/magento module:enable ETechFlow_OrderEmailEditor
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento setup:static-content:deploy -f en_GB  # production mode only
bin/magento cache:flush

# Option B — Manual drop-in
cp -r ETechFlow/OrderEmailEditor app/code/ETechFlow/OrderEmailEditor
bin/magento module:enable ETechFlow_OrderEmailEditor
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento cache:flush
```

The `setup:upgrade` step creates one new database table: `etechflow_email_change_history`.

## Licensing

**Admin → Stores → Configuration → eTechFlow → Order Email Editor → License**

| Field | Default | What it does |
|---|---|---|
| **Production Environment** | Yes | Yes = check the license key against the current domain. No = run at full features without a key (use on dev/staging on non-standard domains). |
| **License Key** | (empty) | Paste the per-domain key from your purchase email. |

If you bought the eTechFlow bundle, enter the bundle key under any module's *License* section — it activates all eTechFlow modules at once.

## Permissions (ACL)

Three new resources appear under **System → Permissions → User Roles → Role Resources**:

- `ETechFlow_OrderEmailEditor::edit_email` — required to use the modal & POST to the update endpoint
- `ETechFlow_OrderEmailEditor::view_history` — required to view the history grid
- `ETechFlow_OrderEmailEditor::config` — required to view the admin config section

By default all three are granted to *Administrators*. Assign granularly to limited roles as needed.

## Usage

1. **Admin → Sales → Orders → pick any order**
2. In the **Order & Account Information** panel you'll see an **Edit Email** button under the existing email
3. Click it. A modal opens with:
   - Current email shown for confirmation
   - New email input
   - "Also update the linked customer account" checkbox (hidden for guest orders)
4. Submit. The modal returns a success message, the email on the page updates inline, and a new row is written to `etechflow_email_change_history`

### Viewing change history

**Admin → Sales → Operations → Order Email Change History** (or the URL `/admin/order_email_editor/history/index`)

Standard Magento UI Component grid with filterable columns: increment ID, old email, new email, admin who changed it, customer-record-updated flag, IP, timestamp.

## Smoke test

After installing, confirm the module is healthy:

```bash
bin/magento etechflow:oee:verify
```

Should print `✅ ALL CHECKS PASSED. v1.0.0 verified.`

## What this module touches in the database

When the **Change Email** button is clicked, the module updates these tables in a single transaction:

| Table | Column | How |
|---|---|---|
| `sales_order` | `customer_email` | via `OrderRepository::save()` |
| `sales_order_address` | `email` (both billing + shipping rows) | via `OrderRepository::save()` |
| `sales_order_grid` | `customer_email` | **auto** (Magento's `sales_order_save_after` observer reindexes) |
| `sales_invoice_grid` | `customer_email` | auto, same observer |
| `sales_creditmemo_grid` | `customer_email` | auto, same observer |
| `sales_shipment_grid` | `customer_email` | auto, same observer |
| `customer_entity` | `email` | **only if** checkbox is on AND `sales_order.customer_id` is set |
| `quote` + `quote_address` | `customer_email` / `email` | defensive — only if the original quote row still exists |
| `etechflow_email_change_history` | new row | inserted with old/new email, admin info, IP |

## Uninstall

```bash
bin/magento module:disable ETechFlow_OrderEmailEditor
# Optionally drop the history table:
mysql -e "DROP TABLE IF EXISTS etechflow_email_change_history" $DB
# If installed via Composer:
composer remove etechflow/module-order-email-editor
rm -rf app/code/ETechFlow/OrderEmailEditor   # if installed manually
bin/magento setup:upgrade
bin/magento cache:flush
```

## License

Proprietary — see `LICENSE.txt`. Commercial licenses available at <https://etechflow.com>.
