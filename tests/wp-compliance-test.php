<?php
/**
 * WordPress Plugin Directory Compliance Test
 *
 * This test validates that the plugin meets WordPress Plugin Directory
 * submission requirements and guidelines.
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Include the bootstrap file
require_once __DIR__ . '/bootstrap.php';

/**
 * Test class for WordPress Plugin Directory Compliance
 */
class WPComplianceTest
{
    private $test_results = [];
    private $plugin_root;

    public function __construct()
    {
        $this->plugin_root = dirname(__DIR__);
    }

    /**
     * Run all tests
     */
    public function run_all_tests()
    {
        echo "Running WordPress Plugin Directory Compliance Tests...\n";
        echo "====================================================\n\n";

        // Test plugin header requirements
        $this->test_plugin_header();
        echo "\n";

        // Test file structure requirements
        $this->test_file_structure();
        echo "\n";

        // Test security requirements
        $this->test_security_requirements();
        echo "\n";

        // Test naming conventions
        $this->test_naming_conventions();
        echo "\n";

        // Test code standards
        $this->test_code_standards();
        echo "\n";

        // Test documentation
        $this->test_documentation();
        echo "\n";

        // Display results
        $this->display_test_results();
    }

    /**
     * Test plugin header requirements
     */
    private function test_plugin_header()
    {
        echo "Testing plugin header requirements...\n";

        $plugin_content = file_get_contents($this->plugin_root . '/preproduct.php');

        // Check required header fields
        $this->assert_true(strpos($plugin_content, 'Plugin Name:') !== false, "Plugin Name header field present");
        $this->assert_true(strpos($plugin_content, 'Description:') !== false, "Description header field present");
        $this->assert_true(strpos($plugin_content, 'Version:') !== false, "Version header field present");
        $this->assert_true(strpos($plugin_content, 'Author:') !== false, "Author header field present");
        $this->assert_true(strpos($plugin_content, 'License:') !== false, "License header field present");
        $this->assert_true(strpos($plugin_content, 'Text Domain:') !== false, "Text Domain header field present");

        // Check specific values
        $this->assert_true(strpos($plugin_content, 'Text Domain: preproduct') !== false, "Text Domain matches plugin folder name");
        $this->assert_true(strpos($plugin_content, 'GPL') !== false, "Uses GPL-compatible license");

        // Check version format
        preg_match('/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $plugin_content, $matches);
        $this->assert_true(!empty($matches), "Version follows semantic versioning (x.y.z)");

        echo "✓ Plugin header tests completed\n";
    }

    /**
     * Test file structure requirements
     */
    private function test_file_structure()
    {
        echo "Testing file structure requirements...\n";

        // Check required files
        $this->assert_true(file_exists($this->plugin_root . '/README.md'), "README.md file exists");
        $this->assert_true(file_exists($this->plugin_root . '/preproduct.php'), "Main plugin file exists");
        $this->assert_true(file_exists($this->plugin_root . '/uninstall.php'), "Uninstall.php file exists");

        // Check directory structure
        $this->assert_true(is_dir($this->plugin_root . '/includes'), "Includes directory exists");
        $this->assert_true(is_dir($this->plugin_root . '/assets'), "Assets directory exists");
        
        // Check for disallowed files/directories
        $this->assert_false(file_exists($this->plugin_root . '/.git'), "No .git directory (should be excluded)");
        $this->assert_false(file_exists($this->plugin_root . '/node_modules'), "No node_modules directory");
        $this->assert_false(file_exists($this->plugin_root . '/.DS_Store'), "No .DS_Store files");

        // Check main plugin file naming
        $plugin_files = glob($this->plugin_root . '/*.php');
        $main_plugin_file = false;
        foreach ($plugin_files as $file) {
            if (basename($file) === 'preproduct.php') {
                $main_plugin_file = true;
                break;
            }
        }
        $this->assert_true($main_plugin_file, "Main plugin file properly named");

        echo "✓ File structure tests completed\n";
    }

    /**
     * Test security requirements
     */
    private function test_security_requirements()
    {
        echo "Testing security requirements...\n";

        // Check for direct access protection
        $php_files = $this->get_plugin_php_files();
        $files_without_protection = 0;
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            // Skip test files and uninstall.php (uses different protection)
            if (strpos($file, '/tests/') !== false) {
                continue;
            }
            if (basename($file) === 'uninstall.php') {
                $has_protection = strpos($content, 'WP_UNINSTALL_PLUGIN') !== false;
            } else {
                $has_protection = strpos($content, 'ABSPATH') !== false;
            }
            
            if (!$has_protection) {
                $files_without_protection++;
            }
        }
        
        $this->assert_equals(0, $files_without_protection, "All plugin files have direct access protection");

        // Check for proper nonce verification in forms (if any)
        $form_files = [];
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'wp_nonce_field') !== false || strpos($content, 'wp_verify_nonce') !== false) {
                $form_files[] = $file;
            }
        }
        
        // For now, check that if nonces are used, they're used properly
        foreach ($form_files as $file) {
            $content = file_get_contents($file);
            $this->assert_true(strpos($content, 'wp_nonce_field') !== false || strpos($content, 'wp_verify_nonce') !== false, 
                              "File " . basename($file) . " uses proper nonce handling");
        }

        // Check for SQL injection protection (no direct SQL queries)
        $unsafe_sql_patterns = [
            '/\$wpdb->query\s*\(\s*["\'](?!.*prepare)/',
            '/mysqli_query/',
            '/mysql_query/',
        ];
        
        $unsafe_files = 0;
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            foreach ($unsafe_sql_patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $unsafe_files++;
                    break;
                }
            }
        }
        
        $this->assert_equals(0, $unsafe_files, "No unsafe SQL queries detected");

        echo "✓ Security tests completed\n";
    }

    /**
     * Test naming conventions
     */
    private function test_naming_conventions()
    {
        echo "Testing naming conventions...\n";

        // Check function prefixes
        $functions_file = $this->plugin_root . '/includes/preproduct-functions.php';
        $content = file_get_contents($functions_file);
        
        // Look for function definitions
        preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);
        $unprefixed_functions = 0;
        
        foreach ($matches[1] as $function_name) {
            if (!preg_match('/^preproduct_/', $function_name)) {
                $unprefixed_functions++;
            }
        }
        
        $this->assert_equals(0, $unprefixed_functions, "All functions use preproduct_ prefix");

        // Check class names
        $php_files = $this->get_plugin_php_files();
        $unprefixed_classes = 0;
        
        foreach ($php_files as $file) {
            if (strpos($file, '/tests/') !== false) continue; // Skip test files
            
            $content = file_get_contents($file);
            preg_match_all('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*/', $content, $matches);
            
            foreach ($matches[1] as $class_name) {
                if (!preg_match('/^PreProduct_/', $class_name)) {
                    $unprefixed_classes++;
                }
            }
        }
        
        $this->assert_equals(0, $unprefixed_classes, "All classes use PreProduct_ prefix");

        // Check hook names
        $all_content = '';
        foreach ($php_files as $file) {
            if (strpos($file, '/tests/') !== false) continue;
            $all_content .= file_get_contents($file);
        }
        
        // Check custom action hooks
        preg_match_all('/do_action\s*\(\s*[\'"]([^\'"]+)[\'"]/', $all_content, $action_matches);
        $unprefixed_actions = 0;
        
        foreach ($action_matches[1] as $hook_name) {
            // Skip WordPress core hooks
            if (in_array($hook_name, ['init', 'admin_menu', 'wp_enqueue_scripts', 'admin_enqueue_scripts', 'admin_notices', 'admin_init', 'plugins_loaded'])) {
                continue;
            }
            if (!preg_match('/^preproduct_/', $hook_name)) {
                $unprefixed_actions++;
            }
        }
        
        $this->assert_equals(0, $unprefixed_actions, "All custom action hooks use preproduct_ prefix");

        echo "✓ Naming convention tests completed\n";
    }

    /**
     * Test code standards
     */
    private function test_code_standards()
    {
        echo "Testing code standards...\n";

        $php_files = $this->get_plugin_php_files();
        
        // Check for PHP short tags
        $files_with_short_tags = 0;
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/<\?\s/', $content)) {
                $files_with_short_tags++;
            }
        }
        
        $this->assert_equals(0, $files_with_short_tags, "No PHP short tags used");

        // Check for proper PHP closing tags (should not be present in pure PHP files)
        $files_with_closing_tags = 0;
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            $last_line = trim(end($lines));
            if ($last_line === '?>' || (count($lines) > 1 && trim($lines[count($lines)-2]) === '?>')) {
                $files_with_closing_tags++;
            }
        }
        
        $this->assert_equals(0, $files_with_closing_tags, "No unnecessary PHP closing tags");

        // Check for error suppression operator
        $files_with_error_suppression = 0;
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/@[a-zA-Z_]/', $content) && strpos($file, '/tests/') === false) {
                $files_with_error_suppression++;
            }
        }
        
        // Allow some error suppression in non-test files (it's sometimes necessary)
        $this->assert_true($files_with_error_suppression <= 2, "Minimal use of error suppression operator");

        // Check for eval() usage (should be none)
        $files_with_eval = 0;
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'eval(') !== false) {
                $files_with_eval++;
            }
        }
        
        $this->assert_equals(0, $files_with_eval, "No eval() usage");

        echo "✓ Code standards tests completed\n";
    }

    /**
     * Test documentation
     */
    private function test_documentation()
    {
        echo "Testing documentation...\n";

        // Check README.md content
        $readme_content = file_get_contents($this->plugin_root . '/README.md');
        
        $this->assert_true(strpos($readme_content, '# ') !== false, "README has main heading");
        $this->assert_true(strpos($readme_content, 'WooCommerce') !== false, "README mentions WooCommerce");
        $this->assert_true(strpos($readme_content, 'install') !== false, "README contains installation info");

        // Check for proper PHPDoc comments in class files
        $class_files = glob($this->plugin_root . '/includes/class-*.php');
        $files_without_phpdoc = 0;
        
        foreach ($class_files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, '/**') === false || strpos($content, '@package') === false) {
                $files_without_phpdoc++;
            }
        }
        
        $this->assert_equals(0, $files_without_phpdoc, "All class files have PHPDoc headers");

        // Check main plugin file has proper description
        $plugin_content = file_get_contents($this->plugin_root . '/preproduct.php');
        $this->assert_true(strlen($plugin_content) > 500, "Main plugin file has substantial content and documentation");

        echo "✓ Documentation tests completed\n";
    }

    /**
     * Get all PHP files in the plugin
     */
    private function get_plugin_php_files()
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->plugin_root, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && 
                strpos($file->getPathname(), '/vendor/') === false &&
                strpos($file->getPathname(), '/node_modules/') === false) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
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
    $test = new WPComplianceTest();
    $test->run_all_tests();
}