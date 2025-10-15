<?php
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name:       Woo-Commerce Facebook Order Tracking  
 * Plugin URI:        https://example.com
 * Description:       Manage WooCommerce orders and block fake orders.
 * Version:           1.0.0
 * Author:            Mahade Hassan
 * Author URI:        https://wa.me/+8801873850580
 * License:           GPL-2.0+
 * Text Domain:       wcfot-plugin
 */

define('WCFOT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCFOT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!class_exists('WCFOT_Order_Manager')) {
        require_once WCFOT_PLUGIN_PATH . 'includes/class-wcfot-order-manager.php';
    }

    // ✅ Instantiate the manager here only
    new WCFOT_Order_Manager();

} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>Woo-Commerce Facebook Order Tracking</strong> requires WooCommerce to be active!</p></div>';
    });
}
