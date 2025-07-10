<?php
/**
 * Test Suite for Hook and Filter Refactoring
 *
 * This test suite verifies that all hooks and filters have been properly
 * refactored to use the new prefixed function names and follow WordPress
 * naming conventions.
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Include the bootstrap file
require_once __DIR__ . '/bootstrap.php';

// We'll analyze the code without executing it to avoid WordPress dependency issues

/**
 * Test class for Hook and Filter Refactoring
 */
class HookFilterRefactoringTest
{
    private $test_results = [];
    private $hooks_registered = [];
    private $filters_registered = [];

    /**
     * Run all tests
     */
    public function run_all_tests()
    {
        echo "Running Hook and Filter Refactoring Tests...\n";
        echo "================================================\n\n";

        // Test action hooks
        $this->test_action_hooks();
        echo "\n";

        // Test filter hooks
        $this->test_filter_hooks();
        echo "\n";

        // Test custom hook names
        $this->test_custom_hook_names();
        echo "\n";

        // Test hook callbacks
        $this->test_hook_callbacks();
        echo "\n";

        // Test hook integration
        $this->test_hook_integration();
        echo "\n";

        // Display results
        $this->display_test_results();
    }

    /**
     * Test action hooks
     */
    private function test_action_hooks()
    {
        echo "Testing action hooks...\n";

        // Test that old function names are not used in add_action calls
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');
        $includes_content = '';
        
        // Read all includes files
        $includes_files = glob(__DIR__ . '/../includes/*.php');
        foreach ($includes_files as $file) {
            $includes_content .= file_get_contents($file);
        }
        
        $all_content = $plugin_content . $includes_content;

        // Check for old function names in add_action calls
        $old_function_pattern = '/add_action\s*\(\s*[\'"][^\'"]++[\'"],\s*[\'"]woo_preproduct_/';
        $old_matches = preg_match_all($old_function_pattern, $all_content);
        
        $this->assert_equals(0, $old_matches, "No old woo_preproduct_ function names in add_action calls");

        // Check for proper prefixed function names in add_action calls
        $new_function_pattern = '/add_action\s*\(\s*[\'"][^\'"]++[\'"],\s*[\'"]preproduct_/';
        $new_matches = preg_match_all($new_function_pattern, $all_content);
        
        $this->assert_true($new_matches >= 2, "Found prefixed preproduct_ function names in add_action calls");

        // Verify specific action hooks exist
        $this->assert_true(strpos($all_content, "add_action('admin_notices', 'preproduct_woocommerce_missing_notice')") !== false, "Uses preproduct_woocommerce_missing_notice");
        $this->assert_true(strpos($all_content, "add_action('admin_init', 'preproduct_activation_redirect')") !== false, "Uses preproduct_activation_redirect");
        $this->assert_true(strpos($all_content, "add_action('plugins_loaded', 'preproduct_init')") !== false, "Uses preproduct_init");

        echo "✓ Action hook tests completed\n";
    }

    /**
     * Test filter hooks
     */
    private function test_filter_hooks()
    {
        echo "Testing filter hooks...\n";

        // Read all plugin files
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');
        $includes_content = '';
        
        $includes_files = glob(__DIR__ . '/../includes/*.php');
        foreach ($includes_files as $file) {
            $includes_content .= file_get_contents($file);
        }
        
        $all_content = $plugin_content . $includes_content;

        // Check for old function names in add_filter calls
        $old_function_pattern = '/add_filter\s*\(\s*[\'"][^\'"]++[\'"],\s*[\'"]woo_preproduct_/';
        $old_matches = preg_match_all($old_function_pattern, $all_content);
        
        $this->assert_equals(0, $old_matches, "No old woo_preproduct_ function names in add_filter calls");

        // Check for proper filter callback usage (many use class methods, not standalone functions)
        $this->assert_true(strpos($all_content, "add_filter('script_loader_tag', array(\$this, 'add_defer_attribute')") !== false, "Script loader filter uses proper callback");
        $this->assert_true(strpos($all_content, "add_filter('woocommerce_loop_add_to_cart_link', array(\$this, 'addPreproductAttributes')") !== false, "WooCommerce filter uses proper callback");

        // Verify specific filter hooks exist
        $this->assert_true(strpos($all_content, "add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'preproduct_action_links')") !== false, "Uses preproduct_action_links");

        echo "✓ Filter hook tests completed\n";
    }

    /**
     * Test custom hook names
     */
    private function test_custom_hook_names()
    {
        echo "Testing custom hook names...\n";

        // Read all plugin files
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');
        $includes_content = '';
        
        $includes_files = glob(__DIR__ . '/../includes/*.php');
        foreach ($includes_files as $file) {
            $includes_content .= file_get_contents($file);
        }
        
        $all_content = $plugin_content . $includes_content;

        // Test custom action hooks
        $this->assert_true(strpos($all_content, "do_action('preproduct_before_init')") !== false, "Custom hook preproduct_before_init exists");
        $this->assert_true(strpos($all_content, "do_action('preproduct_init')") !== false, "Custom hook preproduct_init exists");
        $this->assert_true(strpos($all_content, "do_action('preproduct_activated')") !== false, "Custom hook preproduct_activated exists");

        // Test custom filter hooks
        $this->assert_true(strpos($all_content, "apply_filters('preproduct_load_script', true)") !== false, "Custom filter preproduct_load_script exists");
        $this->assert_true(strpos($all_content, "apply_filters('preproduct_enable_for_product', true, \$product)") !== false, "Custom filter preproduct_enable_for_product exists");

        // Check for old hook names that should not exist
        $this->assert_false(strpos($all_content, "do_action('woo_preproduct_") !== false, "No old woo_preproduct_ action hooks");
        $this->assert_false(strpos($all_content, "apply_filters('woo_preproduct_") !== false, "No old woo_preproduct_ filter hooks");

        echo "✓ Custom hook name tests completed\n";
    }

    /**
     * Test hook callbacks
     */
    private function test_hook_callbacks()
    {
        echo "Testing hook callbacks...\n";

        // Read plugin file to verify function definitions exist
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');
        $functions_content = file_get_contents(__DIR__ . '/../includes/woo-preproduct-functions.php');
        
        // Test that all callback functions are defined in the code
        $this->assert_true(strpos($plugin_content, 'function preproduct_woocommerce_missing_notice') !== false, "preproduct_woocommerce_missing_notice function is defined");
        $this->assert_true(strpos($plugin_content, 'function preproduct_activation_redirect') !== false, "preproduct_activation_redirect function is defined");
        $this->assert_true(strpos($plugin_content, 'function preproduct_init') !== false, "preproduct_init function is defined");
        $this->assert_true(strpos($plugin_content, 'function preproduct_action_links') !== false, "preproduct_action_links function is defined");

        // Test that old callback functions are not defined
        $this->assert_false(strpos($plugin_content, 'function woo_preproduct_woocommerce_missing_notice') !== false, "Old woo_preproduct_woocommerce_missing_notice function is not defined");
        $this->assert_false(strpos($plugin_content, 'function woo_preproduct_activation_redirect') !== false, "Old woo_preproduct_activation_redirect function is not defined");
        $this->assert_false(strpos($plugin_content, 'function woo_preproduct_init') !== false, "Old woo_preproduct_init function is not defined");
        $this->assert_false(strpos($plugin_content, 'function woo_preproduct_action_links') !== false, "Old woo_preproduct_action_links function is not defined");

        echo "✓ Hook callback tests completed\n";
    }

    /**
     * Test hook integration
     */
    private function test_hook_integration()
    {
        echo "Testing hook integration...\n";

        // Test that class methods are properly referenced
        $includes_content = '';
        $includes_files = glob(__DIR__ . '/../includes/*.php');
        foreach ($includes_files as $file) {
            $includes_content .= file_get_contents($file);
        }

        // Check for proper array callback syntax
        $this->assert_true(strpos($includes_content, "array(\$this, 'add_admin_menu')") !== false, "Proper array callback syntax used");
        $this->assert_true(strpos($includes_content, "array(\$this, 'enqueue_frontend_script')") !== false, "Proper array callback syntax used");
        $this->assert_true(strpos($includes_content, "array(\$this, 'add_defer_attribute')") !== false, "Proper array callback syntax used");

        // Test that static class methods are properly referenced
        $this->assert_true(strpos($includes_content, "array(__CLASS__, 'handle_debug_request')") !== false, "Proper static array callback syntax used");

        echo "✓ Hook integration tests completed\n";
    }

    /**
     * Assert equals helper
     */
    private function assert_equals($expected, $actual, $message)
    {
        if ($expected === $actual) {
            echo "  ✓ $message\n";
            $this->test_results[] = ['status' => 'passed', 'message' => $message];
        } else {
            echo "  ❌ $message (Expected: $expected, Actual: $actual)\n";
            $this->test_results[] = ['status' => 'failed', 'message' => $message];
        }
    }

    /**
     * Assert true helper
     */
    private function assert_true($condition, $message)
    {
        if ($condition) {
            echo "  ✓ $message\n";
            $this->test_results[] = ['status' => 'passed', 'message' => $message];
        } else {
            echo "  ❌ $message\n";
            $this->test_results[] = ['status' => 'failed', 'message' => $message];
        }
    }

    /**
     * Assert false helper
     */
    private function assert_false($condition, $message)
    {
        if (!$condition) {
            echo "  ✓ $message\n";
            $this->test_results[] = ['status' => 'passed', 'message' => $message];
        } else {
            echo "  ❌ $message\n";
            $this->test_results[] = ['status' => 'failed', 'message' => $message];
        }
    }

    /**
     * Display test results
     */
    private function display_test_results()
    {
        $passed = array_filter($this->test_results, function($result) {
            return $result['status'] === 'passed';
        });
        
        $failed = array_filter($this->test_results, function($result) {
            return $result['status'] === 'failed';
        });

        echo "Test Results Summary\n";
        echo "====================\n";
        echo "Total tests: " . count($this->test_results) . "\n";
        echo "✅ Passed: " . count($passed) . "\n";
        echo "❌ Failed: " . count($failed) . "\n\n";

        if (count($failed) > 0) {
            echo "Failed tests:\n";
            foreach ($failed as $test) {
                echo "  - " . $test['message'] . "\n";
            }
            echo "\nOverall result: FAILED\n";
        } else {
            echo "Overall result: PASSED\n";
        }
    }
}

// Run the tests
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $test = new HookFilterRefactoringTest();
    $test->run_all_tests();
}