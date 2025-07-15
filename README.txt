=== PreProduct ===
Contributors: preproduct
Tags: woocommerce, pre-orders, preorders, ecommerce, inventory
Requires at least: 5.2
Tested up to: 6.8
Stable tag: 1.0.4
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


== Description ==

Level up your pre-orders and grow.
PreProduct for WooCommerce integrates your store with the PreProduct pre-order platform, enabling you to capture demand and sales before your products are available.

See [https://preproduct.io](https://preproduct.io) for more information

**Key Features:**

* **Integration** with your existing WooCommerce store, catalogue and orders.
* **Charge when you want** pre-orders Now, Later or via Deposit 
* **Control** if multiple pre-orders or single pre-order products can be checked out together.
* **Keep customers in the loop** with customisable front-end, email flows and customer portal .
* **Smoother fulfilment** by keeping pre-orders out of WooCommerce until you push the orders from PreProduct.
* **Admin Dashboard**: Manage pre-orders, listings and automations from dedicated dashboards.

**How It Works:**

1. Install and activate the plugin
2. Your storefront will then be ready to take pre-orders.
4. To get started, navigate to WooCommerce > PreProduct to complete setup.
5. Then click the "New listing" menu item from the top of PreProduct to pick products for pre-order.

**Requirements:**

* WooCommerce 5.0 or higher
* PHP 7.4 or higher


== Installation ==

**Automatic Installation:**

1. Log in to your WordPress dashboard
2. Navigate to Plugins > Add New
3. Search for "PreProduct"
4. Click "Install Now" and then "Activate"
5. Navigate to WooCommerce > PreProduct to complete setup

**Manual Installation:**

1. Download the plugin zip file
2. Upload the plugin files to the `/wp-content/plugins/woo-preproduct` directory, or install the plugin through the WordPress plugins screen
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Navigate to WooCommerce > PreProduct to complete setup

**Setup:**

1. __After Installation:__ Click the option to open the PreProduct app.
2. __Sign Up:__ You'll then be prompted to sign up for a user account.
3. __Connect Menu:__ Once logged in, click the "Connect" menu on the left-hand side.
4. __Payment Processor:__ From the top "Payment Processor" row, select an option to connect a payment processor.
5. __Ecommerce Platform:__ Under the "Ecommerce Platform" row, choose "WooCommerce" and confirm access when redirected.
6. __Ready to Start Pre-Selling:__ You are now fully connected and ready to start pre-selling. Click "Home" to choose a PreProduct plan.
7. __Start Pre-Selling:__ Navigate to the "Setup" screen to adjust your settings, or click "New Listing" in the menu to choose products to start pre-selling.


== Frequently Asked Questions ==

= Does this require a PreProduct account? =

Yes, you'll be prompted to create an account after opening PreProduct from your Wordpress admin.

= Will this affect my existing WooCommerce functionality? =

No, the plugin is designed to enhance your existing store without interfering with normal WooCommerce operations. It only adds pre-order capabilities to your products.

= How does the plugin detect which products should have pre-order functionality? =

Yes, the plugin automatically tags eligible add-to-cart buttons so that PreProduct understands which products they belong to. You can then list products for pre-order via the PreProduct app (either manually or via automation).

= What happens when I deactivate or uninstall the plugin? =

When deactivated, all PreProduct functionality is removed from your store (script and buy button attributes). 
When uninstalled, the plugin sends a notification to PreProduct and cleans up all stored data.

= Is the plugin compatible with my theme? =

Yes, the plugin works with any properly coded WooCommerce-compatible theme. It uses standard WooCommerce hooks and filters to add functionality.
Please get in touch if the integration isn't working as expected with your particular theme.

= How does the plugin handle different environments (development/staging/production)? =

The plugin automatically detects your environment and uses appropriate endpoints. Development environments (localhost, .test, .local domains) connect to PreProduct's development servers, while production sites connect to production servers.

= Are there any performance impacts? =

The plugin is designed for minimal performance impact. The PreProduct script is loaded with the `defer` attribute and only runs after your page has finished loading.

== Screenshots ==

1. Track, communicate & charge at the product or customer level
2. Tight integrations with your WooCommerce catalogue, storefront & orders admin
3. Communicate & sell via a customisable pre-order cart
4. Charge when you’re ready; now, later or both via deposits

== Changelog ==
= 1.0.4 = 
* Readme update 

= 1.0.3 = 
* Readme update + assets

= 1.0.2 = 
* Readme update + assets

= 1.0.0 =
* Initial release
* WooCommerce integration
* Environment-aware endpoint detection
* Automatic button tagging for simple products
* Admin dashboard integration
* Webhook notifications for plugin lifecycle events
* Comprehensive test suite with 111+ tests
* Security features including HMAC signature verification
* Performance optimizations with deferred script loading

== Upgrade Notice ==

= 1.0.0 =
Initial release of PreProduct for WooCommerce. Install to enable pre-order functionality for your store.

== Support ==

For support, please visit:
* Plugin Support: hello@preproduct.io
* Documentation: https://preproduct.io/docs
* Contact: hello@preproduct.io
