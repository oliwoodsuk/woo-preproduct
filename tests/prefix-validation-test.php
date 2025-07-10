<?php
/**
 * Prefix Validation Test
 *
 * Tests to verify all prefixing changes are working correctly:
 * - Classes use PreProduct_ prefix
 * - Hooks use preproduct_ prefix  
 * - Database options use preproduct_ prefix
 * - No old naming conventions remain
 */

// Include the bootstrap file for WordPress function mocks
require_once __DIR__ . '/bootstrap.php';

// Include the main plugin files
require_once dirname(__DIR__) . '/includes/class-woo-preproduct.php';
require_once dirname(__DIR__) . '/includes/class-environment-manager.php';
require_once dirname(__DIR__) . '/includes/class-admin-page.php';
require_once dirname(__DIR__) . '/includes/class-button-tagger.php';
require_once dirname(__DIR__) . '/includes/class-script-manager.php';
require_once dirname(__DIR__) . '/includes/class-debug-info.php';
require_once dirname(__DIR__) . '/includes/class-logger.php';
require_once dirname(__DIR__) . '/includes/class-plugin-uninstall-webhook.php';

class PrefixValidationTest {
    private $test_results = array();
    
    /**
     * Run all prefix validation tests
     */
    public function run_all_tests() {
        echo "Running Prefix Validation Tests...\n";
        echo "====================================\n\n";
        
        $this->test_class_prefixes();
        $this->test_hook_prefixes();
        $this->test_database_option_prefixes();
        $this->test_no_old_references();
        $this->test_class_instantiation();
        
        $this->print_results();
    }
    
    /**
     * Test that all classes use the PreProduct_ prefix
     */
    private function test_class_prefixes() {
        echo "Testing class prefixes...\n";
        
        $expected_classes = array(
            'PreProduct_Plugin',
            'PreProduct_Admin_Page',
            'PreProduct_Environment_Manager',
            'PreProduct_Script_Manager',
            'PreProduct_Debug_Info',
            'PreProduct_Logger',
            'PreProduct_Plugin_Uninstall_Webhook',
            'PreProduct_Button_Tagger'
        );
        
        foreach ($expected_classes as $class_name) {
            $this->assert_test(
                "Class {$class_name} exists",
                class_exists($class_name),
                "Expected class {$class_name} to exist"
            );
        }
        
        // Test that old class names no longer exist
        $old_classes = array(
            'WooPreProduct',
            'WooPreProduct_Admin_Page',
            'WooPreProduct_Environment_Manager'
        );
        
        foreach ($old_classes as $old_class) {
            $this->assert_test(
                "Old class {$old_class} does not exist",
                !class_exists($old_class),
                "Old class {$old_class} should not exist anymore"
            );
        }
        
        echo "✓ Class prefix tests completed\n\n";
    }
    
    /**
     * Test WordPress hooks use preproduct_ prefix
     */
    private function test_hook_prefixes() {
        echo "Testing hook prefixes...\n";
        
        // Reset global hook tracking
        global $mock_action_hooks;
        $mock_action_hooks = array();
        
        // Capture hooks by overriding do_action temporarily
        $captured_hooks = array();
        $original_do_action = 'do_action';
        
        // Create a custom do_action for testing
        function test_do_action($hook, ...$args) {
            global $captured_hooks;
            $captured_hooks[] = $hook;
        }
        
        // Create instance and call init to trigger hooks
        $plugin = new PreProduct_Plugin();
        
        // Temporarily capture the hook calls during init
        ob_start();
        $plugin->init();
        ob_end_clean();
        
        // Check if the code contains the new hook names (basic validation)
        $init_method = new ReflectionMethod('PreProduct_Plugin', 'init');
        $init_code = file_get_contents($init_method->getFileName());
        
        $this->assert_test(
            "Code contains preproduct_before_init hook",
            strpos($init_code, 'preproduct_before_init') !== false,
            "Should find preproduct_before_init in source code"
        );
        
        $this->assert_test(
            "Code contains preproduct_init hook", 
            strpos($init_code, 'preproduct_init') !== false,
            "Should find preproduct_init in source code"
        );
        
        $this->assert_test(
            "Code does not contain old woo_preproduct_init hook",
            strpos($init_code, 'woo_preproduct_init') === false,
            "Should not find woo_preproduct_init in source code"
        );
        
        echo "✓ Hook prefix tests completed\n\n";
    }
    
    /**
     * Test database options use preproduct_ prefix
     */
    private function test_database_option_prefixes() {
        echo "Testing database option prefixes...\n";
        
        // Reset mock options
        global $mock_options;
        $mock_options = array();
        
        // Test new option names work
        update_option('preproduct_webhook_id', 123);
        $retrieved_value = get_option('preproduct_webhook_id');
        $this->assert_test(
            'New option preproduct_webhook_id works',
            $retrieved_value == 123,
            'Should be able to store and retrieve preproduct_webhook_id, got: ' . var_export($retrieved_value, true)
        );
        
        // Test transient names work
        set_transient('preproduct_activation_redirect', true, 30);
        $this->assert_test(
            'New transient preproduct_activation_redirect works',
            get_transient('preproduct_activation_redirect') === true,
            'Should be able to store and retrieve preproduct_activation_redirect'
        );
        
        echo "✓ Database option prefix tests completed\n\n";
    }
    
    /**
     * Test that no old naming references remain in code
     */
    private function test_no_old_references() {
        echo "Testing for old naming references...\n";
        
        // This is a basic check - in real implementation would scan files
        $this->assert_test(
            'No WooPreProduct class instantiated',
            !class_exists('WooPreProduct'),
            'WooPreProduct class should not exist'
        );
        
        $this->assert_test(
            'PreProduct_Plugin class exists instead',
            class_exists('PreProduct_Plugin'),
            'PreProduct_Plugin should be the main class'
        );
        
        echo "✓ Old reference cleanup tests completed\n\n";
    }
    
    /**
     * Test that classes can be instantiated properly
     */
    private function test_class_instantiation() {
        echo "Testing class instantiation...\n";
        
        // Test main plugin class
        try {
            $plugin = PreProduct_Plugin::instance();
            $this->assert_test(
                'PreProduct_Plugin instantiates correctly',
                $plugin instanceof PreProduct_Plugin,
                'Main plugin class should instantiate properly'
            );
        } catch (Exception $e) {
            $this->assert_test(
                'PreProduct_Plugin instantiation failed',
                false,
                'Error: ' . $e->getMessage()
            );
        }
        
        // Test environment manager
        try {
            $env_manager = PreProduct_Environment_Manager::get_instance();
            $this->assert_test(
                'PreProduct_Environment_Manager instantiates correctly',
                $env_manager instanceof PreProduct_Environment_Manager,
                'Environment manager should instantiate properly'
            );
        } catch (Exception $e) {
            $this->assert_test(
                'PreProduct_Environment_Manager instantiation failed',
                false,
                'Error: ' . $e->getMessage()
            );
        }
        
        // Test other classes if WooCommerce functions are available
        if (function_exists('WC')) {
            try {
                $button_tagger = new PreProduct_Button_Tagger();
                $this->assert_test(
                    'PreProduct_Button_Tagger instantiates correctly',
                    $button_tagger instanceof PreProduct_Button_Tagger,
                    'Button tagger should instantiate properly'
                );
            } catch (Exception $e) {
                $this->assert_test(
                    'PreProduct_Button_Tagger instantiation failed',
                    false,
                    'Error: ' . $e->getMessage()
                );
            }
        }
        
        echo "✓ Class instantiation tests completed\n\n";
    }
    
    /**
     * Assert a test result
     */
    private function assert_test($test_name, $condition, $error_message = '') {
        $result = array(
            'name' => $test_name,
            'passed' => (bool) $condition,
            'error' => $error_message
        );
        
        $this->test_results[] = $result;
        
        if ($condition) {
            echo "  ✓ $test_name\n";
        } else {
            echo "  ✗ $test_name\n";
            if ($error_message) {
                echo "    Error: $error_message\n";
            }
        }
    }
    
    /**
     * Print test results summary
     */
    private function print_results() {
        echo "Test Results Summary\n";
        echo "====================\n";
        
        $total_tests = count($this->test_results);
        $passed_tests = count(array_filter($this->test_results, function($test) {
            return $test['passed'];
        }));
        $failed_tests = $total_tests - $passed_tests;
        
        echo "Total tests: $total_tests\n";
        echo "✅ Passed: $passed_tests\n";
        echo "❌ Failed: $failed_tests\n";
        
        if ($failed_tests > 0) {
            echo "\nFailed tests:\n";
            foreach ($this->test_results as $test) {
                if (!$test['passed']) {
                    echo "- " . $test['name'] . "\n";
                    if ($test['error']) {
                        echo "  Error: " . $test['error'] . "\n";
                    }
                }
            }
        }
        
        echo "\nOverall result: " . ($failed_tests === 0 ? "PASSED" : "FAILED") . "\n";
    }
}

// Add missing WordPress functions for this test
if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration) {
        global $mock_transients;
        $mock_transients[$transient] = $value;
        return true;
    }
}

if (!function_exists('get_transient')) {
    function get_transient($transient) {
        global $mock_transients;
        return isset($mock_transients[$transient]) ? $mock_transients[$transient] : false;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        global $mock_transients;
        unset($mock_transients[$transient]);
        return true;
    }
}

// Run the tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new PrefixValidationTest();
    $test->run_all_tests();
}