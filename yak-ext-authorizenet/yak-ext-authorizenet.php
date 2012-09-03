<?php
/*
Plugin Name: YAK Add-on Module - Authorize.net Payments
Description: Authorize.net add-on module for YAK-for-WordPress
Version: 3.3.0
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

define("AUTHORIZE_NET_TEST", "Authorize.net (Test)");
define("AUTHORIZE_NET", "Authorize.net");
define("AUTHORIZE_NET_TEST_URL", "https://test.authorize.net/gateway/transact.dll");
define("AUTHORIZE_NET_URL", "https://secure.authorize.net/gateway/transact.dll");

define("AUTHORIZE_NET_LOGIN_ID", "yak_authorize_login_id");
define("AUTHORIZE_NET_TRANS_KEY", "yak_authorize_trans_key");
define("AUTHORIZE_NET_LANDING_PAGE", "yak_authorize_landing_page");
define("AUTHORIZE_NET_ERROR_PAGE", "yak_authorize_error_page");
define("AUTHORIZE_NET_TEST_MODE", "yak_authorize_test_mode");


if (!function_exists('yak_authorizenet_payment_options')) {
    /**
     * hook for YAK payment types (displayed in the dropdown).
     */
    function yak_authorizenet_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[AUTHORIZE_NET_TEST] = 'SPECIAL: ' . AUTHORIZE_NET_TEST;
        $pages[AUTHORIZE_NET] = 'SPECIAL: ' . AUTHORIZE_NET;
        $options[AUTHORIZE_NET_TEST] = 'authorize.net';
        $options[AUTHORIZE_NET] = 'authorize.net';
        
        return $payments;
    }
}


if (!function_exists('yak_authorizenet_settings')) {
    /**
     * Display the settings (YAK General Options->Payment Types)
     */
    function yak_authorizenet_settings() {
        global $model;
        
        $model[AUTHORIZE_NET_LOGIN_ID] = yak_get_option(AUTHORIZE_NET_LOGIN_ID, '');
        $model[AUTHORIZE_NET_TRANS_KEY] = yak_get_option(AUTHORIZE_NET_TRANS_KEY, '');
        $model[AUTHORIZE_NET_LANDING_PAGE] = yak_get_option(AUTHORIZE_NET_LANDING_PAGE, '');
        $model[AUTHORIZE_NET_ERROR_PAGE] = yak_get_option(AUTHORIZE_NET_ERROR_PAGE, '');
        $model[AUTHORIZE_NET_TEST_MODE] = yak_get_option(AUTHORIZE_NET_TEST_MODE, '');
        ?>
        <h3><?php _e('Authorize.net settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('API Login ID', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo AUTHORIZE_NET_LOGIN_ID ?>" value="<?php echo $model[AUTHORIZE_NET_LOGIN_ID] ?>"
                        title="<?php _e('Get this from Settings, when you login to the Authorize.net admin area.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Transaction Key', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo AUTHORIZE_NET_TRANS_KEY ?>" value="<?php echo $model[AUTHORIZE_NET_TRANS_KEY] ?>" 
                        title="<?php _e('Generate this in the Settings admin area (usually 16 characters)', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Landing Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>AUTHORIZE_NET_LANDING_PAGE, 'selected'=>$model[AUTHORIZE_NET_LANDING_PAGE], 'values'=>$model[PAGES],
                        'title'=>__('Final page for a successful credit card order through authorize.net', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Error Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>AUTHORIZE_NET_ERROR_PAGE, 'selected'=>$model[AUTHORIZE_NET_ERROR_PAGE], 'values'=>$model[PAGES],
                        'title'=>__('Final page for an unsuccessful credit card order through authorize.net', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Test Mode', 'yak-admin') ?></th>
                <td><input type="checkbox" name="<?php echo AUTHORIZE_NET_TEST_MODE ?>" <?php yak_html_checkbox($model[AUTHORIZE_NET_TEST_MODE]) ?>
                        title="<?php _e('Set the test mode flag on - this allows you to test your website connection to the gateway without processing live transactions.', 'yak-admin') ?>" /></td>
            </tr>
        </table>
        <?php
    }
}


if (!function_exists('yak_authorizenet_apply_settings')) {
    function yak_authorizenet_apply_settings() {
        yak_admin_options_set(AUTHORIZE_NET_LOGIN_ID);
        yak_admin_options_set(AUTHORIZE_NET_TRANS_KEY);
        yak_admin_options_set(AUTHORIZE_NET_LANDING_PAGE);
        yak_admin_options_set(AUTHORIZE_NET_ERROR_PAGE);
        yak_admin_options_set(AUTHORIZE_NET_TEST_MODE, 'off');
    }
}


if (!function_exists('yak_authorizenet_redirect')) {
    /**
     * Use Authorize.net to submit credit card transaction details.  Pass result to redirect page if successful.
     */
    function yak_authorizenet_redirect($payment_type, $order_id, $items, $shippingcost, $selected_shipping, $additional_query_string) {
        global $wpdb, $order_table;
    
        $cc = $_SESSION['cc'];
    
        $value = yak_order_value(false, $order_id);
    
        // Get Billing Address
        $baddress = yak_get_address('billing', false);

        // Get First and Last name
        $arr = split("[\n\r\t ]+", $baddress->recipient);
        $firstname = $arr[0];
        $lastname = $arr[1];

        // Get Shipping Address
        $saddress = yak_get_address('shipping', false);  

        // Get First and Last name for shipping address
        $arr2 = split("[\n\r\t ]+", $saddress->recipient);
        $s_firstname = $arr2[0];
        $s_lastname = $arr2[1];
            
        // Create Customer ID
        $cust_id = rand(1000000000, 9999999999);
    
        $params_array = array(
            'x_login'              => yak_get_option(AUTHORIZE_NET_LOGIN_ID),
            'x_tran_key'           => yak_get_option(AUTHORIZE_NET_TRANS_KEY),
            'x_version'            => '3.1',
            'x_delim_char'         => '|',
            'x_delim_data'         => 'TRUE',
            'x_method'             => 'CC',
            'x_relay_response'     => 'FALSE',
            'x_type'               => 'AUTH_CAPTURE',
            'x_amount'             => number_format($value, 2, '.', ''),
            'x_card_num'           => $cc['number'],
            'x_exp_date'           => $cc['expiry'],
            'x_card_code'          => $cc['security_code'],
            'x_trans_id'           => $order_id,
            'x_description'        => get_bloginfo('description') . ' Products',
            'c_cust_id'            => $cust_id,
            'x_country'            => $baddress->country,
            'x_zip'                => $baddress->postcode,
            'x_city'               => $baddress->city,
            'x_phone'              => $baddress->phone,
            'x_address'            => $baddress->addr1,
            'x_state'              => $baddress->get_state_or_region(),
            'x_email'              => $baddress->email,
            'x_first_name'         => $firstname,
            'x_last_name'          => $lastname,
            'x_ship_to_country'    => $saddress->country,
            'x_ship_to_zip'        => $saddress->postcode,
            'x_ship_to_city'       => $saddress->city,
            'x_ship_to_phone'      => $saddress->phone,
            'x_ship_to_address'    => $saddress->addr1,
            'x_ship_to_state'      => $saddress->get_state_or_region(),
            'x_ship_to_first_name' => $s_firstname,
            'x_ship_to_last_name'  => $s_lastname
        );
    
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        if ($payment_types[$payment_type] == AUTHORIZE_NET) {
            $url = AUTHORIZE_NET_URL;
        }
        else {
            $url = AUTHORIZE_NET_TEST_URL;
        }
    
        if (yak_get_option(AUTHORIZE_NET_TEST_MODE, 'off') == 'on') {
            $params_array['x_test_request'] = 'TRUE';
        }
    
        $params = yak_encode_params($params_array);
    
        yak_log("Using URL: " . $url);

        $redirect_uri = yak_get_option(AUTHORIZE_NET_LANDING_PAGE, '');
    
        if ($value > 0.0) {
            $response = yak_do_http($url, '', $params, null, 'POST', 120);
        
            $split = explode('|', $response);
    
            $rtn = "";
            if ($split[0] == 1) {
                $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $value, $order_id);
                $wpdb->query($sql);
              
                yak_insert_orderlog($order_id, 'Authorize.net transaction was approved');
              
                $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
            }
            else if ($split[0] == 2) {
                $_SESSION['error_message'] = __('The transaction has been declined', 'yak');
        
                $sql = $wpdb->prepare("update $order_table set status = %s where id = %d", ERROR, $order_id);
                $wpdb->query($sql);

                yak_insert_orderlog($order_id, 'Authorize.net transaction was declined');
        
                $rtn = yak_get_option(AUTHORIZE_NET_ERROR_PAGE, '');
                if (!empty($rtn)) {
                    $rtn .= $additional_query_string;
                }
            }
            else if ($split[0] == 4) {
                yak_insert_orderlog($order_id, 'Authorize.net transaction is held for review -- please resolve manually');
    
                $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
            }
            else {
                if ($split[0] == 3) {
                    $err = $split[3];
                    yak_insert_orderlog($order_id, 'An error occurred processing the Authorize.net transaction: ' . $err);
                }
                else {
                    $err = __('An unexpected error occurred processing your transaction, please try again later.');
                }
        
                $sql = $wpdb->prepare("update $order_table set status = %s where id = %d", ERROR, $order_id);
                $wpdb->query($sql);
        
                $_SESSION['error_message'] = $err;
                
                $rtn = yak_get_option(AUTHORIZE_NET_ERROR_PAGE, '');
                if (!empty($rtn)) {
                    $rtn .= $additional_query_string;
                }
            }
    
            yak_insert_orderlog($order_id, 'response received from Authorize.net was: ' . $response);
        }
        else {
            // no order value -- just redirect to the success page
            $rtn = yak_redirect_page($order_id, $items, $shippingcost, true, $redirect_uri);
            yak_insert_orderlog($order_id, 'Total order cost is 0, not submitting to Authorize.net');
        }
    
        yak_check_order($order_id);

        return $rtn;
    }
}


if (!function_exists('yak_authorizenet_next_page')) {
    /**
     * hook into the checkout process to display YAK's credit card input form.
     */
    function yak_authorizenet_next_page() {
        return 'yak-view-cc.php';
    }
}


add_action('yak-payment-settings', 'yak_authorizenet_settings');
add_action('yak-payment-apply-settings', 'yak_authorizenet_apply_settings');
add_filter('yak-redirect-authorize.net', 'yak_authorizenet_redirect', 10, 6);
add_filter('yak-next-page-authorize.net', 'yak_authorizenet_next_page');
add_filter('yak-payment-options', 'yak_authorizenet_payment_options');  
?>