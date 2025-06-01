<?php
/**
 * Plugin Name:       PreProduct
 * Plugin URI:        https://preproduct.io/woocommerce-pre-orders/
 * Description:       Smarter WooCommerce pre-orders. Charge upfront, later or both (via deposits), add a pre-order area to your cart page, automations available as well as fine-grained control.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: PreProduct
 * Author URI:        https://preproduct.io/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WOO_PREPRODUCT_VERSION', '1.0.0');
define('WOO_PREPRODUCT_PLUGIN_FILE', __FILE__);
define('WOO_PREPRODUCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOO_PREPRODUCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOO_PREPRODUCT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
function woo_preproduct_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woo_preproduct_woocommerce_missing_notice');
        return false;
    }
    return true;
}

// Admin notice for missing WooCommerce
function woo_preproduct_woocommerce_missing_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('PreProduct for WooCommerce requires WooCommerce to be installed and active.', 'woo-preproduct');
    echo '</p></div>';
}

// Plugin activation hook
register_activation_hook(__FILE__, 'woo_preproduct_activate');

function woo_preproduct_activate() {
    if (!woo_preproduct_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('PreProduct for WooCommerce requires WooCommerce to be installed and active.', 'woo-preproduct'),
            esc_html__('Plugin Activation Error', 'woo-preproduct'),
            array('back_link' => true)
        );
    }
}

// Initialize plugin
add_action('plugins_loaded', 'woo_preproduct_init');

function woo_preproduct_init() {
    if (!woo_preproduct_check_woocommerce()) {
        return;
    }
    
    // Load text domain for translations
    load_plugin_textdomain('woo-preproduct', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Include main plugin class
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-woo-preproduct.php';
    
    // Initialize the plugin
    WooPreProduct::instance();
}