<?php
/**
 * Plugin Path Usage Test
 *
 * Tests all plugin path-related functionality including:
 * - plugin_dir_path() usage
 * - plugin_dir_url() usage
 * - plugins_url() usage
 * - Asset loading paths
 * - Localization paths
 * - Path security
 */

// Include the bootstrap file for WordPress function mocks
require_once __DIR__ . '/bootstrap.php';

// Include the main plugin class
require_once dirname(__DIR__) . '/includes/class-woo-preproduct.php';

class PluginPathTest {
    private $test_results = array();
    private $plugin_instance;
    
    public function __construct() {
        $this->plugin_instance = new PreProduct_Plugin();
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "Running Plugin Path Tests...\n";
        echo "================================\n\n";
        
        $this->test_plugin_dir_path_usage();
        $this->test_plugin_dir_url_usage();
        $this->test_plugins_url_usage();
        $this->test_asset_path_generation();
        $this->test_localization_paths();
        $this->test_path_security();
        $this->test_path_consistency();
        
        $this->print_results();
    }
    
    /**
     * Test plugin_dir_path() usage
     */
    private function test_plugin_dir_path_usage() {
        echo "Testing plugin_dir_path() usage...\n";
        
        // Test main plugin path method
        $plugin_path = $this->plugin_instance->plugin_path();
        $this->assert_test(
            'plugin_path() returns proper path',
            !empty($plugin_path) && is_string($plugin_path),
            "Expected non-empty string, got: " . var_export($plugin_path, true)
        );
        
        // Test pluginPath method (alternative method)
        $plugin_path_alt = $this->plugin_instance->pluginPath();
        $this->assert_test(
            'pluginPath() returns proper path',
            !empty($plugin_path_alt) && is_string($plugin_path_alt),
            "Expected non-empty string, got: " . var_export($plugin_path_alt, true)
        );
        
        // Test that both methods return the same result
        $this->assert_test(
            'plugin_path() and pluginPath() return same result',
            $plugin_path === $plugin_path_alt,
            "plugin_path(): $plugin_path vs pluginPath(): $plugin_path_alt"
        );
        
        // Test that path doesn't end with slash (untrailingslashit applied)
        $this->assert_test(
            'plugin paths do not end with slash',
            !str_ends_with($plugin_path, '/'),
            "Path should not end with slash: $plugin_path"
        );
        
        echo "✓ plugin_dir_path() tests completed\n\n";
    }
    
    /**
     * Test plugin_dir_url() usage
     */
    private function test_plugin_dir_url_usage() {
        echo "Testing plugin_dir_url() usage...\n";
        
        // Test main plugin URL method
        $plugin_url = $this->plugin_instance->plugin_url();
        $this->assert_test(
            'plugin_url() returns proper URL',
            !empty($plugin_url) && is_string($plugin_url) && filter_var($plugin_url, FILTER_VALIDATE_URL),
            "Expected valid URL, got: " . var_export($plugin_url, true)
        );
        
        // Test that URL doesn't end with slash (untrailingslashit applied)
        $this->assert_test(
            'plugin URL does not end with slash',
            !str_ends_with($plugin_url, '/'),
            "URL should not end with slash: $plugin_url"
        );
        
        // Test that URL uses HTTPS in our mock environment
        $this->assert_test(
            'plugin URL uses HTTPS',
            str_starts_with($plugin_url, 'https://'),
            "Expected HTTPS URL, got: $plugin_url"
        );
        
        echo "✓ plugin_dir_url() tests completed\n\n";
    }
    
    /**
     * Test plugins_url() usage
     */
    private function test_plugins_url_usage() {
        echo "Testing plugins_url() usage...\n";
        
        // Test that plugins_url can generate asset URLs
        $asset_url = plugins_url('assets/css/admin.css', PREPRODUCT_PLUGIN_FILE);
        $this->assert_test(
            'plugins_url() generates valid asset URL',
            !empty($asset_url) && is_string($asset_url) && filter_var($asset_url, FILTER_VALIDATE_URL),
            "Expected valid URL, got: " . var_export($asset_url, true)
        );
        
        // Test that asset URL contains the expected path
        $this->assert_test(
            'asset URL contains correct path',
            str_contains($asset_url, 'assets/css/admin.css'),
            "Expected URL to contain 'assets/css/admin.css', got: $asset_url"
        );
        
        echo "✓ plugins_url() tests completed\n\n";
    }
    
    /**
     * Test asset path generation
     */
    private function test_asset_path_generation() {
        echo "Testing asset path generation...\n";
        
        // Test CSS asset URL generation
        $css_url = $this->plugin_instance->plugin_url() . '/assets/css/admin.css';
        $this->assert_test(
            'CSS asset URL is properly formed',
            filter_var($css_url, FILTER_VALIDATE_URL) && str_contains($css_url, 'admin.css'),
            "Expected valid CSS URL, got: $css_url"
        );
        
        // Test JS asset URL generation
        $js_url = $this->plugin_instance->plugin_url() . '/assets/js/admin.js';
        $this->assert_test(
            'JS asset URL is properly formed',
            filter_var($js_url, FILTER_VALIDATE_URL) && str_contains($js_url, 'admin.js'),
            "Expected valid JS URL, got: $js_url"
        );
        
        // Test that asset URLs don't have double slashes
        $this->assert_test(
            'asset URLs do not contain double slashes',
            !str_contains($css_url, '//') || str_starts_with($css_url, 'https://'),
            "URL should not contain double slashes except in protocol: $css_url"
        );
        
        echo "✓ Asset path generation tests completed\n\n";
    }
    
    /**
     * Test localization paths
     */
    private function test_localization_paths() {
        echo "Testing localization paths...\n";
        
        // Reset mock globals
        global $mock_loaded_textdomains;
        $mock_loaded_textdomains = array();
        
        // Test textdomain loading
        $this->plugin_instance->load_plugin_textdomain();
        
        $this->assert_test(
            'textdomain loading was called',
            !empty($mock_loaded_textdomains),
            "Expected textdomain to be loaded"
        );
        
        if (!empty($mock_loaded_textdomains)) {
            $this->assert_test(
                'correct textdomain is loaded',
                isset($mock_loaded_textdomains['preproduct']),
                "Expected 'preproduct' textdomain to be loaded"
            );
            
            if (isset($mock_loaded_textdomains['preproduct'])) {
                $textdomain_data = $mock_loaded_textdomains['preproduct'];
                $this->assert_test(
                    'textdomain path is properly set',
                    !empty($textdomain_data['path']) && str_contains($textdomain_data['path'], 'languages'),
                    "Expected path to contain 'languages', got: " . var_export($textdomain_data['path'], true)
                );
            }
        }
        
        echo "✓ Localization path tests completed\n\n";
    }
    
    /**
     * Test path security
     */
    private function test_path_security() {
        echo "Testing path security...\n";
        
        $plugin_path = $this->plugin_instance->plugin_path();
        $plugin_url = $this->plugin_instance->plugin_url();
        
        // Test that paths don't contain directory traversal attempts
        $this->assert_test(
            'plugin path is free of directory traversal',
            !str_contains($plugin_path, '../') && !str_contains($plugin_path, '..\\'),
            "Path should not contain directory traversal patterns: $plugin_path"
        );
        
        $this->assert_test(
            'plugin URL is free of directory traversal',
            !str_contains($plugin_url, '../') && !str_contains($plugin_url, '..\\'),
            "URL should not contain directory traversal patterns: $plugin_url"
        );
        
        // Test that paths are properly normalized
        $this->assert_test(
            'plugin path is normalized',
            !str_contains($plugin_path, '//') && !str_contains($plugin_path, '\\'),
            "Path should be normalized: $plugin_path"
        );
        
        echo "✓ Path security tests completed\n\n";
    }
    
    /**
     * Test path consistency
     */
    private function test_path_consistency() {
        echo "Testing path consistency...\n";
        
        $plugin_path = $this->plugin_instance->plugin_path();
        $plugin_url = $this->plugin_instance->plugin_url();
        
        // Test that plugin methods are consistent
        $this->assert_test(
            'plugin_path() and pluginPath() are consistent',
            $plugin_path === $this->plugin_instance->pluginPath(),
            "Methods should return the same result"
        );
        
        // Test that paths follow WordPress conventions
        $this->assert_test(
            'plugin path follows WordPress conventions',
            str_contains($plugin_path, 'plugins') && str_contains($plugin_path, 'woo-preproduct'),
            "Path should contain 'plugins' and 'woo-preproduct': $plugin_path"
        );
        
        $this->assert_test(
            'plugin URL follows WordPress conventions',
            str_contains($plugin_url, 'plugins') && str_contains($plugin_url, 'woo-preproduct'),
            "URL should contain 'plugins' and 'woo-preproduct': $plugin_url"
        );
        
        echo "✓ Path consistency tests completed\n\n";
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

// Run the tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new PluginPathTest();
    $test->run_all_tests();
}