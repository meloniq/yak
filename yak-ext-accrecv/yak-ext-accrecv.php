<?php
/*
Plugin Name: YAK Add-on Module - Accounts Receivable Payment
Description: Accounts Receivable add-on module for YAK-for-WordPress
Version: 3.3.6
Author: a filly ate it
Author URI: http://afillyateit.com
*/

define("ACCOUNTS_RECEIVABLE", "Accounts Receivable");
define("ACC_RECV_LANDING_PAGE", "yak_accrecv_landing_page");
define("ACC_RECV_LABEL", "yak_accrecv_label");

if (!function_exists('yak_acc_recv_payment_options')) {
    function yak_acc_recv_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[ACCOUNTS_RECEIVABLE] = 'SPECIAL: ' . ACCOUNTS_RECEIVABLE;
        $options[ACCOUNTS_RECEIVABLE] = 'accounts_receivable';
        
        return $payments;
    }
}

if (!function_exists('yak_acc_recv_settings')) {
    function yak_acc_recv_settings() {
        global $model;
        
        $landing_page = yak_get_option(ACC_RECV_LANDING_PAGE, '');
        $acc_recv_label = yak_get_option(ACC_RECV_LABEL, '');
        ?>
        <h3><?php _e('Accounts Receivable settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Landing Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>ACC_RECV_LANDING_PAGE, 'selected'=>$landing_page, 'values'=>$model[PAGES],
                        'title'=>__('Final page for a successful Accounts Receivable order.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Label', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo ACC_RECV_LABEL ?>" value="<?php echo $acc_recv_label ?>"
                        title="<?php _e('Label to use on the accounts receivable form (for example, IMS, Premium account, etc)', 'yak-admin') ?>" /></td>
            </tr>
        </table>
        <?php
    }
}

if (!function_exists('yak_acc_recv_apply_settings')) {
    function yak_acc_recv_apply_settings() {
        yak_admin_options_set(ACC_RECV_LANDING_PAGE);
        yak_admin_options_set(ACC_RECV_LABEL);
    }
}

if (!function_exists('yak_acc_recv_redirect')) {
    function yak_acc_recv_redirect($payment_type, $order_id, $items, $shippingcost) {   
        $url = yak_get_option(ACC_RECV_LANDING_PAGE, '');

        return yak_redirect_page($order_id, $items, $shippingcost, true, $url);
    }
}

if (!function_exists('yak_acc_recv_confirm_order')) {
    function yak_acc_recv_confirm_order($order_id, $items) {
        global $wpdb, $order_meta_table;

        $accrecv = $_SESSION['accrecv'];
        $accrecv_number = $accrecv['number'];
        $accrecv_name = $accrecv['name'];
    
        $label = yak_get_option(ACC_RECV_LABEL);
    
        $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value) 
                               values (%d, %s, %s)", $order_id, $label . ' number', $accrecv_number);
        $wpdb->query($sql);
                  
        $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value) 
                               values (%d, %s, %s)", $order_id, $label . ' name', $accrecv_name);
        $wpdb->query($sql);
    }
}

if (!function_exists('yak_acc_recv_next_page')) {
    function yak_acc_recv_next_page() {
        return ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-view-accrecv.php';
    }
}

add_action('yak-payment-settings', 'yak_acc_recv_settings');
add_action('yak-payment-apply-settings', 'yak_acc_recv_apply_settings');
add_action('yak-confirm-order-accounts_receivable', 'yak_acc_recv_confirm_order', 10, 2);
add_filter('yak-redirect-accounts_receivable', 'yak_acc_recv_redirect', 10, 4);
add_filter('yak-payment-options', 'yak_acc_recv_payment_options');
add_filter('yak-next-page-accounts_receivable', 'yak_acc_recv_next_page');
?>