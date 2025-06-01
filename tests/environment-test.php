<?php
/**
 * Environment Manager Test Script
 * 
 * Run this with: php tests/environment-test.php
 */

// Include shared bootstrap
require_once __DIR__ . '/bootstrap.php';

// Include required classes
require_once 'includes/class-environment-manager.php';

class EnvironmentTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    
    public function run_all_tests() {
        echo "🧪 Running Environment Detection Tests\n";
        echo "=====================================\n\n";
        
        $this->test_production_detection();
        $this->test_development_detection();
        $this->test_manual_override();
        $this->test_url_generation();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_production_detection() {
        echo "Testing Production Environment Detection...\n";
        
        $test_urls = array(
            'https://example.com',
            'https://mystore.com',
            'https://preproduct.io',
            'https://shop.example.org'
        );
        
        foreach ($test_urls as $url) {
            // Mock site_url to return our test URL
            global $test_site_url;
            $test_site_url = $url;
            
            // Reset singleton
            $this->reset_environment_manager();
            $env_manager = WooPreProduct_Environment_Manager::get_instance();
            
            if ($env_manager->is_production()) {
                $this->assert_true(true, "✅ '{$url}' detected as production");
            } else {
                $this->assert_true(false, "❌ '{$url}' should be detected as production");
            }
        }
        
        echo "\n";
    }
    
    private function test_development_detection() {
        echo "Testing Development Environment Detection...\n";
        
        $test_urls = array(
            'http://localhost:8000',
            'https://mysite.test',
            'https://shop.local',
            'https://staging.example.com',
            'https://dev.mystore.com'
        );
        
        foreach ($test_urls as $url) {
            // Mock site_url to return our test URL
            global $test_site_url;
            $test_site_url = $url;
            
            // Reset singleton
            $this->reset_environment_manager();
            $env_manager = WooPreProduct_Environment_Manager::get_instance();
            
            if ($env_manager->is_development()) {
                $this->assert_true(true, "✅ '{$url}' detected as development");
            } else {
                $this->assert_true(false, "❌ '{$url}' should be detected as development");
            }
        }
        
        echo "\n";
    }
    
    private function test_manual_override() {
        echo "Testing Manual Override...\n";
        
        // Test with production URL but manual override
        global $test_site_url;
        $test_site_url = 'https://example.com';
        
        // Define override constant
        if (!defined('PREPRODUCT_DEV_MODE')) {
            define('PREPRODUCT_DEV_MODE', true);
        }
        
        $this->reset_environment_manager();
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        
        if ($env_manager->is_development()) {
            $this->assert_true(true, "✅ Manual override to development works");
        } else {
            $this->assert_true(false, "❌ Manual override to development failed");
        }
        
        echo "\n";
    }
    
    private function test_url_generation() {
        echo "Testing URL Generation...\n";
        
        // Test development URLs
        global $test_site_url;
        $test_site_url = 'https://mysite.test';
        
        $this->reset_environment_manager();
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        
        $script_url = $env_manager->get_script_url();
        if (strpos($script_url, 'preproduct.ngrok.io') !== false && strpos($script_url, 'preproduct-embed.js') !== false) {
            $this->assert_true(true, "✅ Development script URL correct: {$script_url}");
        } else {
            $this->assert_true(false, "❌ Development script URL incorrect: {$script_url}");
        }
        
        // Display all URLs for verification
        echo "🔍 All URLs for current environment:\n";
        $all_urls = $env_manager->get_all_urls();
        foreach ($all_urls as $key => $url) {
            echo "   {$key}: {$url}\n";
        }
        
        echo "\n";
    }
    
    private function reset_environment_manager() {
        // Reset the singleton by accessing the private static property via reflection
        $reflection = new ReflectionClass('WooPreProduct_Environment_Manager');
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
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
$test = new EnvironmentTest();
$test->run_all_tests(); 