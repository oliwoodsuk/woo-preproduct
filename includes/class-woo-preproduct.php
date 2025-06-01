<?php

/**
 * Main WooPreProduct Class
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main WooPreProduct Class
 */
class WooPreProduct
{
    /**
     * Single instance of the class
     *
     * @var WooPreProduct
     */
    protected static $_instance = null;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = WOO_PREPRODUCT_VERSION;

    /**
     * Environment Manager instance
     *
     * @var WooPreProduct_Environment_Manager
     */
    public $environment_manager = null;

    /**
     * Button Tagger instance
     *
     * @var WooPreProduct_Button_Tagger
     */
    public $button_tagger = null;

    /**
     * Main WooPreProduct Instance
     *
     * Ensures only one instance of WooPreProduct is loaded or can be loaded.
     *
     * @return WooPreProduct - Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WooPreProduct Constructor
     */
    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define WooPreProduct Constants
     */
    private function define_constants()
    {
        $this->define('WOO_PREPRODUCT_ABSPATH', dirname(WOO_PREPRODUCT_PLUGIN_FILE) . '/');
    }

    /**
     * Define constant if not already set
     *
     * @param string $name
     * @param string|bool $value
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required core files
     */
    public function includes()
    {
        // Core includes
        include_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-environment-manager.php';
        include_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/woo-preproduct-functions.php';
        include_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-debug-info.php';
        include_once WOO_PREPRODUCT_PLUGIN_DIR . 'includes/class-button-tagger.php';

        // Initialize Environment Manager
        $this->environment_manager = WooPreProduct_Environment_Manager::get_instance();
        
        // Initialize Button Tagger (only if WooCommerce is active)
        if ($this->isWoocommerceActive()) {
            $this->button_tagger = new WooPreProduct_Button_Tagger();
        }
        
        // Additional includes will be added here as the plugin develops
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks()
    {
        add_action('init', array($this, 'init'), 0);
    }

    /**
     * Init WooPreProduct when WordPress Initializes
     */
    public function init()
    {
        // Before init action
        do_action('woo_preproduct_before_init');

        // Set up localization
        $this->load_plugin_textdomain();

        // Initialize debug info (for administrators only)
        WooPreProduct_Debug_Info::init();

        // Init action
        do_action('woo_preproduct_init');
    }

    /**
     * Load Localisation files
     */
    public function load_plugin_textdomain()
    {
        $locale = determine_locale();
        $locale = apply_filters('plugin_locale', $locale, 'woo-preproduct');

        unload_textdomain('woo-preproduct');
        load_textdomain('woo-preproduct', WP_LANG_DIR . '/woo-preproduct/woo-preproduct-' . $locale . '.mo');
        load_plugin_textdomain('woo-preproduct', false, plugin_basename(dirname(WOO_PREPRODUCT_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Get the plugin URL
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', WOO_PREPRODUCT_PLUGIN_FILE));
    }

    /**
     * Get the plugin path
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(WOO_PREPRODUCT_PLUGIN_FILE));
    }

    /**
     * Get Environment Manager instance
     *
     * @return WooPreProduct_Environment_Manager
     */
    public function environment()
    {
        return $this->environment_manager;
    }

    /**
     * Get plugin path
     *
     * @return string
     */
    public function pluginPath()
    {
        return untrailingslashit(plugin_dir_path(WOO_PREPRODUCT_PLUGIN_FILE));
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function isWoocommerceActive()
    {
        // Check if WooCommerce class exists
        if (class_exists('WooCommerce')) {
            return true;
        }
        
        // Check if WooCommerce plugin is active
        if (function_exists('is_plugin_active')) {
            return is_plugin_active('woocommerce/woocommerce.php');
        }
        
        // Fallback check for WC functions
        return function_exists('WC');
    }
}
