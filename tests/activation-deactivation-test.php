<?php
/**
 * Test Suite for Plugin Activation and Deactivation
 *
 * This test simulates WordPress plugin activation and deactivation
 * to verify that all hooks and procedures work correctly.
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Include the bootstrap file
require_once __DIR__ . '/bootstrap.php';

/**
 * Test class for Plugin Activation and Deactivation
 */
class ActivationDeactivationTest
{
    private $test_results = [];
    private $activation_hooks = [];
    private $deactivation_hooks = [];

    /**
     * Run all tests
     */
    public function run_all_tests()
    {
        echo "Running Plugin Activation and Deactivation Tests...\n";
        echo "===================================================\n\n";

        // Test activation hooks
        $this->test_activation_hooks();
        echo "\n";

        // Test deactivation hooks
        $this->test_deactivation_hooks();
        echo "\n";

        // Test uninstall hooks
        $this->test_uninstall_hooks();
        echo "\n";

        // Test activation function existence
        $this->test_activation_functions();
        echo "\n";

        // Test plugin file structure
        $this->test_plugin_structure();
        echo "\n";

        // Display results
        $this->display_test_results();
    }

    /**
     * Test activation hooks
     */
    private function test_activation_hooks()
    {
        echo "Testing activation hooks...\n";

        // Read plugin file
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');

        // Check for activation hook registration
        $this->assert_true(strpos($plugin_content, 'register_activation_hook') !== false, "Plugin registers activation hook");

        // Check for correct activation function
        $this->assert_true(strpos($plugin_content, "register_activation_hook(__FILE__, 'preproduct_activate')") !== false, "Uses correct activation function name");

        // Check activation function exists
        $this->assert_true(strpos($plugin_content, 'function preproduct_activate()') !== false, "Activation function is defined");

        // Check for old function names
        $this->assert_false(strpos($plugin_content, 'woo_preproduct_activate') !== false, "Old activation function name not used");

        echo "✓ Activation hook tests completed\n";
    }

    /**
     * Test deactivation hooks
     */
    private function test_deactivation_hooks()
    {
        echo "Testing deactivation hooks...\n";

        // Read plugin file
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');

        // Check for deactivation hook registration
        $this->assert_true(strpos($plugin_content, 'register_deactivation_hook') !== false, "Plugin registers deactivation hook");

        // Check for correct deactivation function
        $this->assert_true(strpos($plugin_content, "register_deactivation_hook(__FILE__, 'preproduct_deactivate')") !== false, "Uses correct deactivation function name");

        // Check deactivation function exists
        $this->assert_true(strpos($plugin_content, 'function preproduct_deactivate()') !== false, "Deactivation function is defined");

        // Check for old function names
        $this->assert_false(strpos($plugin_content, 'woo_preproduct_deactivate') !== false, "Old deactivation function name not used");

        echo "✓ Deactivation hook tests completed\n";
    }

    /**
     * Test uninstall hooks
     */
    private function test_uninstall_hooks()
    {
        echo "Testing uninstall hooks...\n";

        // Check uninstall.php exists
        $this->assert_true(file_exists(__DIR__ . '/../uninstall.php'), "Uninstall.php file exists");

        // Read uninstall file
        $uninstall_content = file_get_contents(__DIR__ . '/../uninstall.php');

        // Check for proper uninstall security
        $this->assert_true(strpos($uninstall_content, 'WP_UNINSTALL_PLUGIN') !== false, "Uninstall file has proper security check");

        // Check for uninstall webhook functionality
        $this->assert_true(strpos($uninstall_content, 'PreProduct_Plugin_Uninstall_Webhook::trigger_uninstall_webhook') !== false, "Uninstall triggers webhook");

        // Check for cleanup functionality
        $this->assert_true(strpos($uninstall_content, 'PreProduct_Plugin_Uninstall_Webhook::cleanup_webhook') !== false, "Uninstall cleans up webhook");

        echo "✓ Uninstall hook tests completed\n";
    }

    /**
     * Test activation functions
     */
    private function test_activation_functions()
    {
        echo "Testing activation functions...\n";

        // Test activation function structure
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');

        // Check activation function content
        $this->assert_true(strpos($plugin_content, 'PreProduct_Logger::log_activation') !== false, "Activation logs event");
        $this->assert_true(strpos($plugin_content, "do_action('preproduct_activated')") !== false, "Activation triggers hook");
        $this->assert_true(strpos($plugin_content, "set_transient('preproduct_activation_redirect', true, 30)") !== false, "Activation sets redirect transient");

        // Check deactivation function content
        $this->assert_true(strpos($plugin_content, 'PreProduct_Logger::log_deactivation') !== false, "Deactivation logs event");

        echo "✓ Activation function tests completed\n";
    }

    /**
     * Test plugin file structure
     */
    private function test_plugin_structure()
    {
        echo "Testing plugin file structure...\n";

        // Test main plugin file
        $this->assert_true(file_exists(__DIR__ . '/../woo-preproduct.php'), "Main plugin file exists");

        // Test required directories
        $this->assert_true(is_dir(__DIR__ . '/../includes'), "Includes directory exists");
        $this->assert_true(is_dir(__DIR__ . '/../assets'), "Assets directory exists");
        $this->assert_true(is_dir(__DIR__ . '/../tests'), "Tests directory exists");

        // Test required files
        $this->assert_true(file_exists(__DIR__ . '/../README.md'), "README.md exists");
        $this->assert_true(file_exists(__DIR__ . '/../includes/woo-preproduct-functions.php'), "Functions file exists");

        // Test plugin header
        $plugin_content = file_get_contents(__DIR__ . '/../woo-preproduct.php');
        $this->assert_true(strpos($plugin_content, 'Plugin Name:') !== false, "Plugin header has name");
        $this->assert_true(strpos($plugin_content, 'Version:') !== false, "Plugin header has version");
        $this->assert_true(strpos($plugin_content, 'Description:') !== false, "Plugin header has description");
        $this->assert_true(strpos($plugin_content, 'Author:') !== false, "Plugin header has author");

        echo "✓ Plugin structure tests completed\n";
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
    $test = new ActivationDeactivationTest();
    $test->run_all_tests();
}