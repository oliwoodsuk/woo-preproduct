<?php

/**
 * WooPreProduct Helper Functions
 *
 * @package WooPreProduct
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the main WooPreProduct instance
 *
 * @return WooPreProduct
 */
function woo_preproduct()
{
    return WooPreProduct::instance();
}

/**
 * Get the Environment Manager instance
 *
 * @return WooPreProduct_Environment_Manager
 */
function woo_preproduct_environment()
{
    return woo_preproduct()->environment();
}

/**
 * Check if we're in development mode
 *
 * @return bool
 */
function woo_preproduct_is_dev()
{
    return woo_preproduct_environment()->is_development();
}

/**
 * Check if we're in production mode
 *
 * @return bool
 */
function woo_preproduct_is_production()
{
    return woo_preproduct_environment()->is_production();
}

/**
 * Get the PreProduct script URL
 *
 * @return string
 */
function woo_preproduct_get_script_url()
{
    return woo_preproduct_environment()->get_script_url();
}

/**
 * Get the PreProduct iframe URL
 *
 * @return string
 */
function woo_preproduct_get_iframe_url()
{
    return woo_preproduct_environment()->get_iframe_url();
}

/**
 * Get the PreProduct webhook URL
 *
 * @return string
 */
function woo_preproduct_get_webhook_url()
{
    return woo_preproduct_environment()->get_webhook_url();
}

/**
 * Get the PreProduct base API URL
 *
 * @return string
 */
function woo_preproduct_get_api_base_url()
{
    return woo_preproduct_environment()->get_api_base_url();
}

/**
 * Get all PreProduct URLs
 *
 * @return array
 */
function woo_preproduct_get_all_urls()
{
    return woo_preproduct_environment()->get_all_urls();
}

/**
 * Get the Button Tagger instance
 *
 * @return WooPreProduct_Button_Tagger|null
 */
function woo_preproduct_button_tagger()
{
	return woo_preproduct()->button_tagger;
}

/**
 * Check if PreProduct should be enabled for a specific product
 *
 * @param WC_Product $product The product object
 * @return bool
 */
function woo_preproduct_is_enabled_for_product($product)
{
	$button_tagger = woo_preproduct_button_tagger();
	if ($button_tagger && method_exists($button_tagger, 'shouldEnablePreproduct')) {
		return $button_tagger->shouldEnablePreproduct($product);
	}
	return false;
}

/**
 * Get the Script Manager instance
 *
 * @return WooPreProduct_Script_Manager|null
 */
function woo_preproduct_script_manager()
{
	return woo_preproduct()->script_manager;
}

/**
 * Check if the PreProduct script should be loaded
 *
 * @return bool
 */
function woo_preproduct_should_load_script()
{
	$script_manager = woo_preproduct_script_manager();
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
function woo_preproduct_get_script_handle()
{
	$script_manager = woo_preproduct_script_manager();
	if ($script_manager && method_exists($script_manager, 'get_script_handle')) {
		return $script_manager->get_script_handle();
	}
	return 'preproduct-embed';
}

/**
 * Get the Admin Page instance
 *
 * @return WooPreProduct_Admin_Page|null
 */
function woo_preproduct_admin_page()
{
	return woo_preproduct()->admin_page;
}

/**
 * Get the admin page URL
 *
 * @return string
 */
function woo_preproduct_get_admin_page_url()
{
	$admin_page = woo_preproduct_admin_page();
	if ($admin_page && method_exists($admin_page, 'get_admin_page_url')) {
		return $admin_page->get_admin_page_url();
	}
	return admin_url('admin.php?page=woo-preproduct');
}

/**
 * Check if we're currently on the PreProduct admin page
 *
 * @return bool
 */
function woo_preproduct_is_admin_page()
{
	$admin_page = woo_preproduct_admin_page();
	if ($admin_page && method_exists($admin_page, 'is_preproduct_admin_page')) {
		return $admin_page->is_preproduct_admin_page();
	}
	return false;
}
