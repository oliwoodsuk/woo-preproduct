# PreProduct for WooCommerce

Enables pre-order functionality for WooCommerce stores via PreProduct integration.

## Description

Smarter WooCommerce pre-orders. Charge upfront, later or both (via deposits), add a pre-order area to your cart page, automations available as well as fine-grained control.

## Requirements

- WordPress 5.2 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the plugin files to the `/wp-content/plugins/woo-preproduct` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure WooCommerce is installed and activated
4. You'll then be able to access the PreProduct admin via the WooCommerce menu in your WordPress admin

## Development

You can add a symlink to your test stores wp-content/plugins/ directory to test the plugin. W
Example below where the first string is the dir you pulled the repo in to.

```bash
ln -s "/Users/oli/Mac/Apps/woo-preproduct" "/Users/oli/Local Sites/preproduct-test/app/public/wp-content/plugins/woo-preproduct"
```


## Environment Configuration

The plugin automatically detects development environments and switches endpoints accordingly:

### Automatic Detection
Development environments are detected when your site URL contains:
- `localhost` (including subdomains like `mystore.localhost`)

All other domains are treated as production, including:
- `.test` domains (e.g., `mystore.test`)
- `.local` domains (e.g., `mystore.local`) 
- Staging subdomains (e.g., `staging.mystore.com`)
- Dev subdomains (e.g., `dev.mystore.com`)
- IP addresses (e.g., `127.0.0.1`, `192.168.1.100`)

### Manual Override
You can manually set the environment by adding this constant to your `wp-config.php` file:

```php
// Force development mode (uses ngrok endpoints)
define('PREPRODUCT_DEV_MODE', true);
```

### Environment Endpoints
- **Development**: Uses `https://preproduct.ngrok.io` for all API endpoints
- **Production**: Uses `https://api.preproduct.io` for all API endpoints

## Features

- **Environment Detection**: Automatically switches between development and production endpoints
- **Button Tagging**: Adds PreProduct data attributes to simple product add-to-cart buttons on collection pages (shop, category pages)
- **WooCommerce Integration**: Seamless integration with WooCommerce product loops and collection pages
- **Simple Product Focus**: Only tags simple products, excluding variable, grouped, and external products
- **Safe Implementation**: Graceful handling of edge cases and invalid data
- **Extensible**: Filter hooks for customizing PreProduct integration per product

## Running Tests

To run tests, you can use the following command from within the plugin directory:

```bash
 ./test
```

## Zipping

To zip the plugin without any unnecessary files, run the following command from within the plugin directory:

```bash
./zip
```


## Support

For support and documentation, visit [PreProduct.io](https://preproduct.io)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Basic plugin structure and WooCommerce integration
- PreProduct platform integration foundation
