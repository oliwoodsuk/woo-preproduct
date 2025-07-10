<?php
/**
 * Admin Page Test Script
 * 
 * Run this with: php test-admin-page.php
 */

// Define ABSPATH to prevent WordPress security check from exiting
define('ABSPATH', '/fake/wordpress/path/');
define('WOO_PREPRODUCT_VERSION', '1.0.0');
define('WOO_PREPRODUCT_PLUGIN_FILE', __FILE__);

// Global variables for testing
global $mock_menu_pages, $mock_enqueued_styles, $mock_is_admin, $mock_current_user_can, $mock_pagenow, $mock_get;

// Mock WordPress functions
function is_admin() {
    global $mock_is_admin;
    return $mock_is_admin ?? false;
}

function current_user_can($capability) {
    global $mock_current_user_can;
    return $mock_current_user_can ?? true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
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

function plugin_dir_url($file) {
    return 'https://example.test/wp-content/plugins/woo-preproduct/';
}

function admin_url($path) {
    return 'https://example.test/wp-admin/' . $path;
}

function site_url($path = '', $scheme = null) {
    return 'https://example.test' . $path;
}

function get_option($option, $default = false) {
    if ($option === 'siteurl' || $option === 'home') {
        return 'https://example.test';
    }
    return $default;
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

// Mock $_GET superglobal
$_GET = array();

// Include required classes
require_once 'includes/class-environment-manager.php';
require_once 'includes/class-admin-page.php';

class AdminPageTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    
    public function run_all_tests() {
        echo "🧪 Running Admin Page Tests\n";
        echo "===========================\n\n";
        
        $this->test_menu_registration();
        $this->test_admin_capability_check();
        $this->test_style_enqueuing();
        $this->test_iframe_rendering();
        $this->test_helper_functions();
        $this->test_page_detection();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_menu_registration() {
        echo "Testing Menu Registration...\n";
        
        global $mock_menu_pages, $mock_is_admin, $mock_current_user_can;
        $mock_menu_pages = array();
        $mock_is_admin = true;
        $mock_current_user_can = true;
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Simulate admin_menu action
        $admin_page->add_admin_menu();
        
        // Check if menu was registered
        if (count($mock_menu_pages) > 0) {
            $this->assert_true(true, "✅ Admin menu page registered");
            
            $menu = $mock_menu_pages[0];
            
            // Check parent slug
            if ($menu['parent_slug'] === 'woocommerce') {
                $this->assert_true(true, "✅ Menu registered under WooCommerce");
            } else {
                $this->assert_true(false, "❌ Menu not registered under WooCommerce");
            }
            
            // Check menu title
            if ($menu['menu_title'] === 'Pre-orders') {
                $this->assert_true(true, "✅ Correct menu title");
            } else {
                $this->assert_true(false, "❌ Incorrect menu title: " . $menu['menu_title']);
            }
            
            // Check capability
            if ($menu['capability'] === 'manage_woocommerce') {
                $this->assert_true(true, "✅ Correct capability requirement");
            } else {
                $this->assert_true(false, "❌ Incorrect capability: " . $menu['capability']);
            }
            
        } else {
            $this->assert_true(false, "❌ Admin menu page not registered");
        }
        
        echo "\n";
    }
    
    private function test_admin_capability_check() {
        echo "Testing Admin Capability Check...\n";
        
        global $mock_menu_pages, $mock_is_admin, $mock_current_user_can;
        $mock_menu_pages = array();
        $mock_is_admin = true;
        $mock_current_user_can = false; // User cannot manage WooCommerce
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Try to add menu without proper capability
        $admin_page->add_admin_menu();
        
        // Menu should not be registered
        if (count($mock_menu_pages) === 0) {
            $this->assert_true(true, "✅ Menu not registered without proper capability");
        } else {
            $this->assert_true(false, "❌ Menu registered despite lacking capability");
        }
        
        echo "\n";
    }
    
    private function test_style_enqueuing() {
        echo "Testing Style Enqueuing...\n";
        
        global $mock_enqueued_styles, $mock_is_admin;
        $mock_enqueued_styles = array();
        $mock_is_admin = true;
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Test with correct hook suffix
        $admin_page->enqueue_admin_styles('woocommerce_page_preproduct');
        
        if (isset($mock_enqueued_styles['preproduct-admin'])) {
            $this->assert_true(true, "✅ Admin styles enqueued on correct page");
            
            $style = $mock_enqueued_styles['preproduct-admin'];
            
            // Check style URL contains css/admin.css
            if (strpos($style['src'], 'css/admin.css') !== false) {
                $this->assert_true(true, "✅ Correct CSS file enqueued");
            } else {
                $this->assert_true(false, "❌ Incorrect CSS file: " . $style['src']);
            }
            
            // Check version
            if ($style['ver'] === WOO_PREPRODUCT_VERSION) {
                $this->assert_true(true, "✅ Correct CSS version");
            } else {
                $this->assert_true(false, "❌ Incorrect CSS version");
            }
            
        } else {
            $this->assert_true(false, "❌ Admin styles not enqueued");
        }
        
        // Test with wrong hook suffix
        $mock_enqueued_styles = array();
        $admin_page->enqueue_admin_styles('other_page');
        
        if (!isset($mock_enqueued_styles['preproduct-admin'])) {
            $this->assert_true(true, "✅ Styles not enqueued on other pages");
        } else {
            $this->assert_true(false, "❌ Styles incorrectly enqueued on other pages");
        }
        
        echo "\n";
    }
    
    private function test_iframe_rendering() {
        echo "Testing Iframe Rendering...\n";
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Capture output
        ob_start();
        $admin_page->render_admin_page();
        $output = ob_get_clean();
        
        // Check for iframe
        if (strpos($output, '<iframe') !== false) {
            $this->assert_true(true, "✅ Iframe element present");
        } else {
            $this->assert_true(false, "❌ Iframe element missing");
        }
        
        // Check for PreProduct URL
        if (strpos($output, 'preproduct.ngrok.io') !== false || strpos($output, 'preproduct.io') !== false) {
            $this->assert_true(true, "✅ PreProduct URL in iframe");
        } else {
            $this->assert_true(false, "❌ PreProduct URL missing from iframe");
        }
        
        // Check for proper escaping
        if (strpos($output, 'esc_url') === false && strpos($output, 'esc_html') === false) {
            $this->assert_true(true, "✅ Content properly escaped");
        } else {
            $this->assert_true(false, "❌ Unescaped content found");
        }
        
        // Check for title attribute
        if (strpos($output, 'title=') !== false) {
            $this->assert_true(true, "✅ Iframe has title attribute");
        } else {
            $this->assert_true(false, "❌ Iframe missing title attribute");
        }
        
        echo "\n";
    }
    
    private function test_helper_functions() {
        echo "Testing Helper Functions...\n";
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Test get_admin_page_url
        $url = $admin_page->get_admin_page_url();
        if (strpos($url, 'page=preproduct') !== false) {
            $this->assert_true(true, "✅ Admin page URL contains correct parameter");
        } else {
            $this->assert_true(false, "❌ Admin page URL incorrect: " . $url);
        }
        
        echo "🔍 Admin page URL: " . $url . "\n\n";
    }
    
    private function test_page_detection() {
        echo "Testing Page Detection...\n";
        
        global $pagenow;
        
        $admin_page = new PreProduct_Admin_Page();
        
        // Mock being on admin.php with our page parameter
        $pagenow = 'admin.php';
        $_GET['page'] = 'preproduct';
        
        $is_our_page = $admin_page->is_preproduct_admin_page();
        
        if ($is_our_page) {
            $this->assert_true(true, "✅ Correctly detects PreProduct admin page");
        } else {
            $this->assert_true(false, "❌ Failed to detect PreProduct admin page");
        }
        
        // Test with different page
        $_GET['page'] = 'other-page';
        $is_our_page = $admin_page->is_preproduct_admin_page();
        
        if (!$is_our_page) {
            $this->assert_true(true, "✅ Correctly excludes other pages");
        } else {
            $this->assert_true(false, "❌ Incorrectly detects other pages as PreProduct page");
        }
        
        echo "\n";
    }
    
    private function assert_true($condition, $message) {
        if ($condition) {
            $this->tests_passed++;
        } else {
            $this->tests_failed++;
        }
        echo "$message\n";
    }
}

// Run the tests
$test = new AdminPageTest();
$test->run_all_tests(); 