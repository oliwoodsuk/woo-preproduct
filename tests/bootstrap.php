<?php
/**
 * Test Bootstrap File
 * 
 * Contains shared WordPress function mocks and setup code
 * to prevent function redeclaration errors across multiple test files
 */

// Only declare functions if they haven't been declared yet
if (!function_exists('is_admin')) {
    // Define ABSPATH to prevent WordPress security check from exiting
    define('ABSPATH', '/fake/wordpress/path/');
    define('PREPRODUCT_VERSION', '1.0.0');
    define('PREPRODUCT_PLUGIN_FILE', dirname(__DIR__) . '/woo-preproduct.php');
    define('PREPRODUCT_PLUGIN_DIR', dirname(__DIR__) . '/');

    // Mock WordPress functions
    function is_admin() {
        global $mock_is_admin;
        return $mock_is_admin ?? false;
    }

    function current_user_can($capability) {
        global $mock_current_user_can;
        return $mock_current_user_can ?? true;
    }

    function apply_filters($hook, $value, ...$args) {
        return $value;
    }

    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        global $mock_action_hooks;
        if (!isset($mock_action_hooks[$hook])) {
            $mock_action_hooks[$hook] = array();
        }
        $mock_action_hooks[$hook][] = array(
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return true;
    }

    function do_action($hook, ...$args) {
        global $mock_action_hooks;
        if (isset($mock_action_hooks[$hook])) {
            foreach ($mock_action_hooks[$hook] as $action) {
                $callback = $action['callback'];
                $accepted_args = $action['accepted_args'];
                
                // Limit args to accepted_args count
                $limited_args = array_slice($args, 0, $accepted_args);
                
                if (is_callable($callback)) {
                    call_user_func_array($callback, $limited_args);
                }
            }
        }
        return true;
    }

    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }

    function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        global $mock_registered_scripts;
        $mock_registered_scripts[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer
        );
        return true;
    }

    function wp_enqueue_script($handle) {
        global $mock_enqueued_scripts;
        $mock_enqueued_scripts[] = $handle;
        return true;
    }

    function wp_enqueue_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        global $mock_enqueued_styles;
        $mock_enqueued_styles[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media
        );
        return true;
    }

    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function) {
        global $mock_menu_pages;
        $mock_menu_pages[] = array(
            'parent_slug' => $parent_slug,
            'page_title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'menu_slug' => $menu_slug,
            'function' => $function
        );
        return 'woocommerce_page_' . $menu_slug;
    }

    function plugin_dir_url($file) {
        return 'https://example.test/wp-content/plugins/woo-preproduct/';
    }
    
    function plugin_dir_path($file) {
        return '/fake/wordpress/path/wp-content/plugins/woo-preproduct/';
    }
    
    function plugins_url($path = '', $plugin = '') {
        $base_url = 'https://example.test/wp-content/plugins/';
        if ($plugin) {
            $base_url .= dirname($plugin) . '/';
        } else {
            $base_url .= 'woo-preproduct/';
        }
        return $base_url . ltrim($path, '/');
    }
    
    function plugin_basename($file) {
        return 'woo-preproduct/' . basename($file);
    }
    
    function determine_locale() {
        return 'en_US';
    }
    
    function unload_textdomain($domain) {
        return true;
    }
    
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        global $mock_loaded_textdomains;
        $mock_loaded_textdomains[$domain] = array(
            'domain' => $domain,
            'path' => $plugin_rel_path
        );
        return true;
    }
    
    function untrailingslashit($string) {
        return rtrim($string, '/\\');
    }
    
    if (!function_exists('str_starts_with')) {
        function str_starts_with($haystack, $needle) {
            return strpos($haystack, $needle) === 0;
        }
    }
    
    if (!function_exists('str_ends_with')) {
        function str_ends_with($haystack, $needle) {
            return substr($haystack, -strlen($needle)) === $needle;
        }
    }
    
    if (!function_exists('str_contains')) {
        function str_contains($haystack, $needle) {
            return strpos($haystack, $needle) !== false;
        }
    }

    function admin_url($path) {
        return 'https://example.test/wp-admin/' . $path;
    }

    function site_url($path = '', $scheme = null) {
        global $test_site_url;
        $base_url = $test_site_url ?? 'https://example.test';
        return $base_url . $path;
    }

    function get_option($option, $default = false) {
        global $mock_options;
        if ($option === 'siteurl' || $option === 'home') {
            return 'https://example.test';
        }
        return isset($mock_options[$option]) ? $mock_options[$option] : $default;
    }

    function __($text, $domain = 'default') {
        return $text;
    }

    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function esc_attr__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    // Mock WooCommerce function
    function WC() {
        return new stdClass();
    }

    // Mock is_shop, is_product_category functions for WooCommerce
    function is_shop() {
        global $mock_is_shop;
        return $mock_is_shop ?? false;
    }

    function is_product_category() {
        global $mock_is_product_category;
        return $mock_is_product_category ?? false;
    }

    function is_product_tag() {
        global $mock_is_product_tag;
        return $mock_is_product_tag ?? false;
    }

    function is_product() {
        global $mock_is_product;
        return $mock_is_product ?? false;
    }

    // Mock WooCommerce webhook classes
    class WC_Webhook {
        private $data = array();
        
        public function set_name($name) {
            $this->data['name'] = $name;
        }
        
        public function set_status($status) {
            $this->data['status'] = $status;
        }
        
        public function set_topic($topic) {
            $this->data['topic'] = $topic;
        }
        
        public function set_delivery_url($url) {
            $this->data['delivery_url'] = $url;
        }
        
        public function set_secret($secret) {
            $this->data['secret'] = $secret;
        }
        
        public function get_topic() {
            return $this->data['topic'] ?? '';
        }
        
        public function get_delivery_url() {
            return $this->data['delivery_url'] ?? 'https://example.test/webhook';
        }
        
        public function get_status() {
            return $this->data['status'] ?? 'active';
        }
        
        public function save() {
            global $mock_webhook_saved;
            $mock_webhook_saved = $this->data;
            return 123; // Mock webhook ID
        }
        
        public function delete($force = false) {
            global $mock_webhook_deleted;
            $mock_webhook_deleted = true;
            return true;
        }
    }
    
    function wc_get_webhook($id) {
        if ($id === 123) {
            $webhook = new WC_Webhook();
            $webhook->set_topic('plugin.uninstalled');
            return $webhook;
        }
        return false;
    }
    
    function wp_generate_password($length = 12, $special_chars = true) {
        return 'mock_generated_password_' . $length;
    }
    
    function update_option($option, $value) {
        global $mock_options;
        $mock_options[$option] = $value;
        return true;
    }
    
    function delete_option($option) {
        global $mock_options;
        unset($mock_options[$option]);
        return true;
    }
    
    // Mock WooCommerce Data Store
    class WC_Data_Store {
        public static function load($type) {
            if ($type === 'webhook') {
                return new Mock_Webhook_Data_Store();
            }
            return new stdClass();
        }
    }
    
    class Mock_Webhook_Data_Store {
        public function get_webhooks_ids() {
            // Return some mock webhook IDs
            return array(123, 456);
        }
    }
} 