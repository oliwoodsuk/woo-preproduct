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
			$page_hook = add_submenu_page(
				'woocommerce',
				__('Pre-orders', 'woo-preproduct'),
				__('Pre-orders', 'woo-preproduct'),
				'manage_woocommerce',
				'woo-preproduct',
				array($this, 'render_admin_page')
			);
			
			// Add help tab when the page loads
			add_action('load-' . $page_hook, array($this, 'add_help_tab'));
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
						'<a href="mailto:hello@preproduct.io">hello@preproduct.io</a>'
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
	
	/**
	 * Add help tab to the admin page
	 */
	public function add_help_tab()
	{
		$screen = get_current_screen();
		
		// Overview tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_overview',
			'title'   => __('Overview', 'woo-preproduct'),
			'content' => $this->get_help_content_overview(),
		));
		
		// Setup tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_setup',
			'title'   => __('Setup Guide', 'woo-preproduct'),
			'content' => $this->get_help_content_setup(),
		));
		
		// Troubleshooting tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_troubleshooting',
			'title'   => __('Troubleshooting', 'woo-preproduct'),
			'content' => $this->get_help_content_troubleshooting(),
		));
		
		// Support tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_support',
			'title'   => __('Support', 'woo-preproduct'),
			'content' => $this->get_help_content_support(),
		));
		
		// Set help sidebar
		$screen->set_help_sidebar($this->get_help_sidebar());
	}
	
	/**
	 * Get overview help content
	 */
	private function get_help_content_overview()
	{
		return '<h3>' . __('PreProduct Overview', 'woo-preproduct') . '</h3>
		<p>' . __('PreProduct for WooCommerce seamlessly integrates your store with the PreProduct platform to enable pre-order functionality.', 'woo-preproduct') . '</p>
		<h4>' . __('Key Features:', 'woo-preproduct') . '</h4>
		<ul>
			<li>' . __('Automatic button enhancement for eligible products', 'woo-preproduct') . '</li>
			<li>' . __('Environment-aware configuration (development/production)', 'woo-preproduct') . '</li>
			<li>' . __('Seamless integration with existing WooCommerce functionality', 'woo-preproduct') . '</li>
			<li>' . __('Real-time webhook notifications for plugin events', 'woo-preproduct') . '</li>
			<li>' . __('Comprehensive error logging and debugging', 'woo-preproduct') . '</li>
		</ul>
		<p>' . __('The plugin automatically detects your environment and configures appropriate endpoints for seamless integration.', 'woo-preproduct') . '</p>';
	}
	
	/**
	 * Get setup help content
	 */
	private function get_help_content_setup()
	{
		return '<h3>' . __('Setup Guide', 'woo-preproduct') . '</h3>
		<h4>' . __('Initial Setup:', 'woo-preproduct') . '</h4>
		<ol>
			<li>' . __('Ensure WooCommerce is installed and active', 'woo-preproduct') . '</li>
			<li>' . __('Install and activate the PreProduct plugin', 'woo-preproduct') . '</li>
			<li>' . __('Navigate to WooCommerce > PreProduct (you should be redirected automatically)', 'woo-preproduct') . '</li>
			<li>' . __('Follow the setup instructions in the PreProduct interface', 'woo-preproduct') . '</li>
		</ol>
		<h4>' . __('Environment Configuration:', 'woo-preproduct') . '</h4>
		<p>' . __('The plugin automatically detects your environment:', 'woo-preproduct') . '</p>
		<ul>
			<li><strong>' . __('Development:', 'woo-preproduct') . '</strong> ' . __('localhost, .test, .local, staging, dev domains', 'woo-preproduct') . '</li>
			<li><strong>' . __('Production:', 'woo-preproduct') . '</strong> ' . __('All other domains', 'woo-preproduct') . '</li>
		</ul>
		<h4>' . __('Manual Override:', 'woo-preproduct') . '</h4>
		<p>' . __('Add to wp-config.php to force an environment:', 'woo-preproduct') . '</p>
		<code>define(\'PREPRODUCT_DEV_MODE\', true); // Force development mode</code>';
	}
	
	/**
	 * Get troubleshooting help content
	 */
	private function get_help_content_troubleshooting()
	{
		$log_file_path = '';
		if (class_exists('WooPreProduct_Logger')) {
			$log_file_path = WooPreProduct_Logger::get_log_file_path();
		}
		
		return '<h3>' . __('Troubleshooting', 'woo-preproduct') . '</h3>
		<h4>' . __('Common Issues:', 'woo-preproduct') . '</h4>
		<dl>
			<dt><strong>' . __('PreProduct interface not loading', 'woo-preproduct') . '</strong></dt>
			<dd>' . __('Check your internet connection and ensure your server can reach external domains.', 'woo-preproduct') . '</dd>
			
			<dt><strong>' . __('Buttons not being enhanced', 'woo-preproduct') . '</strong></dt>
			<dd>' . __('The plugin currently enhances simple products on collection pages. Variable, grouped, and external products are not supported yet.', 'woo-preproduct') . '</dd>
			
			<dt><strong>' . __('Plugin activation errors', 'woo-preproduct') . '</strong></dt>
			<dd>' . __('Ensure WooCommerce is installed and active before activating PreProduct.', 'woo-preproduct') . '</dd>
		</dl>
		<h4>' . __('Debugging:', 'woo-preproduct') . '</h4>
		<p>' . __('Enable WordPress debugging by adding these lines to wp-config.php:', 'woo-preproduct') . '</p>
		<code>
		define(\'WP_DEBUG\', true);<br>
		define(\'WP_DEBUG_LOG\', true);
		</code>' . 
		($log_file_path ? '<p>' . sprintf(__('PreProduct logs can be found at: %s', 'woo-preproduct'), '<code>' . esc_html($log_file_path) . '</code>') . '</p>' : '');
	}
	
	/**
	 * Get support help content
	 */
	private function get_help_content_support()
	{
		return '<h3>' . __('Support Resources', 'woo-preproduct') . '</h3>
		<h4>' . __('Documentation:', 'woo-preproduct') . '</h4>
		<ul>
			<li><a href="https://docs.preproduct.io/woocommerce" target="_blank">' . __('WooCommerce Integration Guide', 'woo-preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io/api" target="_blank">' . __('API Documentation', 'woo-preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io/troubleshooting" target="_blank">' . __('Troubleshooting Guide', 'woo-preproduct') . '</a></li>
		</ul>
		<h4>' . __('Get Help:', 'woo-preproduct') . '</h4>
		<ul>
			<li><strong>' . __('Email Support:', 'woo-preproduct') . '</strong> <a href="mailto:support@preproduct.io">support@preproduct.io</a></li>
			<li><strong>' . __('Plugin Support:', 'woo-preproduct') . '</strong> <a href="https://preproduct.io/support" target="_blank">preproduct.io/support</a></li>
		</ul>
		<h4>' . __('System Information:', 'woo-preproduct') . '</h4>
		<ul>
			<li><strong>' . __('Plugin Version:', 'woo-preproduct') . '</strong> ' . WOO_PREPRODUCT_VERSION . '</li>
			<li><strong>' . __('WordPress Version:', 'woo-preproduct') . '</strong> ' . get_bloginfo('version') . '</li>
			<li><strong>' . __('WooCommerce Version:', 'woo-preproduct') . '</strong> ' . (defined('WC_VERSION') ? WC_VERSION : __('Not installed', 'woo-preproduct')) . '</li>
			<li><strong>' . __('PHP Version:', 'woo-preproduct') . '</strong> ' . PHP_VERSION . '</li>
		</ul>';
	}
	
	/**
	 * Get help sidebar content
	 */
	private function get_help_sidebar()
	{
		return '<h4>' . __('Quick Links', 'woo-preproduct') . '</h4>
		<ul>
			<li><a href="https://preproduct.io" target="_blank">' . __('PreProduct Website', 'woo-preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io" target="_blank">' . __('Documentation', 'woo-preproduct') . '</a></li>
			<li><a href="https://preproduct.io/support" target="_blank">' . __('Support', 'woo-preproduct') . '</a></li>
		</ul>
		<h4>' . __('Plugin Version', 'woo-preproduct') . '</h4>
		<p>' . WOO_PREPRODUCT_VERSION . '</p>';
	}
} 