# MageCloud AI Assistant for Magento 2

Injects a third-party AI Assistant widget script into all frontend pages.
## Requirements

- PHP 8.1+
- Magento 2.4.x (Community or Commerce Edition)

## Installation
### Via Composer (recommended)

```bash
composer require magecloud/magento2-ai-assistant
bin/magento setup:upgrade
bin/magento cache:flush
```

### Manual installation

1. Create the directory `app/code/MageCloud/AiAssistant`.
2. Copy the module contents into that directory.
3. Run the post-install commands:

```bash
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

1. Go to **Stores > Configuration > General > AI Assistant**.
2. Set **Enable AI Assistant** to *Yes*.
3. Paste the widget script URL into **Widget Script URL**.
4. Save the config and flush the cache:

```bash
bin/magento cache:flush
```

The script tag is injected just before `</body>` on every frontend page only when the module is enabled and the URL is not empty.

## Restore endpoint

The module registers a frontend route at `/ai_assistant/restore/index/`.

**Required parameter:** `cartId` — the masked quote ID.

- If `cartId` is missing or empty the request is forwarded to the 404 handler and a warning is written to the log.
- If `cartId` is present an info entry is logged and the page renders normally.

Log output goes to `var/log/system.log` by default.

## Uninstallation

```bash
bin/magento module:disable MageCloud_AiAssistant
composer remove magecloud/magento2-ai-assistant
bin/magento setup:upgrade
bin/magento cache:flush
```
