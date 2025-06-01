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

/*
 * Environment Configuration
 *
 * The plugin automatically detects development environments based on domain patterns
 * (localhost, .test, .local, staging, dev), but you can also manually override
 * the environment detection by defining the following constant in wp-config.php:
 *
 * // Force development mode (use ngrok endpoints)
 * define('PREPRODUCT_DEV_MODE', true);
 *
 * // Force production mode (use production endpoints)
 * define('PREPRODUCT_DEV_MODE', false);
 */

// Plugin constants
define('WOO_PREPRODUCT_VERSION', '0.0.1');
define('WOO_PREPRODUCT_PLUGIN_FILE', __FILE__);
define('WOO_PREPRODUCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOO_PREPRODUCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOO_PREPRODUCT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
function woo_preproduct_check_woocommerce()
{
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woo_preproduct_woocommerce_missing_notice');
        return false;
    }
    return true;
}

// Admin notice for missing WooCommerce
function woo_preproduct_woocommerce_missing_notice()
{
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('PreProduct for WooCommerce requires WooCommerce to be installed and active.', 'woo-preproduct');
    echo '</p></div>';
}

// Plugin activation hook
register_activation_hook(__FILE__, 'woo_preproduct_activate');

function woo_preproduct_activate()
{
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('PreProduct requires WooCommerce to be installed and active.', 'woo-preproduct'),
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
    
    // Include logger for activation logging
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-logger.php';
    
    // Log activation
    WooPreProduct_Logger::log_activation();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set a transient to redirect to the admin page on first activation
    set_transient('woo_preproduct_activation_redirect', true, 30);
    
    // Trigger webhook creation (will be handled by the webhook class)
    do_action('woo_preproduct_activated');
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'woo_preproduct_deactivate');

function woo_preproduct_deactivate()
{
    // Include logger for deactivation logging
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-logger.php';
    
    // Log deactivation
    WooPreProduct_Logger::log_deactivation();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // TEMPORARY: Trigger webhook for testing (normally this would only happen on uninstall)
    // Ensure the webhook class is loaded
    if (!class_exists('WooPreProduct_Plugin_Uninstall_Webhook')) {
        require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-plugin-uninstall-webhook.php';
    }
    
    try {
        if (class_exists('WooPreProduct_Plugin_Uninstall_Webhook')) {
            WooPreProduct_Plugin_Uninstall_Webhook::trigger_uninstall_webhook();
            WooPreProduct_Logger::info('Deactivation webhook sent successfully');
        } else {
            WooPreProduct_Logger::warning('Webhook class not available during deactivation');
        }
    } catch (Exception $e) {
        WooPreProduct_Logger::error('Webhook trigger failed during deactivation: ' . $e->getMessage());
    }
}

// Activation redirect to admin page
add_action('admin_init', 'woo_preproduct_activation_redirect');

function woo_preproduct_activation_redirect()
{
    // Check if we should redirect
    if (get_transient('woo_preproduct_activation_redirect')) {
        delete_transient('woo_preproduct_activation_redirect');
        
        // Only redirect if user can access the admin page
        if (current_user_can('manage_woocommerce')) {
            wp_safe_redirect(admin_url('admin.php?page=woo-preproduct'));
            exit;
        }
    }
}

// Initialize plugin
add_action('plugins_loaded', 'woo_preproduct_init');

function woo_preproduct_init()
{
    if (!woo_preproduct_check_woocommerce()) {
        return;
    }

    // Load text domain for translations
    load_plugin_textdomain('woo-preproduct', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Include logger class
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-logger.php';

    // Include main plugin class
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-woo-preproduct.php';

    // Include plugin uninstall webhook handler
    require_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-plugin-uninstall-webhook.php';

    // Initialize the plugin
    WooPreProduct::instance();
}

// Add plugin action links (Settings link on plugins page)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woo_preproduct_action_links');

function woo_preproduct_action_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=woo-preproduct') . '">' . __('Settings', 'woo-preproduct') . '</a>',
        '<a href="https://docs.preproduct.io/woocommerce" target="_blank">' . __('Documentation', 'woo-preproduct') . '</a>',
        '<a href="https://preproduct.io/support" target="_blank">' . __('Support', 'woo-preproduct') . '</a>',
    );
    
    return array_merge($plugin_links, $links);
}
