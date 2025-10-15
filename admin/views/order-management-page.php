<?php
if (!defined('ABSPATH')) exit;

// Ensure WooCommerce is active
if (!class_exists('WooCommerce')) {
    echo '<p style="color:red;">WooCommerce is not active!</p>';
    return;
}

// Pagination setup
$per_page = 50;
$paged    = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset   = ($paged - 1) * $per_page;

// Get total orders count
$total_orders = wc_orders_count('completed') 
              + wc_orders_count('processing') 
              + wc_orders_count('on-hold') 
              + wc_orders_count('cancelled');

$total_pages = ceil($total_orders / $per_page);

// Fetch paginated orders
$orders = wc_get_orders([
    'limit'   => $per_page,
    'offset'  => $offset,
    'orderby' => 'date',
    'order'   => 'DESC',
]);
?>

<div class="wrap">
    <h1>Woo-Commerce Facebook Order Tracking</h1>

    <!-- Pagination Above Table -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav">
        <div class="tablenav-pages">
            <?php
            $base_url = admin_url('admin.php?page=wcfot_order_tracking');
            $max_pages_to_show = 5;
            $start_page = max(1, $paged - 2);
            $end_page   = min($total_pages, $start_page + $max_pages_to_show - 1);

            // Previous
            if ($paged > 1) {
                echo '<a class="button prev-page" href="' . esc_url(add_query_arg('paged', $paged - 1, $base_url)) . '">&laquo; Prev</a>';
            }

            // First page + ellipsis
            if ($start_page > 1) {
                echo '<a class="button" href="' . esc_url(add_query_arg('paged', 1, $base_url)) . '">1</a>';
                if ($start_page > 2) echo '<span class="pagination-ellipsis">…</span>';
            }

            // Page numbers
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active_class = ($i == $paged) ? ' active-status' : '';
                echo '<a class="button' . $active_class . '" href="' . esc_url(add_query_arg('paged', $i, $base_url)) . '">' . $i . '</a>';
            }

            // Last page + ellipsis
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) echo '<span class="pagination-ellipsis">…</span>';
                echo '<a class="button" href="' . esc_url(add_query_arg('paged', $total_pages, $base_url)) . '">' . $total_pages . '</a>';
            }

            // Next
            if ($paged < $total_pages) {
                echo '<a class="button next-page" href="' . esc_url(add_query_arg('paged', $paged + 1, $base_url)) . '">Next &raquo;</a>';
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Orders Table -->
    <table class="widefat fixed striped wcfot-table">
        <thead>
            <tr>
                <th>S/L No.</th>
                <th>Order ID</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders && count($orders) > 0): ?>
                <?php $sl = $offset + 1; ?>
                <?php foreach ($orders as $order): ?>
                    <?php 
                        $is_fake = get_post_meta($order->get_id(), '_is_fake_order', true) === 'yes'; 
                        $current_status = $is_fake ? 'blocked' : $order->get_status();
                    ?>
                    <tr <?php echo $is_fake ? 'style="background:#f8d7da;"' : ''; ?>>
                        <td><?php echo $sl++; ?></td>
                        <td>#<?php echo esc_html($order->get_id()); ?></td>
                        <td>
                            <?php 
                            echo $is_fake 
                                ? '<strong style="color:red;">Blocked</strong>' 
                                : esc_html(wc_get_order_status_name($order->get_status()));
                            ?>
                        </td>
                        <td class="actions">
                            <?php 
                            $statuses = ['processing', 'on-hold', 'completed', 'cancelled'];
                            foreach ($statuses as $status) {
                                $active_class = (!$is_fake && $current_status === $status) ? ' active-status' : '';
                                echo '<a class="button' . $active_class . '" href="' 
                                     . admin_url("admin.php?page=wcfot_order_tracking&action=change_status&status=$status&order_id=" . $order->get_id() . "&paged=$paged") 
                                     . '">' . ucfirst(str_replace('-', ' ', $status)) . '</a>';
                            }
                            // Block button
                            $block_class = $is_fake ? ' active-status' : '';
                            echo '<a class="button button-danger' . $block_class . '" href="' 
                                 . admin_url("admin.php?page=wcfot_order_tracking&action=mark_fake&order_id=" . $order->get_id() . "&paged=$paged") 
                                 . '">Block</a>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
