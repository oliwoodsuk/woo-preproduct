<?php
/**
 * Test Runner for WooPreProduct Plugin
 * 
 * Run all tests with: php tests/run-tests.php
 * Run specific test with: php tests/run-tests.php EnvironmentTest
 */

// Change to the project root directory
chdir(dirname(__DIR__));

// Include shared bootstrap
require_once 'tests/bootstrap.php';

echo "🧪 WooPreProduct Plugin Test Suite\n";
echo "==================================\n\n";

// Define test files and their corresponding class names
$tests = array(
    'environment-test.php' => 'EnvironmentTest',
    'button-tagger-test.php' => 'ButtonTaggerTest', 
    'script-manager-test.php' => 'ScriptManagerTest',
    'admin-page-test.php' => 'AdminPageTest'
);

// Check if user wants to run a specific test
$specific_test = isset($argv[1]) ? $argv[1] : null;

$total_passed = 0;
$total_failed = 0;
$tests_run = 0;

foreach ($tests as $test_file => $test_class) {
    // Skip if user requested a specific test and this isn't it
    if ($specific_test && strpos($test_class, $specific_test) === false) {
        continue;
    }
    
    $test_path = 'tests/' . $test_file;
    
    if (!file_exists($test_path)) {
        echo "⚠️  Test file not found: {$test_file}\n";
        continue;
    }
    
    echo "📋 Running {$test_class}...\n";
    echo str_repeat('-', 50) . "\n";
    
    // Reset globals for each test
    $_GET = array();
    $GLOBALS['pagenow'] = '';
    
    // Include and run the test
    try {
        // Create a new process to run each test to avoid conflicts
        $output = shell_exec("php -f \"{$test_path}\" 2>&1");
        echo $output;
        
        // Parse results from output
        if (preg_match('/✅ Passed: (\d+)/', $output, $matches)) {
            $total_passed += (int)$matches[1];
        }
        if (preg_match('/❌ Failed: (\d+)/', $output, $matches)) {
            $total_failed += (int)$matches[1];
        }
        
    } catch (Exception $e) {
        echo "❌ Error running {$test_class}: " . $e->getMessage() . "\n";
        $total_failed++;
    }
    
    $tests_run++;
    echo "\n";
}

// Summary
echo "🎯 Test Suite Summary\n";
echo "===================\n";
echo "Tests Run: {$tests_run}\n";
echo "✅ Total Passed: {$total_passed}\n";
echo "❌ Total Failed: {$total_failed}\n";

if ($total_failed === 0 && $tests_run > 0) {
    echo "\n🎉 All tests passed!\n";
    exit(0);
} else if ($tests_run === 0) {
    echo "\n⚠️  No tests were run.\n";
    exit(1);
} else {
    echo "\n💥 Some tests failed!\n";
    exit(1);
} 