<?php

/**
 * Environment Manager Class
 *
 * Handles environment detection and endpoint configuration
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PreProduct Environment Manager Class
 */
class PreProduct_Environment_Manager
{
    /**
     * Single instance of the class
     *
     * @var PreProduct_Environment_Manager
     */
    private static $instance = null;

    /**
     * Current environment
     *
     * @var string
     */
    private $environment = 'production';

    /**
     * Get instance of Environment Manager
     *
     * @return PreProduct_Environment_Manager
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - detects environment on instantiation
     */
    private function __construct()
    {
        $this->detect_environment();
    }

    /**
     * Detect the current environment
     *
     * Checks for development constant override first,
     * then checks domain patterns for development environments
     */
    private function detect_environment()
    {
        // Check for development constant override
        if (defined('PREPRODUCT_DEV_MODE') && PREPRODUCT_DEV_MODE) {
            $this->environment = 'development';
            return;
        }

        // Check for development domains
        $site_url = site_url();
        if (
            strpos($site_url, 'localhost') !== false
        ) {
            $this->environment = 'development';
            return;
        }

        // Default to production
        $this->environment = 'production';
    }

    /**
     * Check if current environment is development
     *
     * @return bool
     */
    public function is_development()
    {
        return $this->environment === 'development';
    }

    /**
     * Check if current environment is production
     *
     * @return bool
     */
    public function is_production()
    {
        return $this->environment === 'production';
    }

    /**
     * Get current environment name
     *
     * @return string
     */
    public function get_environment()
    {
        return $this->environment;
    }

    /**
     * Get the PreProduct embed script URL
     *
     * @return string
     */
    public function get_script_url()
    {
        return $this->is_development()
            ? 'https://preproduct.ngrok.io/preproduct-embed.js'
            : 'https://api.preproduct.io/preproduct-embed.js';
    }

    /**
     * Get the PreProduct iframe URL for WooCommerce integration
     *
     * @return string
     */
    public function get_iframe_url()
    {
        return $this->is_development()
            ? 'https://preproduct.ngrok.io/woocommerce'
            : 'https://api.preproduct.io/woocommerce';
    }

    /**
     * Get the PreProduct webhook URL for WooCommerce
     *
     * @return string
     */
    public function get_webhook_url()
    {
        return $this->is_development()
            ? 'https://preproduct.ngrok.io/woocommerce/webhooks'
            : 'https://api.preproduct.io/woocommerce/webhooks';
    }

    /**
     * Get the base API URL
     *
     * @return string
     */
    public function get_api_base_url()
    {
        return $this->is_development()
            ? 'https://preproduct.ngrok.io'
            : 'https://api.preproduct.io';
    }

    /**
     * Get all endpoint URLs as an array
     *
     * @return array
     */
    public function get_all_urls()
    {
        return array(
            'script'      => $this->get_script_url(),
            'iframe'      => $this->get_iframe_url(),
            'webhook'     => $this->get_webhook_url(),
            'api_base'    => $this->get_api_base_url(),
            'environment' => $this->get_environment(),
        );
    }
}
