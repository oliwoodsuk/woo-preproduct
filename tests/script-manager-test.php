<?php
/**
 * Script Manager Test Script
 * 
 * Run this with: php tests/script-manager-test.php
 */

// Include shared bootstrap
require_once __DIR__ . '/bootstrap.php';

// Include required classes
require_once 'includes/class-environment-manager.php';
require_once 'includes/class-script-manager.php';

class ScriptManagerTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    
    public function run_all_tests() {
        echo "🧪 Running Script Manager Tests\n";
        echo "==============================\n\n";
        
        $this->test_script_registration();
        $this->test_defer_attribute();
        $this->test_admin_page_exclusion();
        $this->test_script_loading_conditions();
        $this->test_environment_url_usage();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_script_registration() {
        echo "Testing Script Registration...\n";
        
        global $mock_registered_scripts, $mock_enqueued_scripts, $mock_is_admin;
        $mock_registered_scripts = array();
        $mock_enqueued_scripts = array();
        $mock_is_admin = false;
        
        $script_manager = new PreProduct_Script_Manager();
        
        // Simulate frontend script enqueuing
        $script_manager->enqueue_frontend_script();
        
        // Check if script was registered
        if (isset($mock_registered_scripts['preproduct-embed'])) {
            $this->assert_true(true, "✅ PreProduct embed script registered");
            
            $script_data = $mock_registered_scripts['preproduct-embed'];
            
            // Check script URL
            if (strpos($script_data['src'], 'preproduct-embed.js') !== false) {
                $this->assert_true(true, "✅ Script URL contains correct filename");
            } else {
                $this->assert_true(false, "❌ Script URL doesn't contain expected filename");
            }
            
            // Check version
            if ($script_data['ver'] === WOO_PREPRODUCT_VERSION) {
                $this->assert_true(true, "✅ Script version matches plugin version");
            } else {
                $this->assert_true(false, "❌ Script version doesn't match plugin version");
            }
            
            // Check footer loading
            if ($script_data['in_footer'] === true) {
                $this->assert_true(true, "✅ Script set to load in footer");
            } else {
                $this->assert_true(false, "❌ Script not set to load in footer");
            }
            
        } else {
            $this->assert_true(false, "❌ PreProduct embed script not registered");
        }
        
        // Check if script was enqueued
        if (in_array('preproduct-embed', $mock_enqueued_scripts)) {
            $this->assert_true(true, "✅ PreProduct embed script enqueued");
        } else {
            $this->assert_true(false, "❌ PreProduct embed script not enqueued");
        }
        
        echo "\n";
    }
    
    private function test_defer_attribute() {
        echo "Testing Defer Attribute Addition...\n";
        
        $script_manager = new PreProduct_Script_Manager();
        
        // Test with our script handle
        $original_tag = '<script type="text/javascript" src="https://preproduct.ngrok.io/preproduct-embed.js" id="preproduct-embed-js"></script>';
        $modified_tag = $script_manager->add_defer_attribute($original_tag, 'preproduct-embed');
        
        if (strpos($modified_tag, 'defer') !== false) {
            $this->assert_true(true, "✅ Defer attribute added to PreProduct script");
        } else {
            $this->assert_true(false, "❌ Defer attribute not added to PreProduct script");
        }
        
        // Test with other script handle (should not be modified)
        $other_tag = '<script type="text/javascript" src="https://example.com/other.js" id="other-script-js"></script>';
        $other_result = $script_manager->add_defer_attribute($other_tag, 'other-script');
        
        if (strpos($other_result, 'defer') === false) {
            $this->assert_true(true, "✅ Defer attribute not added to other scripts");
        } else {
            $this->assert_true(false, "❌ Defer attribute incorrectly added to other scripts");
        }
        
        echo "🔍 Modified tag: " . $modified_tag . "\n\n";
    }
    
    private function test_admin_page_exclusion() {
        echo "Testing Admin Page Exclusion...\n";
        
        global $mock_registered_scripts, $mock_enqueued_scripts, $mock_is_admin;
        $mock_registered_scripts = array();
        $mock_enqueued_scripts = array();
        $mock_is_admin = true; // Simulate admin page
        
        $script_manager = new PreProduct_Script_Manager();
        
        // Try to enqueue script on admin page
        $script_manager->enqueue_frontend_script();
        
        // Script should not be registered or enqueued on admin pages
        if (!isset($mock_registered_scripts['preproduct-embed'])) {
            $this->assert_true(true, "✅ Script not registered on admin pages");
        } else {
            $this->assert_true(false, "❌ Script should not be registered on admin pages");
        }
        
        if (!in_array('preproduct-embed', $mock_enqueued_scripts)) {
            $this->assert_true(true, "✅ Script not enqueued on admin pages");
        } else {
            $this->assert_true(false, "❌ Script should not be enqueued on admin pages");
        }
        
        echo "\n";
    }
    
    private function test_script_loading_conditions() {
        echo "Testing Script Loading Conditions...\n";
        
        global $mock_is_admin;
        $mock_is_admin = false;
        
        $script_manager = new PreProduct_Script_Manager();
        
        // Test should_load_script method
        $should_load = $script_manager->should_load_script();
        
        if ($should_load === true) {
            $this->assert_true(true, "✅ Script loading allowed on frontend with WooCommerce");
        } else {
            $this->assert_true(false, "❌ Script loading should be allowed on frontend with WooCommerce");
        }
        
        // Test script handle
        $handle = $script_manager->get_script_handle();
        
        if ($handle === 'preproduct-embed') {
            $this->assert_true(true, "✅ Correct script handle returned");
        } else {
            $this->assert_true(false, "❌ Incorrect script handle returned: " . $handle);
        }
        
        echo "\n";
    }
    
    private function test_environment_url_usage() {
        echo "Testing Environment URL Usage...\n";
        
        // Create environment manager and script manager
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        $script_url = $env_manager->get_script_url();
        
        if (strpos($script_url, 'preproduct-embed.js') !== false) {
            $this->assert_true(true, "✅ Environment manager provides correct script URL");
        } else {
            $this->assert_true(false, "❌ Environment manager doesn't provide correct script URL");
        }
        
        echo "🔍 Script URL from environment: " . $script_url . "\n\n";
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
$test = new ScriptManagerTest();
$test->run_all_tests(); 