<?php
/*
Plugin Name: YAK Add-on Module - Stripe Payments
Description: Add-on module for YAK-for-WordPress providing Stripe payments. See license.txt for license terms.
Version: 3.3.0
Author: a filly ate it
Author URI: http://afillyateit.com
*/

require_once(ABSPATH . 'wp-content/plugins/yak-ext-stripe/stripe/Stripe.php');

define("STRIPE", "Stripe");
define("STRIPE_SECRET_KEY", "yak_stripe_secret_key");
define("STRIPE_PUBLIC_KEY", "yak_stripe_public_key");
define("STRIPE_LANDING_PAGE", "yak_stripe_landing_page");
define("STRIPE_ERROR_PAGE", "yak_stripe_error_page");


if (!function_exists('yak_stripe_payment_options')) {
    function yak_stripe_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[STRIPE] = 'SPECIAL: ' . STRIPE;
        $options[STRIPE] = 'stripe';
        
        return $payments;
    }
}


if (!function_exists('yak_stripe_settings')) {
    function yak_stripe_settings() {
        global $model;
        
        $secret_key = yak_get_option(STRIPE_SECRET_KEY, '');
        $public_key = yak_get_option(STRIPE_PUBLIC_KEY, '');
        $landing_page = yak_get_option(STRIPE_LANDING_PAGE, '');
        $error_page = yak_get_option(STRIPE_ERROR_PAGE, '');
        ?>
        <h3><?php _e('Stripe settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Landing Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>STRIPE_LANDING_PAGE, 'selected'=>$landing_page, 'values'=>$model[PAGES],
                        'title'=>__('Final page for a successful order.', 'yak-admin'))) ?></td>
            </tr>
                <tr>
                    <th><?php _e('Error Page', 'yak-admin') ?></th>
                    <td><?php echo yak_html_select(array('name'=>STRIPE_ERROR_PAGE, 'selected'=>$error_page, 'values'=>$model[PAGES],
                            'title'=>__('Final page for an error.', 'yak-admin'))) ?></td>
                </tr>
            <tr>
                <th><?php _e('Secret Key', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo STRIPE_SECRET_KEY ?>" value="<?php echo $secret_key ?>" size="60"
                        title="<?php _e('The secret key provided by Stripe on your account page.', 'yak-admin') ?>" /></td>
            </tr>
                <tr>
                    <th><?php _e('Pulishable Key', 'yak-admin') ?></th>
                    <td><input type="text" name="<?php echo STRIPE_PUBLIC_KEY ?>" value="<?php echo $public_key ?>" size="60"
                            title="<?php _e('The publishable key provided by Stripe on your account page.', 'yak-admin') ?>" /></td>
                </tr>
        </table>
        
        <?php
    }
}


if (!function_exists('yak_stripe_apply_settings')) {
    function yak_stripe_apply_settings() {
        yak_admin_options_set(STRIPE_LANDING_PAGE);
        yak_admin_options_set(STRIPE_ERROR_PAGE);
        yak_admin_options_set(STRIPE_PUBLIC_KEY);
        yak_admin_options_set(STRIPE_SECRET_KEY);
    }
}


if (!function_exists('yak_stripe_redirect')) {
    function yak_stripe_redirect($payment_type, $order_id, $items, $shippingcost) {
        global $wpdb, $cards, $order_table;
        
        $value = yak_order_value(false, $order_id);
        
        $redirect_uri = yak_get_option(STRIPE_LANDING_PAGE, '');
        
        $secret_key = yak_get_option(STRIPE_SECRET_KEY, '');

        $rtn = '';        
        if ($value > 0.0) {
            $baddress = yak_get_address('billing', false);
            
            yak_insert_orderlog($order_id, 'Submitting to Stripe');
            
            Stripe::setApiKey($secret_key);
            $token = $_POST['stripeToken'];
            
            try {
                $charge = Stripe_Charge::create(array(
                    "amount" => $value * 100,
                    "currency" => "usd",
                    "card" => $token,
                    "description" => $baddress->email)
                );
                
                $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $charge->amount / 100, $order_id);
                $wpdb->query($sql);
                
                yak_insert_orderlog($order_id, 'Stripe transaction was successfully processed');
                
                $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);  
            }
            catch (Stripe_CardError $e) {
                $_SESSION['error_message'] = $e->getMessage();
            
                yak_insert_orderlog($order_id, 'Stripe transaction has failed: ' . $e->getMessage());
                
                $sql = $wpdb->prepare("update $order_table set status = %s where id = %d", ERROR, $order_id);
                $wpdb->query($sql);
            
                $rtn = yak_get_option(STRIPE_ERROR_PAGE, '');
            }
        }
        else {
            // no order value -- just redirect to the success page
            yak_insert_orderlog($order_id, 'Total order cost is 0, not submitting to Stripe');
            $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
        }
        
        yak_check_order($order_id);
        
        return $rtn;
    }
}


if (!function_exists('yak_stripe_next_page')) {
    function yak_stripe_next_page() {
        return ABSPATH . 'wp-content/plugins/yak-ext-stripe/yak-ext-stripe-creditcard.php';
    }
}


if (!function_exists('yak_stripe_confirm')) {
    function yak_stripe_confirm() {
        echo '<input type="hidden" name="stripeToken" value="' . $_REQUEST['stripeToken'] . '"/>';
    }
}


add_action('yak-payment-settings', 'yak_stripe_settings');
add_action('yak-payment-apply-settings', 'yak_stripe_apply_settings');
add_filter('yak-redirect-stripe', 'yak_stripe_redirect', 10, 4);
add_filter('yak-next-page-stripe', 'yak_stripe_next_page');
add_filter('yak-payment-options', 'yak_stripe_payment_options');  
add_action('yak-cart-confirm-hidden', 'yak_stripe_confirm');
?>