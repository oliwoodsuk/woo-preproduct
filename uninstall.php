<?php
/**
 * Uninstall script for PreProduct Plugin
 *
 * This file is executed when the plugin is uninstalled through WordPress admin.
 * It triggers a custom WooCommerce webhook topic for plugin uninstall events.
 *
 * @package PreProduct
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define plugin constants needed by included classes
// These are normally defined in the main plugin file, but during uninstall
// the main plugin file isn't loaded, so we need to define them here
if (!defined('PREPRODUCT_VERSION')) {
    define('PREPRODUCT_VERSION', '0.0.1');
}
if (!defined('PREPRODUCT_PLUGIN_FILE')) {
    define('PREPRODUCT_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'preproduct.php');
}
if (!defined('PREPRODUCT_PLUGIN_DIR')) {
    define('PREPRODUCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('PREPRODUCT_PLUGIN_URL')) {
    define('PREPRODUCT_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('PREPRODUCT_PLUGIN_BASENAME')) {
    define('PREPRODUCT_PLUGIN_BASENAME', plugin_basename(__FILE__ . '/preproduct.php'));
}

// Include the logger class
require_once plugin_dir_path(__FILE__) . 'includes/class-logger.php';

// Include the plugin uninstall webhook class
require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-uninstall-webhook.php';

// Log the uninstall event
PreProduct_Logger::log_uninstall();

try {
    // Trigger the uninstall webhook using WooCommerce's native webhook system
    PreProduct_Plugin_Uninstall_Webhook::trigger_uninstall_webhook();
    PreProduct_Logger::info('Uninstall webhook sent successfully');
} catch (Exception $e) {
    PreProduct_Logger::error('Failed to send uninstall webhook: ' . $e->getMessage());
}

try {
    // Clean up the webhook after triggering (so PreProduct knows we're uninstalling)
    PreProduct_Plugin_Uninstall_Webhook::cleanup_webhook();
    PreProduct_Logger::info('Webhook cleanup completed');
} catch (Exception $e) {
    PreProduct_Logger::error('Failed to cleanup webhook: ' . $e->getMessage());
} 