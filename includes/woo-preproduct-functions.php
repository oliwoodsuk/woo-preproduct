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
