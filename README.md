# Comerix AI Assistant for Magento 2

Integrates a third-party AI Assistant chat widget into the Magento 2 storefront and
keeps an external chat/search server in sync with your catalog and CMS content.

The module:

- Injects the AI Assistant widget script into frontend pages, with optional contextual
  widgets on product, category, and cart pages.
- Pushes product and CMS page changes to an external chat server for (re)indexing,
  both automatically (on save/delete) and on demand from the admin.
- Tracks recently viewed products (guest session and logged-in customer) and exposes
  them so the widget can personalise responses.
- Provides a cart restore endpoint so a masked quote can be reloaded into the session.

## Requirements

- PHP 8.1+
- Magento 2.4.x (Community or Commerce Edition)

## Installation

### Via Composer (recommended)

```bash
composer require comerix/magento2-ai-assistant
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### Manual installation

1. Create the directory `app/code/Comerix/AiAssistant`.
2. Copy the module contents into that directory.
3. Run the post-install commands:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Configuration

Go to **Stores > Configuration > Comerix > AI Assistant**.

### General

| Field | Description |
| --- | --- |
| **Enable AI Assistant** | Master switch. When *No*, no widget is injected and reindex observers are skipped. |
| **Widget Script URL** | URL of the main widget script injected before `</body>` on every frontend page. Leave empty to disable injection. |
| **Chat Server URL** | Base URL of the external chat/indexing server that receives reindex requests. |
| **Reindex Secret** | Secret key sent as the `x-reindex-secret` header to authorize reindex requests. Stored encrypted. |

### Widget Additional Configurations

These enable extra contextual widgets in addition to the main script. They share an
embedded **Widget Script URL** used by the contextual templates.

| Field | Description |
| --- | --- |
| **Widget Script URL** | Script URL used by the contextual (product/category/cart) widgets. |
| **Enable Product Page Quick Chat Widget** | Renders a quick-chat widget on product pages. |
| **Enable Category Bottom Widget** | Renders a widget at the bottom of category pages. |
| **Enable Category Sidebar Widget** | Renders a widget in the category page sidebar. |
| **Enable Cart Quick Chat Widget** | Renders a quick-chat widget on the cart page. |

### Indexes

| Button | Description |
| --- | --- |
| **Reindex Products** | Sends a request to the chat server to reindex all products. |
| **Reindex CMS Pages** | Iterates all CMS pages and sends each to the chat server. |

After changing configuration, flush the cache:

```bash
bin/magento cache:flush
```

## Catalog & CMS synchronisation

When the module is enabled, changes are pushed to the configured **Chat Server URL**
using the **Reindex Secret** for authorization. Requests are best-effort: failures are
logged and never block the Magento save/delete operation.

### Automatic (observers)

| Event | Action sent |
| --- | --- |
| `catalog_product_save_after` (only when data changed) | `POST /api/reindex-product` `{ sku, action: "save" }` |
| `catalog_product_delete_after` | `POST /api/reindex-product` `{ sku, action: "delete" }` |
| `cms_page_save_after` (only when data changed) | `POST /api/reindex-cms-page` `{ identifier, action: "save" }` |
| `cms_page_delete_after` | `POST /api/reindex-cms-page` `{ identifier, action: "delete" }` |

### On demand

- **Reindex Products** button → `POST /api/reindex-products` (full catalog reindex on the server side).
- **Reindex CMS Pages** button → one `POST /api/reindex-cms-page` per page.
- **Product grid mass action** ("Send to AiAssistant for reindex") → one `POST /api/reindex-product`
  per selected product. Requires the `Comerix_AiAssistant::config` ACL resource.

All outbound requests send `Content-Type: application/json` and the
`x-reindex-secret` header. If the chat server URL or secret is not configured, requests
are skipped silently.

## Recently viewed products

The module tracks the last 5 viewed products:

- **Guests:** captured on `catalog_product_view` and stored in the catalog session.
- **Logged-in customers:** read from Magento's `Reports` viewed-products data.

### REST API

```
GET /rest/V1/comerix_aiassistant/viewed-products
```

Returns recently viewed products for the current session, or for an explicit customer
when called with a `customerId`. Response items contain `sku`, `name`, and `price`.

The widget also receives the guest viewed-products list inline as JSON in the page
config payload.

## Cart restore endpoint

Frontend route: `/ai_assistant/restore/index/?cartId=<maskedQuoteId>`

- **`cartId`** (required) — the masked quote ID.
- Loads the matching active quote into the checkout session and redirects to
  `checkout/cart`.
- If `cartId` is missing, no matching quote exists, or the quote is inactive, the
  request is forwarded to the 404 handler and a warning is logged.

## Content Security Policy

`etc/frontend/csp_whitelist.xml` whitelists the AI Assistant host for `script-src`.
If you host the widget on a different origin, update this file accordingly.

## Logging

Module activity (reindex failures, restore events, widget errors) is written to:

```
var/log/comerix_ai_assistant.log
```

Underlying exceptions are additionally logged to the standard Magento system log.

## Uninstallation

```bash
bin/magento module:disable Comerix_AiAssistant
composer remove comerix/magento2-ai-assistant
bin/magento setup:upgrade
bin/magento cache:flush
```