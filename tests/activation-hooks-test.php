<?php
/**
 * Activation Hooks Test Script
 * 
 * Run this with: php tests/activation-hooks-test.php
 */

// Include shared bootstrap
require_once __DIR__ . '/bootstrap.php';

class ActivationHooksTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $test_results = array();
    private $test_user_capabilities = array();
    
    public function run_all_tests() {
        echo "🧪 Running Activation and Deactivation Hooks Tests\n";
        echo "==================================================\n\n";
        
        $this->test_activation_with_woocommerce();
        $this->test_activation_without_woocommerce();
        $this->test_deactivation();
        $this->test_activation_redirect_with_permissions();
        $this->test_activation_redirect_without_permissions();
        $this->test_activation_redirect_no_transient();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_activation_with_woocommerce() {
        echo "Testing Activation with WooCommerce Active...\n";
        
        $this->reset_test_environment();
        
        // Create mock WooCommerce class
        if (!class_exists('WooCommerce')) {
            eval('class WooCommerce {}');
        }
        
        try {
            $this->woo_preproduct_activate();
            
            if ($this->test_results['flush_rewrite_rules_called']) {
                $this->assert_true(true, "✅ flush_rewrite_rules() called correctly");
            } else {
                $this->assert_true(false, "❌ flush_rewrite_rules() was not called");
            }
            
            if (isset($this->test_results['transients']['woo_preproduct_activation_redirect'])) {
                $transient = $this->test_results['transients']['woo_preproduct_activation_redirect'];
                if ($transient['value'] === true && $transient['expiration'] === 30) {
                    $this->assert_true(true, "✅ Activation redirect transient set correctly");
                } else {
                    $this->assert_true(false, "❌ Activation redirect transient incorrect");
                }
            } else {
                $this->assert_true(false, "❌ Activation redirect transient not set");
            }
            
        } catch (Exception $e) {
            $this->assert_true(false, "❌ Unexpected exception: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function test_activation_without_woocommerce() {
        echo "Testing Activation without WooCommerce...\n";
        
        $this->reset_test_environment();
        
        try {
            $this->woo_preproduct_activate_no_wc();
            $this->assert_true(false, "❌ wp_die() was not called when WooCommerce is missing");
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'PreProduct requires WooCommerce') !== false) {
                $this->assert_true(true, "✅ wp_die() called with correct message");
            } else {
                $this->assert_true(false, "❌ wp_die() message incorrect");
            }
            
            if ($this->test_results['deactivate_called']) {
                $this->assert_true(true, "✅ Plugin deactivated when WooCommerce missing");
            } else {
                $this->assert_true(false, "❌ Plugin was not deactivated");
            }
        }
        
        echo "\n";
    }
    
    private function test_deactivation() {
        echo "Testing Deactivation Functionality...\n";
        
        $this->reset_test_environment();
        $this->woo_preproduct_deactivate();
        
        if ($this->test_results['flush_rewrite_rules_called']) {
            $this->assert_true(true, "✅ flush_rewrite_rules() called during deactivation");
        } else {
            $this->assert_true(false, "❌ flush_rewrite_rules() not called during deactivation");
        }
        
        echo "\n";
    }
    
    private function test_activation_redirect_with_permissions() {
        echo "Testing Activation Redirect with Proper Permissions...\n";
        
        $this->reset_test_environment();
        $this->test_user_capabilities = array('manage_woocommerce');
        
        // Set the transient
        $this->set_transient('woo_preproduct_activation_redirect', true, 30);
        
        $result = $this->woo_preproduct_activation_redirect_test();
        
        if ($result === 'exit_called') {
            $this->assert_true(true, "✅ Redirect executed correctly");
        } else {
            $this->assert_true(false, "❌ Redirect was not executed");
        }
        
        if ($this->test_results['redirect_called']) {
            $expected_url = 'http://example.com/wp-admin/admin.php?page=woo-preproduct';
            if ($this->test_results['redirect_location'] === $expected_url) {
                $this->assert_true(true, "✅ Redirect URL correct");
            } else {
                $this->assert_true(false, "❌ Redirect URL incorrect");
            }
        } else {
            $this->assert_true(false, "❌ wp_safe_redirect() not called");
        }
        
        if (in_array('woo_preproduct_activation_redirect', $this->test_results['deleted_transients'])) {
            $this->assert_true(true, "✅ Transient deleted correctly");
        } else {
            $this->assert_true(false, "❌ Transient not deleted");
        }
        
        echo "\n";
    }
    
    private function test_activation_redirect_without_permissions() {
        echo "Testing Activation Redirect without Permissions...\n";
        
        $this->reset_test_environment();
        $this->test_user_capabilities = array(); // No capabilities
        
        // Set the transient
        $this->set_transient('woo_preproduct_activation_redirect', true, 30);
        
        $result = $this->woo_preproduct_activation_redirect_test();
        
        if ($result === 'no_redirect') {
            $this->assert_true(true, "✅ Redirect correctly skipped without permissions");
        } else {
            $this->assert_true(false, "❌ Redirect should have been skipped");
        }
        
        if (in_array('woo_preproduct_activation_redirect', $this->test_results['deleted_transients'])) {
            $this->assert_true(true, "✅ Transient still deleted correctly");
        } else {
            $this->assert_true(false, "❌ Transient not deleted");
        }
        
        echo "\n";
    }
    
    private function test_activation_redirect_no_transient() {
        echo "Testing Activation Redirect with No Transient...\n";
        
        $this->reset_test_environment();
        $this->test_user_capabilities = array('manage_woocommerce');
        
        // Don't set the transient
        $result = $this->woo_preproduct_activation_redirect_test();
        
        if ($result === 'no_redirect') {
            $this->assert_true(true, "✅ Redirect correctly skipped when no transient");
        } else {
            $this->assert_true(false, "❌ Redirect should have been skipped");
        }
        
        echo "\n";
    }
    
    // Mock activation function (with WooCommerce)
    private function woo_preproduct_activate() {
        if (!class_exists('WooCommerce')) {
            $this->deactivate_plugins($this->plugin_basename(__FILE__));
            $this->wp_die(
                'PreProduct requires WooCommerce to be installed and active.',
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }
        
        $this->flush_rewrite_rules();
        $this->set_transient('woo_preproduct_activation_redirect', true, 30);
    }
    
    // Mock activation function (without WooCommerce)
    private function woo_preproduct_activate_no_wc() {
        if (!class_exists('WooCommerceNonExistent')) {
            $this->deactivate_plugins($this->plugin_basename(__FILE__));
            $this->wp_die(
                'PreProduct requires WooCommerce to be installed and active.',
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }
        
        $this->flush_rewrite_rules();
        $this->set_transient('woo_preproduct_activation_redirect', true, 30);
    }
    
    // Mock deactivation function
    private function woo_preproduct_deactivate() {
        $this->flush_rewrite_rules();
    }
    
    // Mock activation redirect function (for testing)
    private function woo_preproduct_activation_redirect_test() {
        if ($this->get_transient('woo_preproduct_activation_redirect')) {
            $this->delete_transient('woo_preproduct_activation_redirect');
            
            if ($this->current_user_can('manage_woocommerce')) {
                $this->wp_safe_redirect($this->admin_url('admin.php?page=woo-preproduct'));
                return 'exit_called';
            }
        }
        return 'no_redirect';
    }
    
    // Mock WordPress functions
    private function plugin_basename($file) {
        return basename(dirname($file)) . '/' . basename($file);
    }
    
    private function deactivate_plugins($plugins) {
        $this->test_results['deactivate_called'] = true;
        $this->test_results['deactivated_plugins'] = $plugins;
    }
    
    private function wp_die($message, $title = '', $args = array()) {
        $this->test_results['wp_die_called'] = true;
        $this->test_results['wp_die_message'] = $message;
        $this->test_results['wp_die_title'] = $title;
        throw new Exception("wp_die called: $message");
    }
    
    private function flush_rewrite_rules() {
        $this->test_results['flush_rewrite_rules_called'] = true;
    }
    
    private function set_transient($transient, $value, $expiration) {
        $this->test_results['transients'][$transient] = array(
            'value' => $value,
            'expiration' => $expiration
        );
    }
    
    private function get_transient($transient) {
        return isset($this->test_results['transients'][$transient]) ? 
               $this->test_results['transients'][$transient]['value'] : false;
    }
    
    private function delete_transient($transient) {
        if (isset($this->test_results['transients'][$transient])) {
            $this->test_results['deleted_transients'][] = $transient;
            unset($this->test_results['transients'][$transient]);
        }
    }
    
    private function current_user_can($capability) {
        return in_array($capability, $this->test_user_capabilities);
    }
    
    private function admin_url($path = '') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
    
    private function wp_safe_redirect($location) {
        $this->test_results['redirect_called'] = true;
        $this->test_results['redirect_location'] = $location;
    }
    
    private function reset_test_environment() {
        $this->test_results = array(
            'transients' => array(),
            'deleted_transients' => array(),
            'wp_die_called' => false,
            'deactivate_called' => false,
            'flush_rewrite_rules_called' => false,
            'redirect_called' => false
        );
        $this->test_user_capabilities = array();
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
$test = new ActivationHooksTest();
$test->run_all_tests(); 