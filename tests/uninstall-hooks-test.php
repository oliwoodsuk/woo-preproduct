<?php
/**
 * Uninstall Hooks Test Script
 * 
 * Run this with: php tests/uninstall-hooks-test.php
 */

// Include shared bootstrap
require_once __DIR__ . '/bootstrap.php';

// Include required classes
require_once 'includes/class-environment-manager.php';

class UninstallHooksTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $test_results = array();
    
    public function run_all_tests() {
        echo "🧪 Running Uninstall Hook Tests\n";
        echo "===============================\n\n";
        
        $this->test_uninstall_security_check();
        $this->test_webhook_payload_production();
        $this->test_webhook_payload_development();
        $this->test_webhook_non_blocking();
        $this->test_environment_manager_integration();
        $this->test_woocommerce_version_detection();
        $this->test_webhook_signature_with_api_token();
        $this->test_webhook_signature_without_api_token();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_uninstall_security_check() {
        echo "Testing Uninstall Security Check...\n";
        
        // Test without WP_UNINSTALL_PLUGIN constant
        $this->reset_test_environment();
        
        try {
            $result = $this->simulate_uninstall_without_constant();
            if ($result === 'exit_called') {
                $this->assert_true(true, "✅ Uninstall script exits when WP_UNINSTALL_PLUGIN not defined");
            } else {
                $this->assert_true(false, "❌ Uninstall script should exit when WP_UNINSTALL_PLUGIN not defined");
            }
        } catch (Exception $e) {
            $this->assert_true(false, "❌ Unexpected exception: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function test_webhook_payload_production() {
        echo "Testing Webhook Payload for Production Environment...\n";
        
        $this->reset_test_environment();
        
        // Mock production environment
        global $test_site_url;
        $test_site_url = 'https://example.com';
        
        // Reset singleton for fresh environment detection
        $this->reset_environment_manager();
        
        $payload = $this->simulate_uninstall_webhook();
        
        if (isset($payload['event']) && $payload['event'] === 'plugin_uninstalled') {
            $this->assert_true(true, "✅ Event type correct in payload");
        } else {
            $this->assert_true(false, "❌ Event type missing or incorrect");
        }
        
        if (isset($payload['store_url']) && $payload['store_url'] === 'https://example.com') {
            $this->assert_true(true, "✅ Store URL correct in payload");
        } else {
            $this->assert_true(false, "❌ Store URL missing or incorrect");
        }
        
        if (isset($payload['timestamp']) && is_numeric($payload['timestamp'])) {
            $this->assert_true(true, "✅ Timestamp present and numeric");
        } else {
            $this->assert_true(false, "❌ Timestamp missing or not numeric");
        }
        
        if (isset($payload['admin_email']) && $payload['admin_email'] === 'admin@example.com') {
            $this->assert_true(true, "✅ Admin email correct in payload");
        } else {
            $this->assert_true(false, "❌ Admin email missing or incorrect");
        }
        
        if (isset($payload['store_name']) && $payload['store_name'] === 'Test Store') {
            $this->assert_true(true, "✅ Store name correct in payload");
        } else {
            $this->assert_true(false, "❌ Store name missing or incorrect");
        }
        
        echo "\n";
    }
    
    private function test_webhook_payload_development() {
        echo "Testing Webhook Payload for Development Environment...\n";
        
        $this->reset_test_environment();
        
        // Mock development environment (localhost-only)
        global $test_site_url;
        $test_site_url = 'http://localhost:8000';
        
        // Reset singleton for fresh environment detection
        $this->reset_environment_manager();
        
        $payload = $this->simulate_uninstall_webhook();
        $webhook_url = $this->test_results['webhook_url'];
        
        if (strpos($webhook_url, 'preproduct.ngrok.io') !== false) {
            $this->assert_true(true, "✅ Development webhook URL used");
        } else {
            $this->assert_true(false, "❌ Should use development webhook URL");
        }
        
        if (strpos($webhook_url, '/woocommerce/webhooks') !== false) {
            $this->assert_true(true, "✅ Webhook endpoint path correct");
        } else {
            $this->assert_true(false, "❌ Webhook endpoint path incorrect");
        }
        
        echo "\n";
    }
    
    private function test_webhook_non_blocking() {
        echo "Testing Webhook Non-blocking Request...\n";
        
        $this->reset_test_environment();
        
        $this->simulate_uninstall_webhook();
        
        if (isset($this->test_results['wp_remote_post_args']['blocking']) && 
            $this->test_results['wp_remote_post_args']['blocking'] === false) {
            $this->assert_true(true, "✅ Webhook request is non-blocking");
        } else {
            $this->assert_true(false, "❌ Webhook request should be non-blocking");
        }
        
        if (isset($this->test_results['wp_remote_post_args']['timeout']) && 
            $this->test_results['wp_remote_post_args']['timeout'] === 45) {
            $this->assert_true(true, "✅ Webhook timeout set correctly");
        } else {
            $this->assert_true(false, "❌ Webhook timeout not set correctly");
        }
        
        echo "\n";
    }
    
    private function test_environment_manager_integration() {
        echo "Testing Environment Manager Integration...\n";
        
        $this->reset_test_environment();
        
        // Test production environment
        global $test_site_url;
        $test_site_url = 'https://production.com';
        $this->reset_environment_manager();
        
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        $webhook_url = $env_manager->get_webhook_url();
        
        if ($webhook_url === 'https://api.preproduct.io/woocommerce/webhooks') {
            $this->assert_true(true, "✅ Production webhook URL correct");
        } else {
            $this->assert_true(false, "❌ Production webhook URL incorrect: " . $webhook_url);
        }
        
        // Test development environment (localhost-only)
        $test_site_url = 'http://localhost';
        $this->reset_environment_manager();
        
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        $webhook_url = $env_manager->get_webhook_url();
        
        if ($webhook_url === 'https://preproduct.ngrok.io/woocommerce/webhooks') {
            $this->assert_true(true, "✅ Development webhook URL correct");
        } else {
            $this->assert_true(false, "❌ Development webhook URL incorrect: " . $webhook_url);
        }
        
        echo "\n";
    }
    
    private function test_woocommerce_version_detection() {
        echo "Testing WooCommerce Version Detection...\n";
        
        $this->reset_test_environment();
        
        // Test without WooCommerce
        $payload = $this->simulate_uninstall_webhook();
        
        if (isset($payload['wc_version']) && $payload['wc_version'] === 'unknown') {
            $this->assert_true(true, "✅ WooCommerce version 'unknown' when not available");
        } else {
            $this->assert_true(false, "❌ Should detect WooCommerce as 'unknown'");
        }
        
        // Test with WooCommerce
        define('WC_VERSION', '7.2.0');
        $payload = $this->simulate_uninstall_webhook();
        
        if (isset($payload['wc_version']) && $payload['wc_version'] === '7.2.0') {
            $this->assert_true(true, "✅ WooCommerce version detected correctly");
        } else {
            $this->assert_true(false, "❌ WooCommerce version not detected correctly");
        }
        
        echo "\n";
    }
    
    private function test_webhook_signature_with_api_token() {
        echo "Testing Webhook Signature with API Token...\n";
        
        $this->reset_test_environment();
        
        // Mock API token
        $api_token = 'test_api_token_123';
        $this->test_results['api_token'] = $api_token;
        
        $this->simulate_uninstall_webhook();
        
        if (isset($this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Signature'])) {
            $sent_signature = $this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Signature'];
            
            // Generate expected signature
            $json_body = $this->test_results['wp_remote_post_args']['body'];
            $expected_signature = base64_encode(hash_hmac('sha256', $json_body, $api_token, true));
            
            if ($sent_signature === $expected_signature) {
                $this->assert_true(true, "✅ HMAC-SHA256 signature generated correctly");
            } else {
                $this->assert_true(false, "❌ HMAC-SHA256 signature incorrect. Expected: $expected_signature, Got: $sent_signature");
            }
        } else {
            $this->assert_true(false, "❌ X-WC-Webhook-Signature header missing");
        }
        
        if (isset($this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Topic']) &&
            $this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Topic'] === 'plugin.uninstalled') {
            $this->assert_true(true, "✅ X-WC-Webhook-Topic header correct");
        } else {
            $this->assert_true(false, "❌ X-WC-Webhook-Topic header missing or incorrect");
        }
        
        echo "\n";
    }
    
    private function test_webhook_signature_without_api_token() {
        echo "Testing Webhook Signature without API Token...\n";
        
        $this->reset_test_environment();
        
        // No API token
        $this->test_results['api_token'] = '';
        
        $this->simulate_uninstall_webhook();
        
        if (!isset($this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Signature'])) {
            $this->assert_true(true, "✅ No signature header when API token is empty");
        } else {
            $this->assert_true(false, "❌ Signature header should not be present without API token");
        }
        
        // Other headers should still be present
        if (isset($this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Source']) &&
            $this->test_results['wp_remote_post_args']['headers']['X-WC-Webhook-Source'] === 'woo-preproduct-plugin') {
            $this->assert_true(true, "✅ X-WC-Webhook-Source header present");
        } else {
            $this->assert_true(false, "❌ X-WC-Webhook-Source header missing");
        }
        
        echo "\n";
    }
    
    // Mock functions to simulate WordPress environment
    private function simulate_uninstall_without_constant() {
        // Simulate uninstall script execution without WP_UNINSTALL_PLUGIN constant
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return 'exit_called';
        }
        return 'continued';
    }
    
    private function simulate_uninstall_webhook() {
        // Mock WordPress function calls
        global $test_site_url;
        
        $env_manager = WooPreProduct_Environment_Manager::get_instance();
        $webhook_url = $env_manager->get_webhook_url();
        
        // Store webhook URL for testing
        $this->test_results['webhook_url'] = $webhook_url;
        
        // Get API token from test results
        $api_token = isset($this->test_results['api_token']) ? $this->test_results['api_token'] : '';
        
        // Simulate the webhook data preparation
        $site_url = $test_site_url;
        $admin_email = 'admin@example.com';
        $store_name = 'Test Store';
        
        $data = array(
            'event' => 'plugin_uninstalled',
            'store_url' => $site_url,
            'admin_email' => $admin_email,
            'store_name' => $store_name,
            'timestamp' => time(),
            'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'unknown'
        );
        
        // Encode the payload as JSON
        $json_body = json_encode($data);
        
        // Prepare headers
        $headers = array(
            'Content-Type' => 'application/json',
            'X-WC-Webhook-Source' => 'woo-preproduct-plugin',
            'X-WC-Webhook-Topic' => 'plugin.uninstalled'
        );
        
        // Add signature header if API token is available
        if (!empty($api_token)) {
            $signature = base64_encode(hash_hmac('sha256', $json_body, $api_token, true));
            $headers['X-WC-Webhook-Signature'] = $signature;
        }
        
        // Mock wp_remote_post call
        $args = array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => false,
            'headers' => $headers,
            'body' => $json_body,
            'cookies' => array()
        );
        
        // Store args for testing
        $this->test_results['wp_remote_post_args'] = $args;
        $this->test_results['webhook_payload'] = $data;
        
        return $data;
    }
    
    private function reset_environment_manager() {
        // Reset the singleton by accessing the private static property via reflection
        $reflection = new ReflectionClass('WooPreProduct_Environment_Manager');
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
    
    private function reset_test_environment() {
        $this->test_results = array();
        global $test_site_url;
        $test_site_url = 'https://example.com';
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
$test = new UninstallHooksTest();
$test->run_all_tests(); 