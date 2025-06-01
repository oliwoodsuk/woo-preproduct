<?php
/**
 * Button Tagger Class
 *
 * Adds PreProduct data attributes to simple product add-to-cart buttons on collection pages only
 * Excludes variable, grouped, external, and other product types
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooPreProduct Button Tagger Class
 */
class WooPreProduct_Button_Tagger
{
	
	/**
	 * Constructor - Initialize hooks
	 */
	public function __construct()
	{
		// Hook into the add to cart button template for product loops (shop, category pages)
		add_filter('woocommerce_loop_add_to_cart_link', array($this, 'addPreproductAttributes'), 10, 2);
	}
	
	/**
	 * Add PreProduct attributes to add-to-cart buttons in product loops
	 *
	 * @param string $html The add to cart button HTML
	 * @param WC_Product $product The product object
	 * @return string Modified HTML with PreProduct attributes
	 */
	public function addPreproductAttributes($html, $product)
	{
		// Safety check for valid product
		if (!$product || !is_a($product, 'WC_Product')) {
			return $html;
		}
		
		// Check if WooCommerce is active and product is valid
		if (!function_exists('wc_get_product') || !$product->get_id()) {
			return $html;
		}
		
		// Only tag simple products, exclude variable products and variations
		if ($product->get_type() !== 'simple') {
			return $html;
		}
		
		// Check if PreProduct should be enabled for this product
		if (!$this->shouldEnablePreproduct($product)) {
			return $html;
		}
		
		// Generate PreProduct data attributes
		$attributes = $this->generatePreproductAttributes($product);
		
		// Only modify if it's an anchor tag and we have attributes
		if (strpos($html, '<a ') !== false && !empty($attributes)) {
			// Find the position of the first '>' in the opening <a> tag
			$pos = strpos($html, '>');
			if ($pos !== false) {
				// Insert attributes before the closing '>' of the opening tag
				$html = substr($html, 0, $pos) . ' ' . $attributes . substr($html, $pos);
			}
		}
		
		return $html;
	}
	
	/**
	 * Generate PreProduct data attributes for a product
	 *
	 * @param WC_Product $product The product object
	 * @return string Space-separated data attributes
	 */
	private function generatePreproductAttributes($product)
	{
		// Basic required attributes
		$attributes = array(
			'data-native-pre-order-btn' => '',
			'data-quick-pre-order' => '',
			'data-id' => esc_attr($product->get_id())
		);
		
		// Add product type for better targeting
		$attributes['data-product-type'] = esc_attr($product->get_type());
		
		// Add SKU if available
		if ($product->get_sku()) {
			$attributes['data-sku'] = esc_attr($product->get_sku());
		}
		
		// Add price for reference
		if ($product->get_price()) {
			$attributes['data-price'] = esc_attr($product->get_price());
		}
		
		// Convert to HTML attribute string
		$attribute_string = '';
		foreach ($attributes as $key => $value) {
			if ($value === '') {
				$attribute_string .= $key . ' ';
			} else {
				$attribute_string .= $key . '="' . $value . '" ';
			}
		}
		
		return trim($attribute_string);
	}
	
	/**
	 * Check if the current product should have PreProduct integration
	 *
	 * @param WC_Product $product The product object
	 * @return bool Whether PreProduct should be enabled for this product
	 */
	public function shouldEnablePreproduct($product)
	{
		// Allow filtering of which products should have PreProduct integration
		return apply_filters('woo_preproduct_enable_for_product', true, $product);
	}
} 