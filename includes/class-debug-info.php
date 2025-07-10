<?php

/**
 * Debug Info Class
 *
 * Provides debugging information about environment detection
 * Only available for administrators and in development environments
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooPreProduct Debug Info Class
 */
class WooPreProduct_Debug_Info
{
    /**
     * Initialize debug info hooks
     */
    public static function init()
    {
        // Only show debug info for administrators
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add debug query parameter handler
        add_action('init', array(__CLASS__, 'handle_debug_request'));
    }

    /**
     * Handle debug request
     */
    	public static function handle_debug_request()
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a read-only debug endpoint for administrators only
		if (isset($_GET['woo_preproduct_debug']) && $_GET['woo_preproduct_debug'] === 'environment') {
			self::enqueue_debug_styles();
			self::display_environment_info();
			exit;
		}
	}

    /**
     * Enqueue debug page styles
     */
    private static function enqueue_debug_styles()
    {
        wp_enqueue_style(
            'preproduct-debug',
            plugin_dir_url(WOO_PREPRODUCT_PLUGIN_FILE) . 'assets/css/debug.css',
            array(),
            WOO_PREPRODUCT_VERSION
        );
        
        // Print styles immediately since we're outside normal WordPress flow
        wp_print_styles('preproduct-debug');
    }

    /**
     * Display environment information
     */
    public static function display_environment_info()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $env_manager = woo_preproduct_environment();
        $urls = $env_manager->get_all_urls();

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>PreProduct Environment Debug</title>
        </head>
        <body>
            <h1>PreProduct Environment Debug Information</h1>
            
            <div class="debug-info <?php echo $env_manager->is_development() ? 'env-dev' : 'env-prod'; ?>">
                <h2>Current Environment: <?php echo esc_html(strtoupper($env_manager->get_environment())); ?></h2>
                <p><strong>Site URL:</strong> <?php echo esc_html(site_url()); ?></p>
                <p><strong>Is Development:</strong> <?php echo $env_manager->is_development() ? 'Yes' : 'No'; ?></p>
                <p><strong>Is Production:</strong> <?php echo $env_manager->is_production() ? 'Yes' : 'No'; ?></p>
                <?php if (defined('PREPRODUCT_DEV_MODE')) : ?>
                    <p><strong>PREPRODUCT_DEV_MODE Constant:</strong> <?php echo PREPRODUCT_DEV_MODE ? 'true' : 'false'; ?></p>
                <?php else : ?>
                    <p><strong>PREPRODUCT_DEV_MODE Constant:</strong> Not defined</p>
                <?php endif; ?>
            </div>
            
            <h3>Endpoint URLs</h3>
            <table>
                <thead>
                    <tr>
                        <th>Endpoint Type</th>
                        <th>URL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Script URL</td>
                        <td class="url"><?php echo esc_html($urls['script']); ?></td>
                    </tr>
                    <tr>
                        <td>Iframe URL</td>
                        <td class="url"><?php echo esc_html($urls['iframe']); ?></td>
                    </tr>
                    <tr>
                        <td>Webhook URL</td>
                        <td class="url"><?php echo esc_html($urls['webhook']); ?></td>
                    </tr>
                    <tr>
                        <td>API Base URL</td>
                        <td class="url"><?php echo esc_html($urls['api_base']); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Environment Detection Rules</h3>
            <ul>
                <li>Development environments are detected when the site URL contains: localhost, .test, .local, staging, or dev</li>
                <li>You can override environment detection by defining <code>PREPRODUCT_DEV_MODE</code> in wp-config.php</li>
                <li>Set <code>define('PREPRODUCT_DEV_MODE', true);</code> to force development mode</li>
                <li>Set <code>define('PREPRODUCT_DEV_MODE', false);</code> to force production mode</li>
            </ul>
            
            <p><a href="<?php echo esc_url(admin_url()); ?>">← Back to WordPress Admin</a></p>
        </body>
        </html>
        <?php
    }
}
