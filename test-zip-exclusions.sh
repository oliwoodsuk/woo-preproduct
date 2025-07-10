#!/bin/bash

# Test script to verify zip exclusions and WordPress plugin compliance
# This script tests that the zip script properly excludes non-plugin files
# and that the resulting package contains only WordPress plugin-compliant files

echo "=== WordPress Plugin Zip Exclusion Test ==="
echo ""

# Initialize test results
TESTS_PASSED=0
TESTS_FAILED=0

# Helper function to run a test
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo "Testing: $test_name"
    if eval "$test_command"; then
        echo "✓ PASS: $test_name"
        ((TESTS_PASSED++))
    else
        echo "✗ FAIL: $test_name"
        ((TESTS_FAILED++))
    fi
    echo ""
}

# Test 1: Verify .distignore file exists and is readable
run_test "Distignore file exists and is readable" "[ -f .distignore ] && [ -r .distignore ]"

# Test 2: Verify zip script exists and is executable
run_test "Zip script exists and is executable" "[ -f zip ] && [ -x zip ]"

# Test 3: Run zip script and verify it completes successfully
echo "Running zip script..."
./zip > /tmp/zip_output.log 2>&1
run_test "Zip script runs successfully" "[ $? -eq 0 ]"

# Test 4: Verify zip archive was created
run_test "Zip archive was created" "[ -f ../woo-package/preproduct.zip ]"

# Test 5: Extract archive to temporary directory for testing
TEST_DIR="/tmp/preproduct-test-$$"
mkdir -p "$TEST_DIR"
cd "$TEST_DIR"
unzip -q /Users/oli/Mac/Apps/woo-package/preproduct.zip

# Test 6: Verify main plugin file exists
run_test "Main plugin file exists" "[ -f woo-preproduct.php ]"

# Test 7: Verify required WordPress plugin files exist
run_test "Required plugin files exist" "[ -f README.txt ] && [ -f changelog.txt ] && [ -f uninstall.php ]"

# Test 8: Verify plugin directory structure exists
run_test "Plugin directory structure exists" "[ -d includes ] && [ -d assets ] && [ -d languages ]"

# Test 9: Verify development files are excluded
EXCLUDED_FILES=(
    "node_modules"
    "vendor"
    "tests"
    "test"
    "package.json"
    "composer.json"
    "composer.lock"
    "yarn.lock"
    ".git"
    ".gitignore"
    ".vscode"
    ".idea"
    ".cursor"
    ".claude"
    ".taskmaster"
    ".mcp.json"
    ".env.example"
    "zip"
    "CLAUDE.md"
    "README.md"
)

ALL_EXCLUDED=true
for file in "${EXCLUDED_FILES[@]}"; do
    if [ -e "$file" ]; then
        echo "✗ EXCLUDED FILE FOUND: $file"
        ALL_EXCLUDED=false
    fi
done

if $ALL_EXCLUDED; then
    echo "✓ PASS: All development files properly excluded"
    ((TESTS_PASSED++))
else
    echo "✗ FAIL: Some development files were not excluded"
    ((TESTS_FAILED++))
fi
echo ""

# Test 10: Verify .distignore patterns work for different file types
PATTERN_TEST_PASSED=true

# Check for .DS_Store files
if find . -name ".DS_Store" -type f | grep -q ".DS_Store"; then
    echo "✗ PATTERN FAIL: .DS_Store files found"
    PATTERN_TEST_PASSED=false
fi

# Check for temporary files
if find . -name "*.tmp" -o -name "*.temp" -o -name "*.log" | grep -q "tmp\|temp\|log"; then
    echo "✗ PATTERN FAIL: Temporary files found"
    PATTERN_TEST_PASSED=false
fi

# Check for build artifacts  
if find . -name "*.map" | grep -q "map"; then
    echo "✗ PATTERN FAIL: Build artifacts found"
    PATTERN_TEST_PASSED=false
fi

if $PATTERN_TEST_PASSED; then
    echo "✓ PASS: Pattern exclusions working correctly"
    ((TESTS_PASSED++))
else
    echo "✗ FAIL: Some patterns not working correctly"
    ((TESTS_FAILED++))
fi
echo ""

# Test 11: Verify archive size is reasonable (not too large with dev files)
ARCHIVE_SIZE=$(stat -f%z "/Users/oli/Mac/Apps/woo-package/preproduct.zip" 2>/dev/null || stat -c%s "/Users/oli/Mac/Apps/woo-package/preproduct.zip" 2>/dev/null || echo "0")
MAX_SIZE=10485760  # 10MB

if [ "$ARCHIVE_SIZE" -lt "$MAX_SIZE" ]; then
    echo "✓ PASS: Archive size is reasonable ($(($ARCHIVE_SIZE / 1024))KB)"
    ((TESTS_PASSED++))
else
    echo "✗ FAIL: Archive size is too large ($(($ARCHIVE_SIZE / 1024))KB) - may include development files"
    ((TESTS_FAILED++))
fi
echo ""

# Test 12: Verify WordPress plugin header exists
if grep -q "Plugin Name:" woo-preproduct.php; then
    echo "✓ PASS: WordPress plugin header found"
    ((TESTS_PASSED++))
else
    echo "✗ FAIL: WordPress plugin header not found"
    ((TESTS_FAILED++))
fi
echo ""

# Test 13: Count total files and verify reasonable number
TOTAL_FILES=$(find . -type f | wc -l)
MIN_FILES=10
MAX_FILES=100

if [ "$TOTAL_FILES" -ge "$MIN_FILES" ] && [ "$TOTAL_FILES" -le "$MAX_FILES" ]; then
    echo "✓ PASS: Archive contains reasonable number of files ($TOTAL_FILES)"
    ((TESTS_PASSED++))
else
    echo "✗ FAIL: Archive contains unexpected number of files ($TOTAL_FILES)"
    ((TESTS_FAILED++))
fi
echo ""

# Cleanup
cd - > /dev/null
rm -rf "$TEST_DIR"

# Summary
echo "=== TEST SUMMARY ==="
echo "Tests Passed: $TESTS_PASSED"
echo "Tests Failed: $TESTS_FAILED"
echo "Total Tests: $((TESTS_PASSED + TESTS_FAILED))"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED! The zip script properly excludes non-plugin files."
    echo "✓ Archive is WordPress plugin compliant"
    echo "✓ Development files are properly excluded"
    echo "✓ Required plugin files are included"
    exit 0
else
    echo "⚠️  SOME TESTS FAILED. Please review the exclusion patterns."
    exit 1
fi