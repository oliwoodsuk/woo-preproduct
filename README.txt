=== PreProduct – Pre-orders & Deposits for WooCommerce ===
Contributors: preproduct
Tags: pre-order, pre-orders, backorders, preorder, deposit
Requires at least: 5.2
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Pre-orders and deposits for WooCommerce. Launch earlier, capture demand, and grow.

== Pre-order App Description ==

**PreProduct helps you take pre-orders for products before they're in stock — while keeping your WooCommerce store organized and in charge.**
**Take pre-orders now, later or by deposit.**

Whether you call it pre-order, preorder, or backorder — PreProduct helps you take orders before you’re ready to ship.
It integrates your store and WooCommerce with the PreProduct pre-order platform, enabling you to capture demand and sales before your products are available.

Whether you're launching a new product or restocking a bestseller, PreProduct gives you full control over *how and when* customers pay.

✅ Let customers pre-order Now, Later, or via Deposit  
✅ Customize pre-order flows, messages and fulfillment behavior  
✅ Keep pre-orders out of WooCommerce until you're ready to fulfill  
✅ Dashboard for listings, sales, customer status and automation  
✅ Works with variable products and supports multi-variant logic

See [https://preproduct.io](https://preproduct.io) for more information

**Key Pre-order Features:**

- Seamless WooCommerce integration with your store, catalog and order flow
- Take payment upfront, later, or via partial deposit
- Control if multiple pre-orders or single pre-order products can be checked out together
- Built-in customer email flows, portal, and progress tracking
- Choose when to push pre-orders into WooCommerce
- Webhook lifecycle support for advanced workflows


**How It Works:**

1. Install and activate the plugin
2. Create your PreProduct account and connect WooCommerce
3. Connect your payment processor
4. Choose products and configure how pre-orders should work
5. Start capturing pre-orders


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


== Frequently Asked Pre-order Questions ==

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
= 1.0.6 = 
* Readme update 

= 1.0.5 = 
* Readme update 

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
