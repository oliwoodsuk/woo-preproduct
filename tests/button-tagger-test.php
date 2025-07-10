<?php
/**
 * Button Tagger Test Script
 * 
 * Run this with: php test-button-tagger.php
 */

// Define ABSPATH to prevent WordPress security check from exiting
define('ABSPATH', '/fake/wordpress/path/');

// Mock WordPress functions
function esc_attr($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_js($text) {
    return $text;
}

function apply_filters($hook, $value, ...$args) {
    return $value;
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    // Mock implementation - just return true
    return true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    // Mock implementation - just return true
    return true;
}

// Mock WooCommerce function
function wc_get_product($id) {
    return new WC_Product($id);
}

// Mock WooCommerce Product class
class WC_Product {
    private $id;
    private $type;
    private $sku;
    private $price;
    
    public function __construct($id = 123, $type = 'simple', $sku = 'TEST-SKU', $price = '19.99') {
        $this->id = $id;
        $this->type = $type;
        $this->sku = $sku;
        $this->price = $price;
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function get_type() {
        return $this->type;
    }
    
    public function get_sku() {
        return $this->sku;
    }
    
    public function get_price() {
        return $this->price;
    }
}

class WC_Product_Variable extends WC_Product {
    public function __construct() {
        parent::__construct(456, 'variable', 'VAR-SKU', '29.99');
    }
}

class WC_Product_Grouped extends WC_Product {
    public function __construct() {
        parent::__construct(789, 'grouped', 'GROUP-SKU', '0');
    }
}

class WC_Product_External extends WC_Product {
    public function __construct() {
        parent::__construct(101, 'external', 'EXT-SKU', '49.99');
    }
}

// Include the Button Tagger class
require_once 'includes/class-button-tagger.php';

class ButtonTaggerTest {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    
    public function run_all_tests() {
        echo "🧪 Running Button Tagger Tests (Collection Pages Only)\n";
        echo "==================================================\n\n";
        
        $this->test_collection_page_attributes();
        $this->test_product_filtering();
        $this->test_invalid_product_handling();
        $this->test_html_modification();
        $this->test_attribute_generation();
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: {$this->tests_passed}\n";
        echo "❌ Failed: {$this->tests_failed}\n";
        
        if ($this->tests_failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above.\n";
        }
    }
    
    private function test_collection_page_attributes() {
        echo "Testing Collection Page Button Attribute Generation...\n";
        
        $tagger = new PreProduct_Button_Tagger();
        $product = new WC_Product(123, 'simple', 'TEST-SKU', '19.99');
        
        // Test HTML modification for collection pages
        $original_html = '<a href="/cart?add-to-cart=123" class="button add_to_cart_button">Add to cart</a>';
        $modified_html = $tagger->addPreproductAttributes($original_html, $product);
        
        // Check if attributes were added to the opening tag, not as text content
        if (strpos($modified_html, 'data-native-pre-order-btn') !== false) {
            $this->assert_true(true, "✅ data-native-pre-order-btn attribute added");
        } else {
            $this->assert_true(false, "❌ data-native-pre-order-btn attribute missing");
        }
        
        if (strpos($modified_html, 'data-quick-pre-order') !== false) {
            $this->assert_true(true, "✅ data-quick-pre-order attribute added");
        } else {
            $this->assert_true(false, "❌ data-quick-pre-order attribute missing");
        }
        
        if (strpos($modified_html, 'data-id="123"') !== false) {
            $this->assert_true(true, "✅ data-id attribute with correct product ID added");
        } else {
            $this->assert_true(false, "❌ data-id attribute with product ID missing");
        }
        
        // Check that attributes are in the opening tag, not in the text content
        $opening_tag_end = strpos($modified_html, '>');
        $closing_tag_start = strpos($modified_html, '</a>');
        $attributes_in_opening_tag = strpos(substr($modified_html, 0, $opening_tag_end), 'data-native-pre-order-btn') !== false;
        $attributes_in_text_content = strpos(substr($modified_html, $opening_tag_end, $closing_tag_start - $opening_tag_end), 'data-native-pre-order-btn') !== false;
        
        if ($attributes_in_opening_tag && !$attributes_in_text_content) {
            $this->assert_true(true, "✅ Attributes correctly placed in opening tag, not in text content");
        } else {
            $this->assert_true(false, "❌ Attributes incorrectly placed in text content instead of opening tag");
        }
        
        echo "🔍 Modified HTML: " . $modified_html . "\n\n";
    }
    
    private function test_product_filtering() {
        echo "Testing Product Type Filtering (Simple Products Only)...\n";
        
        $tagger = new PreProduct_Button_Tagger();
        
        // Test with simple product - should be tagged
        $simple_product = new WC_Product(123, 'simple', 'SIMPLE-SKU', '19.99');
        $simple_html = '<a href="?add-to-cart=123" class="button">Add to cart</a>';
        $simple_result = $tagger->addPreproductAttributes($simple_html, $simple_product);
        
        if (strpos($simple_result, 'data-native-pre-order-btn') !== false) {
            $this->assert_true(true, "✅ Simple product gets tagged with PreProduct attributes");
        } else {
            $this->assert_true(false, "❌ Simple product should get tagged with PreProduct attributes");
        }
        
        // Test with variable product - should NOT be tagged
        $variable_product = new WC_Product_Variable();
        $variable_html = '<a href="?add-to-cart=456" class="button">Select options</a>';
        $variable_result = $tagger->addPreproductAttributes($variable_html, $variable_product);
        
        if (strpos($variable_result, 'data-native-pre-order-btn') === false) {
            $this->assert_true(true, "✅ Variable product correctly excluded from tagging");
        } else {
            $this->assert_true(false, "❌ Variable product should be excluded from tagging");
        }
        
        // Test shouldEnablePreproduct method for both types
        $enabled_simple = $tagger->shouldEnablePreproduct($simple_product);
        $enabled_variable = $tagger->shouldEnablePreproduct($variable_product);
        
        if ($enabled_simple === true) {
            $this->assert_true(true, "✅ shouldEnablePreproduct returns true for simple products");
        } else {
            $this->assert_true(false, "❌ shouldEnablePreproduct should return true for simple products");
        }
        
        if ($enabled_variable === true) {
            $this->assert_true(true, "✅ shouldEnablePreproduct still returns true for variable products (filtering happens at type level)");
        } else {
            $this->assert_true(false, "❌ shouldEnablePreproduct should return true for variable products (filtering happens at type level)");
        }
        
        echo "🔍 Simple product result: " . (strpos($simple_result, 'data-native-pre-order-btn') !== false ? 'TAGGED' : 'NOT TAGGED') . "\n";
        echo "🔍 Variable product result: " . (strpos($variable_result, 'data-native-pre-order-btn') !== false ? 'TAGGED' : 'NOT TAGGED') . "\n";
        
        // Test with grouped product - should NOT be tagged
        $grouped_product = new WC_Product_Grouped();
        $grouped_html = '<a href="?add-to-cart=789" class="button">View products</a>';
        $grouped_result = $tagger->addPreproductAttributes($grouped_html, $grouped_product);
        
        if (strpos($grouped_result, 'data-native-pre-order-btn') === false) {
            $this->assert_true(true, "✅ Grouped product correctly excluded from tagging");
        } else {
            $this->assert_true(false, "❌ Grouped product should be excluded from tagging");
        }
        
        // Test with external product - should NOT be tagged
        $external_product = new WC_Product_External();
        $external_html = '<a href="?add-to-cart=101" class="button">Buy product</a>';
        $external_result = $tagger->addPreproductAttributes($external_html, $external_product);
        
        if (strpos($external_result, 'data-native-pre-order-btn') === false) {
            $this->assert_true(true, "✅ External product correctly excluded from tagging");
        } else {
            $this->assert_true(false, "❌ External product should be excluded from tagging");
        }
        
        echo "🔍 Grouped product result: " . (strpos($grouped_result, 'data-native-pre-order-btn') !== false ? 'TAGGED' : 'NOT TAGGED') . "\n";
        echo "🔍 External product result: " . (strpos($external_result, 'data-native-pre-order-btn') !== false ? 'TAGGED' : 'NOT TAGGED') . "\n\n";
    }
    
    private function test_invalid_product_handling() {
        echo "Testing Invalid Product Handling...\n";
        
        $tagger = new PreProduct_Button_Tagger();
        
        // Test with null product
        $original_html = '<a href="/cart?add-to-cart=123">Add to cart</a>';
        $result_html = $tagger->addPreproductAttributes($original_html, null);
        
        if ($result_html === $original_html) {
            $this->assert_true(true, "✅ Null product handled gracefully");
        } else {
            $this->assert_true(false, "❌ Null product not handled properly");
        }
        
        // Test with invalid object
        $result_html2 = $tagger->addPreproductAttributes($original_html, "not a product");
        
        if ($result_html2 === $original_html) {
            $this->assert_true(true, "✅ Invalid product object handled gracefully");
        } else {
            $this->assert_true(false, "❌ Invalid product object not handled properly");
        }
        
        echo "\n";
    }
    
    private function test_html_modification() {
        echo "Testing HTML Modification Edge Cases...\n";
        
        $tagger = new PreProduct_Button_Tagger();
        $product = new WC_Product();
        
        // Test with HTML that doesn't have closing </a>
        $invalid_html = '<button class="add-to-cart">Add to cart</button>';
        $result = $tagger->addPreproductAttributes($invalid_html, $product);
        
        if ($result === $invalid_html) {
            $this->assert_true(true, "✅ Non-anchor HTML left unchanged");
        } else {
            $this->assert_true(false, "❌ Non-anchor HTML was modified incorrectly");
        }
        
        // Test with empty HTML
        $empty_html = '';
        $result2 = $tagger->addPreproductAttributes($empty_html, $product);
        
        if ($result2 === $empty_html) {
            $this->assert_true(true, "✅ Empty HTML handled gracefully");
        } else {
            $this->assert_true(false, "❌ Empty HTML not handled properly");
        }
        
        echo "\n";
    }
    
    private function test_attribute_generation() {
        echo "Testing Attribute Generation...\n";
        
        $tagger = new PreProduct_Button_Tagger();
        
        // Use reflection to test private method
        $reflection = new ReflectionClass($tagger);
        $method = $reflection->getMethod('generatePreproductAttributes');
        $method->setAccessible(true);
        
        $product = new WC_Product(789, 'simple', 'ATTR-TEST', '39.99');
        $attributes = $method->invoke($tagger, $product);
        
        // Check if all expected attributes are present
        $expected_parts = [
            'data-native-pre-order-btn',
            'data-quick-pre-order',
            'data-id="789"',
            'data-product-type="simple"',
            'data-sku="ATTR-TEST"',
            'data-price="39.99"'
        ];
        
        foreach ($expected_parts as $expected) {
            if (strpos($attributes, $expected) !== false) {
                $this->assert_true(true, "✅ Found expected attribute: $expected");
            } else {
                $this->assert_true(false, "❌ Missing expected attribute: $expected");
            }
        }
        
        echo "🔍 Generated attributes: " . $attributes . "\n\n";
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
$test = new ButtonTaggerTest();
$test->run_all_tests(); 