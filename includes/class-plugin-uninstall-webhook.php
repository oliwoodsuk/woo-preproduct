<?php
/**
 * Plugin Uninstall Webhook Class
 *
 * Uses existing WooCommerce webhook and modifies payload/headers via filters
 * for plugin uninstall notifications to PreProduct
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Uninstall Webhook Class
 */
class PreProduct_Plugin_Uninstall_Webhook
{
    
    /**
     * Constructor - Initialize hooks
     */
    public function __construct()
    {
        // Hook into plugin activation to ensure we have a webhook configured
        add_action('preproduct_activated', array($this, 'ensure_webhook_exists'));
    }
    
    /**
     * Trigger plugin uninstall webhook using existing WooCommerce webhook
     * 
     * @param string $event The event type: 'deactivated' or 'uninstalled'
     */
    public static function trigger_uninstall_webhook($event = 'uninstalled')
    {
        // Auto-detect event if not specified
        if ($event === 'uninstalled') {
            // Check if we're in the uninstall context
            if (defined('WP_UNINSTALL_PLUGIN')) {
                $event = 'plugin.uninstalled';
            } else {
                $event = 'plugin.deactivated'; // Likely called from deactivation hook
            }
        } else {
            $event = 'plugin.' . $event;
        }
        
        // Prepare plugin data for the webhook
        $plugin_data = array(
            'event' => $event,
            'store_url' => site_url(),
            'admin_email' => get_option('admin_email'),
            'store_name' => get_option('blogname'),
            'timestamp' => time(),
            'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'unknown',
            'plugin_version' => defined('PREPRODUCT_VERSION') ? PREPRODUCT_VERSION : '1.0.0'
        );
        
        // Find an existing webhook with the correct URL
        $webhook = self::get_existing_webhook();
        
        if ($webhook) {
            $webhook_id = $webhook->get_id();
            
            // Create filter functions with proper cleanup
            $payload_filter = function($payload, $resource, $resource_id, $id) use ($plugin_data, $webhook_id) {
                // Only modify payload for our specific webhook
                if ($id === $webhook_id) {
                    return $plugin_data;
                }
                return $payload;
            };
            
            // Use WordPress HTTP API filter to modify headers before request is sent
            $http_request_filter = function($parsed_args, $url) use ($event, $webhook) {
                // Only modify requests to our webhook URL
                if ($url === $webhook->get_delivery_url()) {
                    if (!isset($parsed_args['headers'])) {
                        $parsed_args['headers'] = array();
                    }
                    // Override the webhook topic header
                    $parsed_args['headers']['X-WC-Webhook-Topic'] = $event;
                    
                    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                        error_log('PreProduct: Modified HTTP request headers for URL: ' . $url);
                        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                        error_log('PreProduct: Set X-WC-Webhook-Topic to: ' . $event);
                    }
                }
                return $parsed_args;
            };
            
            // Add filters
            add_filter('woocommerce_webhook_payload', $payload_filter, 10, 4);
            add_filter('http_request_args', $http_request_filter, 10, 2);
            
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                error_log('PreProduct: About to deliver webhook with event: ' . $event);
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                error_log('PreProduct: Webhook ID: ' . $webhook_id);
            }
            
            // Deliver the webhook immediately using a dummy product ID
            // This bypasses the enqueue system and delivers right away
            $webhook->deliver(1); // Using product ID 1 as dummy (will be replaced by our filter)
            
            // Clean up filters immediately after delivery
            remove_filter('woocommerce_webhook_payload', $payload_filter, 10);
            remove_filter('http_request_args', $http_request_filter, 10);
            
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                error_log('PreProduct: Webhook delivery completed');
            }
        }
    }
    
    /**
     * Get the plugin directory path
     * Works both when the constant is defined and during uninstall when it might not be
     * 
     * @return string
     */
    private static function get_plugin_dir()
    {
        if (defined('PREPRODUCT_PLUGIN_DIR')) {
            return PREPRODUCT_PLUGIN_DIR;
        }
        
        // Fallback: Use WordPress function if PREPRODUCT_PLUGIN_FILE is defined
        if (defined('PREPRODUCT_PLUGIN_FILE')) {
            return plugin_dir_path(PREPRODUCT_PLUGIN_FILE);
        }
        
        // Last resort fallback: calculate from current file location
        // This file is in includes/, so parent directory is the plugin root
        return trailingslashit(dirname(__DIR__));
    }
    
    /**
     * Get an existing webhook to use for delivery
     * Only uses webhooks with the exact correct delivery URL (matching get_webhook_url())
     * This ensures the webhook secret will be correct for PreProduct authentication
     * 
     * @return WC_Webhook|false
     */
    private static function get_existing_webhook()
    {
        if (!class_exists('WC_Data_Store')) {
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                error_log('PreProduct: WC_Data_Store class not available');
            }
            return false;
        }
        
        // Get the exact webhook URL we need
        require_once self::get_plugin_dir() . 'includes/class-environment-manager.php';
        $env_manager = PreProduct_Environment_Manager::get_instance();
        $expected_webhook_url = $env_manager->get_webhook_url();
        
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
            error_log('PreProduct: Looking for webhook with URL: ' . $expected_webhook_url);
        }
        
        // Get webhook data store
        $data_store = WC_Data_Store::load('webhook');
        $webhook_ids = $data_store->get_webhooks_ids();
        
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
            error_log('PreProduct: Found ' . count($webhook_ids) . ' total webhooks');
        }
        
        foreach ($webhook_ids as $webhook_id) {
            $webhook = wc_get_webhook($webhook_id);
            
            if (!$webhook || $webhook->get_status() !== 'active') {
                if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                    error_log('PreProduct: Webhook ID ' . $webhook_id . ' is not active or not found');
                }
                continue;
            }
            
            $webhook_url = $webhook->get_delivery_url();
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                error_log('PreProduct: Checking webhook ID ' . $webhook_id . ' with URL: ' . $webhook_url);
            }
            
            // Only use webhooks with the EXACT correct delivery URL
            // This ensures the secret matches what PreProduct expects
            if ($webhook_url === $expected_webhook_url) {
                if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
                    error_log('PreProduct: Found matching webhook ID ' . $webhook_id);
                }
                return $webhook;
            }
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('error_log')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for webhook operations
            error_log('PreProduct: No matching webhook found');
        }
        
        return false;
    }
    
    /**
     * Ensure we have at least one webhook configured for PreProduct
     * Called during plugin activation
     */
    public function ensure_webhook_exists()
    {
        // Only proceed if WooCommerce is active
        if (!class_exists('WC_Webhook')) {
            return false;
        }
        
        // Check if we already have a PreProduct webhook
        $existing_webhook = $this->has_preproduct_webhook();
        if ($existing_webhook) {
            return true; // We have an existing webhook with correct URL
        }
        
        // Create a new webhook for PreProduct
        return $this->create_preproduct_webhook();
    }
    
    /**
     * Check if we have an existing PreProduct webhook with the correct URL
     * Returns the webhook instance if found, false otherwise
     * 
     * @return WC_Webhook|false
     */
    private function has_preproduct_webhook()
    {
        if (!class_exists('WC_Data_Store')) {
            return false;
        }
        
        // Get the exact webhook URL we need
        require_once self::get_plugin_dir() . 'includes/class-environment-manager.php';
        $env_manager = PreProduct_Environment_Manager::get_instance();
        $expected_webhook_url = $env_manager->get_webhook_url();
        
        $data_store = WC_Data_Store::load('webhook');
        $webhook_ids = $data_store->get_webhooks_ids();
        
        foreach ($webhook_ids as $webhook_id) {
            $webhook = wc_get_webhook($webhook_id);
            
            if (!$webhook) {
                continue;
            }
            
            // Check for exact URL match and return the webhook instance
            if ($webhook->get_delivery_url() === $expected_webhook_url) {
                return $webhook;
            }
        }
        
        return false;
    }
    
    /**
     * Create a PreProduct webhook for notifications
     * 
     * @return bool
     */
    private function create_preproduct_webhook()
    {
        require_once self::get_plugin_dir() . 'includes/class-environment-manager.php';
        $env_manager = PreProduct_Environment_Manager::get_instance();
        $webhook_url = $env_manager->get_webhook_url();
        
        $webhook = new WC_Webhook();
        $webhook->set_name('PreProduct Notifications');
        $webhook->set_status('active');
        $webhook->set_topic('product.updated'); // Use existing topic, we'll modify payload/headers
        $webhook->set_delivery_url($webhook_url);
        $webhook->set_secret(wp_generate_password(32, false));
        
        $webhook_id = $webhook->save();
        
        if ($webhook_id) {
            update_option('preproduct_webhook_id', $webhook_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the webhook endpoint URL that will be used
     * 
     * @return string The webhook endpoint URL
     */
    public function get_webhook_endpoint()
    {
        require_once self::get_plugin_dir() . 'includes/class-environment-manager.php';
        $env_manager = PreProduct_Environment_Manager::get_instance();
        return $env_manager->get_webhook_url();
    }
    
    /**
     * Get the topic that will be sent in headers for plugin uninstall
     * 
     * @return string
     */
    public function get_webhook_topic()
    {
        return 'plugin.uninstalled';
    }
    
    /**
     * Clean up webhook when plugin is uninstalled
     */
    public static function cleanup_webhook()
    {
        $webhook_id = get_option('preproduct_webhook_id', 0);
        
        if ($webhook_id) {
            $webhook = wc_get_webhook($webhook_id);
            if ($webhook) {
                $webhook->delete(true);
            }
            delete_option('preproduct_webhook_id');
        }
    }
}

// Initialize the webhook handler
new PreProduct_Plugin_Uninstall_Webhook(); 