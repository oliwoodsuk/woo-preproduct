<?php
/**
 * Admin Page Class
 *
 * Handles the WordPress admin interface for PreProduct including
 * the iframe that loads the PreProduct WooCommerce management interface
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PreProduct Admin Page Class
 */
class PreProduct_Admin_Page
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
				__('Pre-orders', 'preproduct'),
				__('Pre-orders', 'preproduct'),
				'manage_woocommerce',
				'preproduct',
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
		if ($hook_suffix === 'woocommerce_page_preproduct') {
			wp_enqueue_style(
				'preproduct-admin',
				plugin_dir_url(PREPRODUCT_PLUGIN_FILE) . 'assets/css/admin.css',
				array(),
				PREPRODUCT_VERSION
			);
		}
	}
	
	/**
	 * Render the admin page with embedded iframe
	 */
	public function render_admin_page()
	{
		// Get iframe URL from environment manager
		$environment_manager = PreProduct_Environment_Manager::get_instance();
		$iframe_url = $environment_manager->get_iframe_url();
		
		?>
		<div class="wrap preproduct-admin-wrap">
			<h1><?php echo esc_html__('PreProduct for WooCommerce', 'preproduct'); ?></h1>
			
			<div class="preproduct-admin-header">
				<p><?php echo esc_html__('Manage your pre-order settings and campaigns through the PreProduct interface below.', 'preproduct'); ?></p>
			</div>
			
			<div class="preproduct-iframe-container">
				<iframe 
					src="<?php echo esc_url($iframe_url); ?>" 
					class="preproduct-iframe"
					frameborder="0"
					title="<?php echo esc_attr__('PreProduct Management Interface', 'preproduct'); ?>">
					<p><?php echo esc_html__('Your browser does not support iframes. Please visit', 'preproduct'); ?> 
					   <a href="<?php echo esc_url($iframe_url); ?>" target="_blank"><?php echo esc_html__('PreProduct directly', 'preproduct'); ?></a>.</p>
				</iframe>
			</div>
			
			<div class="preproduct-admin-footer">
				<p class="description">
					<?php 
					printf(
						/* translators: %s: PreProduct support email address link */
						esc_html__('Having issues? Contact PreProduct support at %s', 'preproduct'),
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
		return admin_url('admin.php?page=preproduct');
	}
	
	/**
	 * Check if we're currently on the PreProduct admin page
	 *
	 * @return bool Whether we're on the PreProduct admin page
	 */
	public function is_preproduct_admin_page()
	{
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Just checking which admin page we're on, no form processing
		return $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'preproduct';
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
			'title'   => __('Overview', 'preproduct'),
			'content' => $this->get_help_content_overview(),
		));
		
		// Setup tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_setup',
			'title'   => __('Setup Guide', 'preproduct'),
			'content' => $this->get_help_content_setup(),
		));
		
		// Troubleshooting tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_troubleshooting',
			'title'   => __('Troubleshooting', 'preproduct'),
			'content' => $this->get_help_content_troubleshooting(),
		));
		
		// Support tab
		$screen->add_help_tab(array(
			'id'      => 'preproduct_support',
			'title'   => __('Support', 'preproduct'),
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
		return '<h3>' . __('PreProduct Overview', 'preproduct') . '</h3>
		<p>' . __('PreProduct for WooCommerce seamlessly integrates your store with the PreProduct platform to enable pre-order functionality.', 'preproduct') . '</p>
		<h4>' . __('Key Features:', 'preproduct') . '</h4>
		<ul>
			<li>' . __('Automatic button enhancement for eligible products', 'preproduct') . '</li>
			<li>' . __('Environment-aware configuration (development/production)', 'preproduct') . '</li>
			<li>' . __('Seamless integration with existing WooCommerce functionality', 'preproduct') . '</li>
			<li>' . __('Real-time webhook notifications for plugin events', 'preproduct') . '</li>
			<li>' . __('Comprehensive error logging and debugging', 'preproduct') . '</li>
		</ul>
		<p>' . __('The plugin automatically detects your environment and configures appropriate endpoints for seamless integration.', 'preproduct') . '</p>';
	}
	
	/**
	 * Get setup help content
	 */
	private function get_help_content_setup()
	{
		return '<h3>' . __('Setup Guide', 'preproduct') . '</h3>
		<h4>' . __('Initial Setup:', 'preproduct') . '</h4>
		<ol>
			<li>' . __('Ensure WooCommerce is installed and active', 'preproduct') . '</li>
			<li>' . __('Install and activate the PreProduct plugin', 'preproduct') . '</li>
			<li>' . __('Navigate to WooCommerce > PreProduct (you should be redirected automatically)', 'preproduct') . '</li>
			<li>' . __('Follow the setup instructions in the PreProduct interface', 'preproduct') . '</li>
		</ol>
		<h4>' . __('Environment Configuration:', 'preproduct') . '</h4>
		<p>' . __('The plugin automatically detects your environment:', 'preproduct') . '</p>
		<ul>
			<li><strong>' . __('Development:', 'preproduct') . '</strong> ' . __('localhost, .test, .local, staging, dev domains', 'preproduct') . '</li>
			<li><strong>' . __('Production:', 'preproduct') . '</strong> ' . __('All other domains', 'preproduct') . '</li>
		</ul>
		<h4>' . __('Manual Override:', 'preproduct') . '</h4>
		<p>' . __('Add to wp-config.php to force an environment:', 'preproduct') . '</p>
		<code>define(\'PREPRODUCT_DEV_MODE\', true); // Force development mode</code>';
	}
	
	/**
	 * Get troubleshooting help content
	 */
	private function get_help_content_troubleshooting()
	{
		$log_file_path = '';
		if (class_exists('PreProduct_Logger')) {
			$log_file_path = PreProduct_Logger::get_log_file_path();
		}
		
		return '<h3>' . __('Troubleshooting', 'preproduct') . '</h3>
		<h4>' . __('Common Issues:', 'preproduct') . '</h4>
		<dl>
			<dt><strong>' . __('PreProduct interface not loading', 'preproduct') . '</strong></dt>
			<dd>' . __('Check your internet connection and ensure your server can reach external domains.', 'preproduct') . '</dd>
			
			<dt><strong>' . __('Buttons not being enhanced', 'preproduct') . '</strong></dt>
			<dd>' . __('The plugin currently enhances simple products on collection pages. Variable, grouped, and external products are not supported yet.', 'preproduct') . '</dd>
			
			<dt><strong>' . __('Plugin activation errors', 'preproduct') . '</strong></dt>
			<dd>' . __('Ensure WooCommerce is installed and active before activating PreProduct.', 'preproduct') . '</dd>
		</dl>
		<h4>' . __('Debugging:', 'preproduct') . '</h4>
		<p>' . __('Enable WordPress debugging by adding these lines to wp-config.php:', 'preproduct') . '</p>
		<code>
		define(\'WP_DEBUG\', true);<br>
		define(\'WP_DEBUG_LOG\', true);
		</code>' . 
		($log_file_path ? '<p>' . sprintf(
			/* translators: %s: file path to log file */
			__('PreProduct logs can be found at: %s', 'preproduct'), 
			'<code>' . esc_html($log_file_path) . '</code>'
		) . '</p>' : '');
	}
	
	/**
	 * Get support help content
	 */
	private function get_help_content_support()
	{
		return '<h3>' . __('Support Resources', 'preproduct') . '</h3>
		<h4>' . __('Documentation:', 'preproduct') . '</h4>
		<ul>
			<li><a href="https://docs.preproduct.io/woocommerce" target="_blank">' . __('WooCommerce Integration Guide', 'preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io/api" target="_blank">' . __('API Documentation', 'preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io/troubleshooting" target="_blank">' . __('Troubleshooting Guide', 'preproduct') . '</a></li>
		</ul>
		<h4>' . __('Get Help:', 'preproduct') . '</h4>
		<ul>
			<li><strong>' . __('Email Support:', 'preproduct') . '</strong> <a href="mailto:support@preproduct.io">support@preproduct.io</a></li>
			<li><strong>' . __('Plugin Support:', 'preproduct') . '</strong> <a href="https://preproduct.io/support" target="_blank">preproduct.io/support</a></li>
		</ul>
		<h4>' . __('System Information:', 'preproduct') . '</h4>
		<ul>
			<li><strong>' . __('Plugin Version:', 'preproduct') . '</strong> ' . PREPRODUCT_VERSION . '</li>
			<li><strong>' . __('WordPress Version:', 'preproduct') . '</strong> ' . get_bloginfo('version') . '</li>
			<li><strong>' . __('WooCommerce Version:', 'preproduct') . '</strong> ' . (defined('WC_VERSION') ? WC_VERSION : __('Not installed', 'preproduct')) . '</li>
			<li><strong>' . __('PHP Version:', 'preproduct') . '</strong> ' . PHP_VERSION . '</li>
		</ul>';
	}
	
	/**
	 * Get help sidebar content
	 */
	private function get_help_sidebar()
	{
		return '<h4>' . __('Quick Links', 'preproduct') . '</h4>
		<ul>
			<li><a href="https://preproduct.io" target="_blank">' . __('PreProduct Website', 'preproduct') . '</a></li>
			<li><a href="https://docs.preproduct.io" target="_blank">' . __('Documentation', 'preproduct') . '</a></li>
			<li><a href="https://preproduct.io/support" target="_blank">' . __('Support', 'preproduct') . '</a></li>
		</ul>
		<h4>' . __('Plugin Version', 'preproduct') . '</h4>
		<p>' . PREPRODUCT_VERSION . '</p>';
	}
} 