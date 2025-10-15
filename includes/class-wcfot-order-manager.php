<?php
if (!defined('ABSPATH')) exit;

class WCFOT_Order_Manager {

    public function __construct() {
        // Add submenu under WooCommerce
        add_action('admin_menu', [$this, 'register_submenu_page']);
        add_action('admin_init', [$this, 'handle_order_actions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_submenu_page() {
        add_submenu_page(
            'woocommerce',
            'Woo-Commerce Facebook Order Tracking',
            'Woo-Commerce Facebook Order Tracking',
            'manage_woocommerce',
            'wcfot_order_tracking',
            [$this, 'render_order_page']
        );
    }

   public function enqueue_admin_assets($hook) {
    if ($hook !== 'woocommerce_page_wcfot_order_tracking') return;

    // Enqueue CSS with file modification time as version to bypass cache
    wp_enqueue_style(
        'wcfot-admin-css', 
        WCFOT_PLUGIN_URL . 'admin/css/admin.css', 
        [], 
        filemtime(WCFOT_PLUGIN_PATH . 'admin/css/admin.css')
    );

    // Enqueue JS with file modification time as version
    wp_enqueue_script(
        'wcfot-admin-js', 
        WCFOT_PLUGIN_URL . 'admin/js/admin.js', 
        ['jquery'], 
        filemtime(WCFOT_PLUGIN_PATH . 'admin/js/admin.js'), 
        true
    );
}


    public function render_order_page() {
        $file = WCFOT_PLUGIN_PATH . 'admin/views/order-management-page.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<p style="color:red;">Order management page file not found!</p>';
        }
    }

    public function handle_order_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wcfot_order_tracking') return;
        if (!isset($_GET['action'], $_GET['order_id'])) return;

        $order_id = intval($_GET['order_id']);
        $action   = sanitize_text_field($_GET['action']);
        $status   = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $order    = wc_get_order($order_id);
        if (!$order) return;

        switch ($action) {
            case 'mark_fake':
                update_post_meta($order_id, '_is_fake_order', 'yes');
                $order->update_status('cancelled');
                break;

            case 'change_status':
                if (in_array($status, ['processing', 'on-hold', 'completed', 'cancelled'])) {
                    $order->update_status($status);
                }
                break;
        }

        wp_redirect(admin_url('admin.php?page=wcfot_order_tracking'));
        exit;
    }


    
}
