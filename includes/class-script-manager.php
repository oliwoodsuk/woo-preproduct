<?php
/**
 * Script Manager Class
 *
 * Handles injection of the PreProduct embed script into the frontend
 * with proper defer attribute and environment-based URL selection
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooPreProduct Script Manager Class
 */
class WooPreProduct_Script_Manager
{
	
	/**
	 * Constructor - Initialize hooks
	 */
	public function __construct()
	{
		// Hook into WordPress script enqueuing
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_script'));
	}
	
	/**
	 * Enqueue the PreProduct embed script on frontend pages
	 */
	public function enqueue_frontend_script()
	{
		// Only load on frontend and if WooCommerce is active
		if (!is_admin() && function_exists('WC')) {
			// Get script URL from environment manager
			$environment_manager = WooPreProduct_Environment_Manager::get_instance();
			$script_url = $environment_manager->get_script_url();
			
			// Register the script
			wp_register_script(
				'preproduct-embed',
				$script_url,
				array(),
				WOO_PREPRODUCT_VERSION,
				true // Load in footer
			);
			
			// Add defer attribute to the script tag
			add_filter('script_loader_tag', array($this, 'add_defer_attribute'), 10, 2);
			
			// Enqueue the script
			wp_enqueue_script('preproduct-embed');
		}
	}
	
	/**
	 * Add defer attribute to the PreProduct embed script
	 *
	 * @param string $tag The script tag
	 * @param string $handle The script handle
	 * @return string Modified script tag with defer attribute
	 */
	public function add_defer_attribute($tag, $handle)
	{
		// Only add defer to our specific script
		if ('preproduct-embed' === $handle) {
			// Add defer attribute
			return str_replace(' src', ' defer src', $tag);
		}
		
		return $tag;
	}
	
	/**
	 * Check if the script should be loaded on the current page
	 *
	 * @return bool Whether to load the script
	 */
	public function should_load_script()
	{
		// Don't load in admin area
		if (is_admin()) {
			return false;
		}
		
		// Don't load if WooCommerce is not active
		if (!function_exists('WC')) {
			return false;
		}
		
		// Allow filtering of which pages should load the script
		return apply_filters('woo_preproduct_load_script', true);
	}
	
	/**
	 * Get the script handle for external reference
	 *
	 * @return string The script handle
	 */
	public function get_script_handle()
	{
		return 'preproduct-embed';
	}
} 