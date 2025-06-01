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

## Environment Configuration

The plugin automatically detects development environments and switches endpoints accordingly:

### Automatic Detection
Development environments are detected when your site URL contains:
- `localhost`
- `.test`
- `.local`
- `staging`
- `dev`

### Manual Override
You can manually set the environment by adding one of these constants to your `wp-config.php` file:

```php
// Force development mode (uses ngrok endpoints)
define('PREPRODUCT_DEV_MODE', true);

// Force production mode (uses production endpoints)
define('PREPRODUCT_DEV_MODE', false);
```

### Debug Information
Administrators can view environment detection information by visiting:
`https://yoursite.com/?woo_preproduct_debug=environment`

## Features

- Seamless integration with PreProduct platform
- Pre-order functionality for WooCommerce products
- Automatic environment detection
- Secure and performant codebase
- Translation ready

## Support

For support and documentation, visit [PreProduct.io](https://preproduct.io)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Basic plugin structure and WooCommerce integration
- PreProduct platform integration foundation
