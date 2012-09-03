<?php
/*
Plugin Name: YAK Add-on Module - Demonstration Payment
Description: Demo Payment add-on module for YAK-for-WordPress
Version: 3.3.5
Author: a filly ate it
Author URI: http://afillyateit.com
*/

if (file_exists(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php')) {
    require_once(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php');
}

define("DEMO_PAYMENT", "Demo Payment Gateway");
define("DEMO_CREDIT_CARDS", "yak_demo_credit_cards");
define("DEMO_RETURN_URL", "yak_demo_return");
define("DEMO_ERROR_URL", "yak_demo_error");

if (!function_exists('yak_demo_payment_options')) {
    function yak_demo_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[DEMO_PAYMENT] = 'SPECIAL: ' . DEMO_PAYMENT;
        $options[DEMO_PAYMENT] = 'demo_pro';
        
        return $payments;
    }
}

if (!function_exists('yak_demo_payment_settings')) {
    function yak_demo_payment_settings() {
        global $model;
        
        $cc = yak_get_option(DEMO_CREDIT_CARDS);
        if (isset($cc) && $cc != null) {
            $cards = implode("\n", $cc);
        }
        else {
            $cards = '';
        }

        ?>
        <h3><?php _e('Demo Payments Gateway settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Valid Credit Card Numbers', 'yak-admin') ?></th>
                <td><textarea name="<?php echo DEMO_CREDIT_CARDS ?>" cols="50" rows="5"><?php echo $cards ?></textarea><br />
                <i><?php _e('A list of credit card numbers that will result in a successful payment. Separate each with a new line', 'yak-admin') ?></i></td>
            </tr>
            <tr>
                <th><?php _e('Return Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>DEMO_RETURN_URL, 'selected'=>yak_get_option(DEMO_RETURN_URL), 'values'=>$model[PAGES])) ?><br />
                <i><?php _e('The page to return to on a successful purchase.', 'yak-admin') ?></i></td>
            </tr>
            <tr>
                <th><?php _e('Error Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>DEMO_ERROR_URL, 'selected'=>yak_get_option(DEMO_ERROR_URL), 'values'=>$model[PAGES])) ?><br />
                <i><?php _e('The page to return to if an error occurs during payments processing.', 'yak-admin') ?></i></td>
            </tr>
        </table>

        <?php
    }
}

if (!function_exists('yak_demo_payment_apply_settings')) {
    function yak_demo_payment_apply_settings() {
        $cards = explode("\n", $_POST[DEMO_CREDIT_CARDS]);
        
        update_option(DEMO_CREDIT_CARDS, $cards);
        yak_admin_options_set(DEMO_RETURN_URL);
        yak_admin_options_set(DEMO_ERROR_URL);
    }
}

if (!function_exists('yak_demo_payment_redirect')) {
    function yak_demo_payment_redirect($payment_type, $order_id, $items, $shippingcost) {
        global $wpdb, $cards, $order_table;
        
        $cards = yak_get_option(DEMO_CREDIT_CARDS, array());
        
        $cc = $_SESSION['cc'];
        
        $value = yak_order_value(false, $order_id);
        
        $redirect_uri = yak_get_option(DEMO_RETURN_URL, '');
        
        if ($value > 0.0) {
            
            if (in_array($cc['number'], $cards)) {
                $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $value, $order_id);
                $wpdb->query($sql);
             
                yak_insert_orderlog($order_id, 'Demo Payment Gateway - transaction was approved');
                
                $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);   
            }
            else {
                $_SESSION['error_message'] = 'Payment was rejected by the demo payment gateway';
            
                yak_insert_orderlog($order_id, 'Demo Payment Gateway - transaction has failed');
                
                $sql = $wpdb->prepare("update $order_table set status = %s where id = %d", ERROR, $order_id);
                $wpdb->query($sql);
            
                $rtn = yak_get_option(DEMO_ERROR_URL, '');
            }
        }
        else {
            // no order value -- just redirect to the success page
            $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
            yak_insert_orderlog($order_id, "Total order cost is 0, not 'submitting' to Demo Payment Gateway");
        }
        
        yak_check_order($order_id);
        
        return $rtn;
    }
}

if (!function_exists('yak_demo_payment_next_page')) {
    function yak_demo_payment_next_page() {
        return 'yak-view-cc.php';
    }
}

add_action('yak-payment-settings', 'yak_demo_payment_settings');
add_action('yak-payment-apply-settings', 'yak_demo_payment_apply_settings');
add_filter('yak-redirect-demo_pro', 'yak_demo_payment_redirect', 10, 4);
add_filter('yak-next-page-demo_pro', 'yak_demo_payment_next_page');
add_filter('yak-payment-options', 'yak_demo_payment_options');  
?>