<?php
/**
 * Logger Class for PreProduct
 *
 * Provides centralized logging functionality using WooCommerce's logging system
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooPreProduct Logger Class
 */
class WooPreProduct_Logger
{
    /**
     * Logger instance
     * @var WC_Logger
     */
    private static $logger = null;
    
    /**
     * Log context
     * @var array
     */
    private static $context = array('source' => 'woo-preproduct');
    
    /**
     * Initialize the logger
     */
    private static function init()
    {
        if (self::$logger === null && function_exists('wc_get_logger')) {
            self::$logger = wc_get_logger();
        }
    }
    
    /**
     * Log a message with specified level
     *
     * @param string $message The message to log
     * @param string $level The log level (emergency, alert, critical, error, warning, notice, info, debug)
     * @param array $additional_context Additional context data
     */
    public static function log($message, $level = 'info', $additional_context = array())
    {
        self::init();
        
        if (self::$logger === null) {
            // Fallback to error_log if WooCommerce logger is not available
            if (function_exists('error_log')) {
                error_log('PreProduct: ' . $message);
            }
            return;
        }
        
        $context = array_merge(self::$context, $additional_context);
        self::$logger->log($level, $message, $context);
    }
    
    /**
     * Log an emergency message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function emergency($message, $context = array())
    {
        self::log($message, 'emergency', $context);
    }
    
    /**
     * Log an alert message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function alert($message, $context = array())
    {
        self::log($message, 'alert', $context);
    }
    
    /**
     * Log a critical message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function critical($message, $context = array())
    {
        self::log($message, 'critical', $context);
    }
    
    /**
     * Log an error message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function error($message, $context = array())
    {
        self::log($message, 'error', $context);
    }
    
    /**
     * Log a warning message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function warning($message, $context = array())
    {
        self::log($message, 'warning', $context);
    }
    
    /**
     * Log a notice message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function notice($message, $context = array())
    {
        self::log($message, 'notice', $context);
    }
    
    /**
     * Log an info message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function info($message, $context = array())
    {
        self::log($message, 'info', $context);
    }
    
    /**
     * Log a debug message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function debug($message, $context = array())
    {
        self::log($message, 'debug', $context);
    }
    
    /**
     * Log plugin activation
     */
    public static function log_activation()
    {
        self::info('Plugin activated', array(
            'wp_version' => get_bloginfo('version'),
            'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'unknown',
            'php_version' => PHP_VERSION,
            'plugin_version' => defined('WOO_PREPRODUCT_VERSION') ? WOO_PREPRODUCT_VERSION : '1.0.0'
        ));
    }
    
    /**
     * Log plugin deactivation
     */
    public static function log_deactivation()
    {
        self::info('Plugin deactivated');
    }
    
    /**
     * Log plugin uninstall
     */
    public static function log_uninstall()
    {
        self::info('Plugin uninstalled');
    }
    
    /**
     * Log API requests
     *
     * @param string $endpoint The API endpoint
     * @param string $method The HTTP method
     * @param array $response The API response
     */
    public static function log_api_request($endpoint, $method, $response = array())
    {
        $message = sprintf('API request: %s %s', $method, $endpoint);
        $context = array(
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => isset($response['response']['code']) ? $response['response']['code'] : 'unknown'
        );
        
        if (is_wp_error($response)) {
            self::error($message . ' - ' . $response->get_error_message(), $context);
        } elseif (isset($response['response']['code']) && $response['response']['code'] >= 400) {
            self::warning($message . ' - HTTP ' . $response['response']['code'], $context);
        } else {
            self::info($message . ' - Success', $context);
        }
    }
    
    /**
     * Log webhook events
     *
     * @param string $event The webhook event
     * @param array $data The webhook data
     */
    public static function log_webhook($event, $data = array())
    {
        $message = sprintf('Webhook triggered: %s', $event);
        $context = array_merge(array('event' => $event), $data);
        
        self::info($message, $context);
    }
    
    /**
     * Log environment detection
     *
     * @param string $environment The detected environment
     * @param string $url The current site URL
     */
    public static function log_environment($environment, $url)
    {
        $message = sprintf('Environment detected: %s for URL: %s', $environment, $url);
        $context = array(
            'environment' => $environment,
            'site_url' => $url
        );
        
        self::debug($message, $context);
    }
    
    /**
     * Log script loading
     *
     * @param string $script_url The script URL being loaded
     * @param bool $is_admin Whether this is an admin context
     */
    public static function log_script_loading($script_url, $is_admin = false)
    {
        $message = sprintf('PreProduct script %s: %s', 
            $is_admin ? 'skipped (admin)' : 'loaded', 
            $script_url
        );
        
        $context = array(
            'script_url' => $script_url,
            'is_admin' => $is_admin,
            'page' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'unknown'
        );
        
        self::debug($message, $context);
    }
    
    /**
     * Log button tagging
     *
     * @param int $product_id The product ID
     * @param string $product_type The product type
     * @param bool $tagged Whether the button was tagged
     */
    public static function log_button_tagging($product_id, $product_type, $tagged)
    {
        $message = sprintf('Product %d (%s) button %s', 
            $product_id, 
            $product_type, 
            $tagged ? 'tagged for PreProduct' : 'not tagged'
        );
        
        $context = array(
            'product_id' => $product_id,
            'product_type' => $product_type,
            'tagged' => $tagged
        );
        
        self::debug($message, $context);
    }
    
    /**
     * Check if debug logging is enabled
     *
     * @return bool
     */
    public static function is_debug_enabled()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    /**
     * Get the log file path for WooCommerce logs
     *
     * @return string
     */
    public static function get_log_file_path()
    {
        if (function_exists('wc_get_log_file_path')) {
            return wc_get_log_file_path('woo-preproduct');
        }
        
        return '';
    }
} 