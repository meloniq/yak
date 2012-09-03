<?php
/*
Plugin Name: YAK Add-on Module - PayPal Pro Payment
Description: PayPal Pro add-on module for YAK-for-WordPress
Version: 3.3.4
Author: a filly ate it
Author URI: http://afillyateit.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (file_exists(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php')) {
    require_once(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php');
}

define("PAYPAL_PRO_SANDBOX", "PayPal Payments Pro (Sandbox)");
define("PAYPAL_PRO_LIVE", "PayPal Payments Pro (Live)");
define("PAYPAL_PRO_SANDBOX_URL", "https://api-3t.sandbox.paypal.com/nvp");
define("PAYPAL_PRO_URL", "https://api-3t.paypal.com/nvp");

define("PAYPAL_API_USERNAME", "yak_paypal_api_username");
define("PAYPAL_API_PASSWORD", "yak_paypal_api_password");
define("PAYPAL_API_SIGNATURE", "yak_paypal_api_signature");
define("PAYPAL_PRO_RETURN_URL", "yak_paypal_pro_return_url");
define("PAYPAL_PRO_ERROR_URL", "yak_paypal_pro_error_url");
define("PAYPAL_PRO_INCLUDE_ADDRESS", "yak_paypal_pro_include_address");

if (!function_exists('yak_paypal_pro_payment_options')) {
    function yak_paypal_pro_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[PAYPAL_PRO_SANDBOX] = 'SPECIAL: ' . PAYPAL_PRO_SANDBOX;
        $pages[PAYPAL_PRO_LIVE] = 'SPECIAL: ' . PAYPAL_PRO_LIVE;
        
        $options[PAYPAL_PRO_SANDBOX] = 'paypal_pro';
        $options[PAYPAL_PRO_LIVE] = 'paypal_pro';
        
        return $payments;
    }
}

if (!function_exists('yak_paypal_pro_settings')) {
    function yak_paypal_pro_settings() {
        global $model;
        
        $model[PAYPAL_API_USERNAME] = yak_get_option(PAYPAL_API_USERNAME, '');
        $model[PAYPAL_API_PASSWORD] = yak_get_option(PAYPAL_API_PASSWORD, '');
        $model[PAYPAL_API_SIGNATURE] = yak_get_option(PAYPAL_API_SIGNATURE, '');
        $model[PAYPAL_PRO_RETURN_URL] = yak_get_option(PAYPAL_PRO_RETURN_URL, '');
        $model[PAYPAL_PRO_ERROR_URL] = yak_get_option(PAYPAL_PRO_ERROR_URL, '');
        $model[PAYPAL_PRO_INCLUDE_ADDRESS] = yak_get_option(PAYPAL_PRO_INCLUDE_ADDRESS, 'off');
        ?>
        <h3><?php _e('PayPal Pro settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('API Username', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo PAYPAL_API_USERNAME ?>" value="<?php echo $model[PAYPAL_API_USERNAME] ?>" size="60"
                        title="<?php _e('The API username if you\'re using PayPal Payments Pro.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('API Password', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo PAYPAL_API_PASSWORD ?>" value="<?php echo $model[PAYPAL_API_PASSWORD] ?>"
                        title="<?php _e('The PayPal Pro API Password.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('API Signature', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo PAYPAL_API_SIGNATURE ?>" value="<?php echo $model[PAYPAL_API_SIGNATURE] ?>" size="80"
                        title="<?php _e('The PayPal Pro API Signature.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Return Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>PAYPAL_PRO_RETURN_URL, 'selected'=>$model[PAYPAL_PRO_RETURN_URL], 'values'=>$model[PAGES],
                        'title'=>__('The page to return to after a successful order.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Error Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>PAYPAL_PRO_ERROR_URL, 'selected'=>$model[PAYPAL_PRO_ERROR_URL], 'values'=>$model[PAGES],
                        'title'=>__('The page to return to if an error occurs during payments processing.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Send shipping address?', 'yak-admin') ?></th>
                <td><input type="checkbox" name="<?php echo PAYPAL_PRO_INCLUDE_ADDRESS ?>" <?php yak_html_checkbox($model[PAYPAL_PRO_INCLUDE_ADDRESS]) ?> /></td>
            </tr>
        </table>
        
        <?php
    }
}

if (!function_exists('yak_paypal_pro_apply_settings')) {
    function yak_paypal_pro_apply_settings() {
        yak_admin_options_set(PAYPAL_API_USERNAME);
        yak_admin_options_set(PAYPAL_API_PASSWORD);
        yak_admin_options_set(PAYPAL_API_SIGNATURE);
        yak_admin_options_set(PAYPAL_PRO_RETURN_URL);
        yak_admin_options_set(PAYPAL_PRO_ERROR_URL);
        yak_admin_options_set(PAYPAL_PRO_INCLUDE_ADDRESS, 'off');
    }
}

if (!function_exists('yak_paypal_pro_redirect')) {
    function yak_paypal_pro_redirect($payment_type, $order_id, $items, $shippingcost) {
        global $wpdb, $cards, $order_table;
        
        $cc = $_SESSION['cc'];
        
        $value = yak_order_value(false, $order_id);
        
        $baddress = yak_get_address('billing', false);
        $saddress = yak_get_address('shipping', false);
        
        $arr = split("[\n\r\t ]+", $baddress->recipient);
        $firstname = $arr[0];
        $lastname = $arr[1];
        
        $card_detail = $cards[strtolower($cc['type'])];
        
        $params_array = array(
            'USER'              => yak_get_option(PAYPAL_API_USERNAME, ''),
            'PWD'               => yak_get_option(PAYPAL_API_PASSWORD, ''),
            'SIGNATURE'         => yak_get_option(PAYPAL_API_SIGNATURE, ''),
            'VERSION'           => '3.2',
            'METHOD'            => 'DoDirectPayment',
            'PAYMENTACTION'     => 'Sale',
            'IPADDRESS'         => yak_get_ip(),
            'CREDITCARDTYPE'    => $card_detail['paypal-name'],
            'AMT'               => round($value, 2),
            'INVNUM'            => $order_id,
            'STREET'            => $baddress->addr1,
            'CITY'              => $baddress->city,
            'STATE'             => $baddress->get_state_or_region(),
            'COUNTRYCODE'       => $baddress->country,
            'ZIP'               => $baddress->postcode,
            'FIRSTNAME'         => $firstname,
            'LASTNAME'          => $lastname
        );
        
        if (yak_get_option(PAYPAL_PRO_INCLUDE_ADDRESS, 'off') == 'on') {
            $params_array['SHIPTONAME'] = $saddress->recipient;
            $params_array['SHIPTOSTREET'] = $saddress->addr1;
            if (!empty($saddress->addr2)) {
                $params_array['SHIPTOSTREET2'] = $saddress->addr2;
            }
            $params_array['SHIPTOCITY'] = $saddress->city;
            $params_array['SHIPTOSTATE'] = $saddress->get_state_or_region();
            $params_array['SHIPTOCOUNTRY'] = $saddress->country;
            if (!empty($saddress->postcode)) {
                $params_array['SHIPTOZIP'] = $saddress->postcode;
            }
        }
        
        // encode params for writing into the order log, before we add the credit card details
        $log_params = str_replace('&', ' ', yak_encode_params($params_array));
        
        $params_array['ACCT'] = $cc['number'];
        $params_array['EXPDATE'] = str_replace('/', '', $cc['expiry']);
        $params_array['CVV2'] = $cc['security_code'];
        
        // the string we'll send to paypal
        $params = yak_encode_params($params_array);
        
        if (defined('YAK_DEBUG')) {
            yak_log("PayPal Pro params: " . $params);
        }
        
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        $ptypeval = $payment_types[$payment_type];
        
        if ($ptypeval == PAYPAL_PRO_LIVE) {
            $url = PAYPAL_PRO_URL;
        }
        else {
            $url = PAYPAL_PRO_SANDBOX_URL;
        }
        
        $redirect_uri = yak_get_option(PAYPAL_PRO_RETURN_URL, '');
        
        if ($value > 0.0) {
            yak_insert_orderlog($order_id, 'Submitting to PayPal Pro: ' . $log_params);
            $response = yak_do_http($url, '', $params);
            $param_array = yak_decode_params($response);
        
            $rtn = '';
            if ($param_array['ACK'] == 'Success' || $param_array['ACK'] == 'SuccessWithWarning') {
                $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $value, $order_id);
                $wpdb->query($sql);
             
                if ($param_array['ACK']) {     
                    yak_insert_orderlog($order_id, 'PayPal Pro transaction was approved');
                
                    $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $value, $order_id);
                    $wpdb->query($sql);
                }
                else {
                    yak_insert_orderlog($order_id, 'PayPal Pro transaction was approved with warning(s) -- please check your PayPal account (manual intervention will be required)');
                }

                $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);   
            }
            else {
                $_SESSION['error_message'] = $param_array['L_LONGMESSAGE0'];
            
                yak_insert_orderlog($order_id, 'PayPal Pro transaction has failed');
                
                $sql = $wpdb->prepare("update $order_table set status = %s where id = %d", ERROR, $order_id);
                $wpdb->query($sql);
            
                $rtn = yak_get_option(PAYPAL_PRO_ERROR_URL, '');
            }
        
            $response = '';
            foreach ($param_array as $key=>$value) {
                $response .= "$key = $value ";
            }
        
            yak_insert_orderlog($order_id, 'response received from PayPal Pro was: ' . $response);
        }
        else {
            // no order value -- just redirect to the success page
            yak_insert_orderlog($order_id, 'Total order cost is 0, not submitting to PayPal Payments Pro');
            $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
        }
        
        yak_check_order($order_id);
        
        return $rtn;
    }
}

if (!function_exists('yak_paypal_pro_next_page')) {
    function yak_paypal_pro_next_page() {
        return 'yak-view-cc.php';
    }
}

add_action('yak-payment-settings', 'yak_paypal_pro_settings');
add_action('yak-payment-apply-settings', 'yak_paypal_pro_apply_settings');
add_filter('yak-redirect-paypal_pro', 'yak_paypal_pro_redirect', 10, 4);
add_filter('yak-next-page-paypal_pro', 'yak_paypal_pro_next_page');
add_filter('yak-payment-options', 'yak_paypal_pro_payment_options');  
?>