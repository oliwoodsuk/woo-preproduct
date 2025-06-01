<?php
/**
 * Simple Test Script for Environment Detection
 * 
 * Run this with: php test-environment.php
 */

// Define ABSPATH to prevent WordPress security check from exiting
define('ABSPATH', '/fake/wordpress/path/');

// Mock WordPress functions for testing
function site_url() {
    global $test_site_url;
    return $test_site_url ?? 'https://example.com';
}

// Include the Environment Manager class
require_once 'includes/class-environment-manager.php';

class EnvironmentDetectionTest {
    
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
        
        // Test production domains
        $production_domains = [
            'https://example.com',
            'https://mystore.com',
            'https://preproduct.io',
            'https://shop.example.org'
        ];
        
        foreach ($production_domains as $domain) {
            $this->set_site_url($domain);
            $env = WooPreProduct_Environment_Manager::get_instance();
            
            if ($env->is_production()) {
                $this->assert_true(true, "✅ '$domain' detected as production");
            } else {
                $this->assert_true(false, "❌ '$domain' should be production but detected as development");
            }
        }
        echo "\n";
    }
    
    private function test_development_detection() {
        echo "Testing Development Environment Detection...\n";
        
        // Test development domains
        $dev_domains = [
            'http://localhost:8000',
            'https://mysite.test',
            'https://shop.local',
            'https://staging.example.com',
            'https://dev.mystore.com'
        ];
        
        foreach ($dev_domains as $domain) {
            $this->set_site_url($domain);
            // Reset singleton for each test
            $this->reset_environment_manager();
            $env = WooPreProduct_Environment_Manager::get_instance();
            
            if ($env->is_development()) {
                $this->assert_true(true, "✅ '$domain' detected as development");
            } else {
                $this->assert_true(false, "❌ '$domain' should be development but detected as production");
            }
        }
        echo "\n";
    }
    
    private function test_manual_override() {
        echo "Testing Manual Override...\n";
        
        // Test forcing development mode
        if (!defined('PREPRODUCT_DEV_MODE')) {
            define('PREPRODUCT_DEV_MODE', true);
        }
        $this->set_site_url('https://production-site.com');
        $this->reset_environment_manager();
        $env = WooPreProduct_Environment_Manager::get_instance();
        
        if ($env->is_development()) {
            $this->assert_true(true, "✅ Manual override to development works");
        } else {
            $this->assert_true(false, "❌ Manual override to development failed");
        }
        echo "\n";
    }
    
    private function test_url_generation() {
        echo "Testing URL Generation...\n";
        
        // Test development URLs (with override constant)
        $this->set_site_url('https://production-site.com');
        $this->reset_environment_manager();
        $env = WooPreProduct_Environment_Manager::get_instance();
        
        $script_url = $env->get_script_url();
        $expected_dev_script = 'https://preproduct.ngrok.io/preproduct-embed.js';
        
        if ($script_url === $expected_dev_script) {
            $this->assert_true(true, "✅ Development script URL correct: $script_url");
        } else {
            $this->assert_true(false, "❌ Development script URL incorrect. Expected: $expected_dev_script, Got: $script_url");
        }
        
        // Show all URLs for current environment
        $all_urls = $env->get_all_urls();
        echo "🔍 All URLs for current environment:\n";
        foreach ($all_urls as $type => $url) {
            echo "   $type: $url\n";
        }
        
        echo "\n";
    }
    
    private function set_site_url($url) {
        global $test_site_url;
        $test_site_url = $url;
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
$test = new EnvironmentDetectionTest();
$test->run_all_tests(); 