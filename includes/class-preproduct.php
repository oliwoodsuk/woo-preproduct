<?php

/**
 * Main PreProduct Plugin Class
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main PreProduct Plugin Class
 */
class PreProduct_Plugin
{
    /**
     * Single instance of the class
     *
     * @var PreProduct_Plugin
     */
    protected static $_instance = null;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = PREPRODUCT_VERSION;

    /**
     * Environment Manager instance
     *
     * @var PreProduct_Environment_Manager
     */
    public $environment_manager = null;

    /**
     * Button Tagger instance
     *
     * @var PreProduct_Button_Tagger
     */
    public $button_tagger = null;

    /**
     * Script Manager instance
     *
     * @var PreProduct_Script_Manager
     */
    public $script_manager = null;

    /**
     * Admin Page instance
     *
     * @var PreProduct_Admin_Page
     */
    public $admin_page = null;

    /**
     * Main PreProduct Plugin Instance
     *
     * Ensures only one instance of PreProduct_Plugin is loaded or can be loaded.
     *
     * @return PreProduct_Plugin - Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * PreProduct Plugin Constructor
     */
    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define PreProduct Plugin Constants
     */
    private function define_constants()
    {
        $this->define('PREPRODUCT_ABSPATH', plugin_dir_path(PREPRODUCT_PLUGIN_FILE));
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
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/class-environment-manager.php';
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/preproduct-functions.php';
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/class-debug-info.php';
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/class-button-tagger.php';
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/class-script-manager.php';
        include_once PREPRODUCT_PLUGIN_DIR . 'includes/class-admin-page.php';
        
        // Initialize Environment Manager
        $this->environment_manager = PreProduct_Environment_Manager::get_instance();
        
        // Initialize Button Tagger (only if WooCommerce is active)
        if ($this->isWoocommerceActive()) {
            $this->button_tagger = new PreProduct_Button_Tagger();
        }
        
        // Initialize Script Manager (only if WooCommerce is active)
        if ($this->isWoocommerceActive()) {
            $this->script_manager = new PreProduct_Script_Manager();
        }
        
        // Initialize Admin Page (only if WooCommerce is active)
        if ($this->isWoocommerceActive()) {
            $this->admin_page = new PreProduct_Admin_Page();
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
     * Init PreProduct Plugin when WordPress Initializes
     */
    public function init()
    {
        // Before init action
        do_action('preproduct_before_init');

        // Initialize debug info (for administrators only)
        PreProduct_Debug_Info::init();

        // Init action
        do_action('preproduct_init');
    }

    /**
     * Get the plugin URL
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', PREPRODUCT_PLUGIN_FILE));
    }

    /**
     * Get the plugin path
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(PREPRODUCT_PLUGIN_FILE));
    }

    /**
     * Get Environment Manager instance
     *
     * @return PreProduct_Environment_Manager
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
        return untrailingslashit(plugin_dir_path(PREPRODUCT_PLUGIN_FILE));
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
