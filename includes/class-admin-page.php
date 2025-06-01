<?php
/**
 * Admin Page Class
 *
 * Handles the WordPress admin interface for PreProduct including
 * the iframe that loads the PreProduct WooCommerce management interface
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooPreProduct Admin Page Class
 */
class WooPreProduct_Admin_Page
{
	
	/**
	 * Constructor - Initialize hooks
	 */
	public function __construct()
	{
		// Only load admin functionality in admin area
		if (is_admin()) {
			add_action('admin_menu', array($this, 'add_admin_menu'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		}
	}
	
	/**
	 * Add admin menu item under WooCommerce
	 */
	public function add_admin_menu()
	{
		// Only add menu if WooCommerce is active and user has proper permissions
		if (function_exists('WC') && current_user_can('manage_woocommerce')) {
			add_submenu_page(
				'woocommerce',
				__('PreProduct', 'woo-preproduct'),
				__('PreProduct', 'woo-preproduct'),
				'manage_woocommerce',
				'woo-preproduct',
				array($this, 'render_admin_page')
			);
		}
	}
	
	/**
	 * Enqueue admin styles for the PreProduct page
	 *
	 * @param string $hook_suffix The current admin page hook suffix
	 */
	public function enqueue_admin_styles($hook_suffix)
	{
		// Only load on our admin page
		if ($hook_suffix === 'woocommerce_page_woo-preproduct') {
			wp_enqueue_style(
				'woo-preproduct-admin',
				plugin_dir_url(WOO_PREPRODUCT_PLUGIN_FILE) . 'assets/admin.css',
				array(),
				WOO_PREPRODUCT_VERSION
			);
		}
	}
	
	/**
	 * Render the admin page with embedded iframe
	 */
	public function render_admin_page()
	{
		// Get iframe URL from environment manager
		$environment_manager = WooPreProduct_Environment_Manager::get_instance();
		$iframe_url = $environment_manager->get_iframe_url();
		
		?>
		<div class="wrap preproduct-admin-wrap">
			<h1><?php echo esc_html__('PreProduct for WooCommerce', 'woo-preproduct'); ?></h1>
			
			<div class="preproduct-admin-header">
				<p><?php echo esc_html__('Manage your pre-order settings and campaigns through the PreProduct interface below.', 'woo-preproduct'); ?></p>
			</div>
			
			<div class="preproduct-iframe-container">
				<iframe 
					src="<?php echo esc_url($iframe_url); ?>" 
					class="preproduct-iframe"
					frameborder="0"
					title="<?php echo esc_attr__('PreProduct Management Interface', 'woo-preproduct'); ?>">
					<p><?php echo esc_html__('Your browser does not support iframes. Please visit', 'woo-preproduct'); ?> 
					   <a href="<?php echo esc_url($iframe_url); ?>" target="_blank"><?php echo esc_html__('PreProduct directly', 'woo-preproduct'); ?></a>.</p>
				</iframe>
			</div>
			
			<div class="preproduct-admin-footer">
				<p class="description">
					<?php 
					printf(
						esc_html__('Having issues? Contact PreProduct support at %s', 'woo-preproduct'),
						'<a href="mailto:support@preproduct.io">support@preproduct.io</a>'
					); 
					?>
				</p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Get the admin page URL
	 *
	 * @return string The admin page URL
	 */
	public function get_admin_page_url()
	{
		return admin_url('admin.php?page=woo-preproduct');
	}
	
	/**
	 * Check if we're currently on the PreProduct admin page
	 *
	 * @return bool Whether we're on the PreProduct admin page
	 */
	public function is_preproduct_admin_page()
	{
		global $pagenow;
		return $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'woo-preproduct';
	}
} 