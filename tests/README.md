# WooPreProduct Test Suite

This directory contains organized tests for the WooPreProduct WordPress plugin.

## Quick Start

```bash
# Run all tests
./test

# Run specific test
./test EnvironmentTest
./test ScriptManagerTest

# Show help
./test --help
```

## Directory Structure

```
tests/
├── README.md                 # This file
├── bootstrap.php             # Shared WordPress function mocks
├── run-tests.php            # Main test runner
├── environment-test.php     # Environment detection tests
├── script-manager-test.php  # Script injection tests
├── button-tagger-test.php   # Button tagging tests (needs update)
└── admin-page-test.php      # Admin interface tests (needs update)
```

## How It Works

### Bootstrap System
- `bootstrap.php` contains all shared WordPress function mocks
- Prevents function redeclaration errors across multiple test files
- Defines constants and globals needed by all tests

### Test Runner
- `run-tests.php` orchestrates running multiple test files
- Runs each test in a separate PHP process to avoid conflicts
- Parses output to aggregate test results
- Supports running individual tests or all tests

### Convenience Script
- `./test` bash script provides easy CLI access
- Wrapper around `php tests/run-tests.php`
- Includes help documentation

## PHP-Only Alternatives

If you prefer PHP-only solutions:

```bash
# Run all tests
php tests/run-tests.php

# Run specific test
php tests/run-tests.php EnvironmentTest

# Run individual test file directly
php tests/environment-test.php
```

## Test File Structure

Each test file follows this pattern:

```php
<?php
// Include shared bootstrap
require_once __DIR__ . '/bootstrap.php';

// Include required classes
require_once 'includes/class-example.php';

class ExampleTest {
    private $tests_passed = 0;
    private $tests_failed = 0;
    
    public function run_all_tests() {
        // Test method calls
        
        // Results output
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
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
$test = new ExampleTest();
$test->run_all_tests();
```

## Updating Remaining Tests

The remaining test files need to be updated to:
1. Include `bootstrap.php` instead of defining their own mocks
2. Update paths to use `__DIR__` relative paths
3. Ensure they follow the standard test class structure

## Benefits of This Approach

✅ **No external dependencies** - Pure PHP, no PHPUnit required  
✅ **Organized structure** - Tests in dedicated directory  
✅ **Shared mocks** - No function redeclaration conflicts  
✅ **Multiple run options** - Individual tests or full suite  
✅ **Clean output** - Aggregated results and summaries  
✅ **Easy integration** - Simple `./test` command 