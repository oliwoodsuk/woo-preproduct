<?php

/**
 * PreProduct Helper Functions
 *
 * @package PreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the main PreProduct instance
 *
 * @return PreProduct_Plugin
 */
function preproduct()
{
    return PreProduct_Plugin::instance();
}

/**
 * Get the Environment Manager instance
 *
 * @return PreProduct_Environment_Manager
 */
function preproduct_environment()
{
    return preproduct()->environment();
}

/**
 * Check if we're in development mode
 *
 * @return bool
 */
function preproduct_is_dev()
{
    return preproduct_environment()->is_development();
}

/**
 * Check if we're in production mode
 *
 * @return bool
 */
function preproduct_is_production()
{
    return preproduct_environment()->is_production();
}

/**
 * Get the PreProduct script URL
 *
 * @return string
 */
function preproduct_get_script_url()
{
    return preproduct_environment()->get_script_url();
}

/**
 * Get the PreProduct iframe URL
 *
 * @return string
 */
function preproduct_get_iframe_url()
{
    return preproduct_environment()->get_iframe_url();
}

/**
 * Get the PreProduct webhook URL
 *
 * @return string
 */
function preproduct_get_webhook_url()
{
    return preproduct_environment()->get_webhook_url();
}

/**
 * Get the PreProduct base API URL
 *
 * @return string
 */
function preproduct_get_api_base_url()
{
    return preproduct_environment()->get_api_base_url();
}

/**
 * Get all PreProduct URLs
 *
 * @return array
 */
function preproduct_get_all_urls()
{
    return preproduct_environment()->get_all_urls();
}

/**
 * Get the Button Tagger instance
 *
 * @return PreProduct_Button_Tagger|null
 */
function preproduct_button_tagger()
{
	return preproduct()->button_tagger;
}

/**
 * Check if PreProduct should be enabled for a specific product
 *
 * @param WC_Product $product The product object
 * @return bool
 */
function preproduct_is_enabled_for_product($product)
{
	$button_tagger = preproduct_button_tagger();
	if ($button_tagger && method_exists($button_tagger, 'shouldEnablePreproduct')) {
		return $button_tagger->shouldEnablePreproduct($product);
	}
	return false;
}

/**
 * Get the Script Manager instance
 *
 * @return PreProduct_Script_Manager|null
 */
function preproduct_script_manager()
{
	return preproduct()->script_manager;
}

/**
 * Check if the PreProduct script should be loaded
 *
 * @return bool
 */
function preproduct_should_load_script()
{
	$script_manager = preproduct_script_manager();
	if ($script_manager && method_exists($script_manager, 'should_load_script')) {
		return $script_manager->should_load_script();
	}
	return false;
}

/**
 * Get the PreProduct script handle
 *
 * @return string
 */
function preproduct_get_script_handle()
{
	$script_manager = preproduct_script_manager();
	if ($script_manager && method_exists($script_manager, 'get_script_handle')) {
		return $script_manager->get_script_handle();
	}
	return 'preproduct-embed';
}

/**
 * Get the Admin Page instance
 *
 * @return PreProduct_Admin_Page|null
 */
function preproduct_admin_page()
{
	return preproduct()->admin_page;
}

/**
 * Get the admin page URL
 *
 * @return string
 */
function preproduct_get_admin_page_url()
{
	$admin_page = preproduct_admin_page();
	if ($admin_page && method_exists($admin_page, 'get_admin_page_url')) {
		return $admin_page->get_admin_page_url();
	}
	return admin_url('admin.php?page=preproduct');
}

/**
 * Check if we're currently on the PreProduct admin page
 *
 * @return bool
 */
function preproduct_is_admin_page()
{
	$admin_page = preproduct_admin_page();
	if ($admin_page && method_exists($admin_page, 'is_preproduct_admin_page')) {
		return $admin_page->is_preproduct_admin_page();
	}
	return false;
}
