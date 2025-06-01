<?php
/**
 * Uninstall script for WooPreProduct Plugin
 *
 * This file is executed when the plugin is uninstalled through WordPress admin.
 * It triggers a custom WooCommerce webhook topic for plugin uninstall events.
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the plugin uninstall webhook class
require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-uninstall-webhook.php';

// Trigger the uninstall webhook using WooCommerce's native webhook system
WooPreProduct_Plugin_Uninstall_Webhook::trigger_uninstall_webhook();

// Clean up the webhook after triggering (so PreProduct knows we're uninstalling)
WooPreProduct_Plugin_Uninstall_Webhook::cleanup_webhook(); 