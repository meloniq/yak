<?php
/*
Plugin Name: YAK Add-on Module - Google Checkout Payment
Description: Google Checkout add-on module for YAK-for-WordPress
Version: 3.3.3
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

define(YAK_GC_ROOT_DIR, ABSPATH . 'wp-content/plugins/yak-ext-google-checkout');
require_once(YAK_GC_ROOT_DIR . '/google/googlecart.php');
require_once(YAK_GC_ROOT_DIR . '/google/googleitem.php');
require_once(YAK_GC_ROOT_DIR . '/google/googleshipping.php');

define("GOOGLE_MERCHANT_ID", "yak_google_merchant_id");
define("GOOGLE_MERCHANT_KEY", "yak_google_merchant_key");
define("GOOGLE_CONTINUE_URL", "yak_google_continue_url");

define("GOOGLE_SANDBOX", "Google (Sandbox)");
define("GOOGLE_LIVE", "Google (Live)");
define("GOOGLE_SANDBOX_HOST", "https://sandbox.google.com");
define("GOOGLE_HOST", "https://checkout.google.com");
define("GOOGLE_SANDBOX_URL", "/checkout/api/checkout/v2/merchantCheckoutForm/Merchant/");
define("GOOGLE_URL", "/api/checkout/v2/merchantCheckoutForm/Merchant/");

if (!function_exists('yak_google_payment_options')) {
    function yak_google_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[GOOGLE_SANDBOX] = 'SPECIAL: ' . GOOGLE_SANDBOX;
        $pages[GOOGLE_LIVE] = 'SPECIAL: ' . GOOGLE_LIVE;
        
        $options[GOOGLE_SANDBOX] = 'google';
        $options[GOOGLE_LIVE] = 'google';
        
        return $payments;
    }
}

if (!function_exists('yak_google_settings')) {
    function yak_google_settings() {
        global $model;
        
        $merchant_id = yak_get_option(GOOGLE_MERCHANT_ID, '');
        $merchant_key = yak_get_option(GOOGLE_MERCHANT_KEY, '');
        $continue_url = yak_get_option(GOOGLE_CONTINUE_URL, '');
        ?>
        <h3><?php _e('Google settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Merchant ID', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo GOOGLE_MERCHANT_ID ?>" value="<?php echo $merchant_id ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Merchant Key', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo GOOGLE_MERCHANT_KEY ?>" value="<?php echo $merchant_key ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Return Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>GOOGLE_CONTINUE_URL, 'selected'=>$continue_url, 'values'=>$model[PAGES],
                        'title'=>__('The page to return to after a successful order.', 'yak-admin'))) ?></td>
            </tr>
        </table>
        <?php
    }
}

if (!function_exists('yak_google_apply_settings')) {
    function yak_google_apply_settings() {
        yak_admin_options_set(GOOGLE_MERCHANT_ID);
        yak_admin_options_set(GOOGLE_MERCHANT_KEY);
        yak_admin_options_set(GOOGLE_CONTINUE_URL);
    }
}

if (!function_exists('yak_google_redirect')) {
    function yak_google_redirect($payment_type, $order_id, $items, $shippingcost, $selected_shipping = null) {
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        $ptypeval = $payment_types[$payment_type];
        
        $merchant_id = yak_get_option(GOOGLE_MERCHANT_ID);
        $merchant_key = yak_get_option(GOOGLE_MERCHANT_KEY);
        $return_url = yak_get_option(GOOGLE_CONTINUE_URL);
        $ccy = yak_get_option(SELECTED_CURRENCY, 'USD');
        
        if ($ptypeval == GOOGLE_SANDBOX) {
            $url = GOOGLE_SANDBOX_URL;  
            $host = GOOGLE_SANDBOX_HOST;
        }
        else {
            $url = GOOGLE_URL;
            $host = GOOGLE_HOST;
        }
        
        $auth = base64_encode($merchant_id . ':' . $merchant_key);
        $headers = array('Authorization: Basic ' . $auth);
        
        $url .= $merchant_id;
        
        $body = '_type=checkout-shopping-cart';
        
        $salestax = 0;
        
        $x = 0;
        foreach ($items as $key => $item) {
            if ($item->type == SALES_TAX_PRODUCT_TYPE) {
                $salestax = number_format($item->price, 2, '.', '');
                continue;
            }
            else if ($item->id == null) {
                continue;
            }
            else {
                $itemname = yak_get_title($item->id, $item->cat_id);
            }
            $x++;
            
            $body .= '&item_name_' . $x . '=' . yak_get_title($item->id, $item->cat_id);
            $body .= '&item_description_' . $x . '=';
            $body .= '&item_quantity_' . $x . '=' . $item->quantity;
            $body .= '&item_price_' . $x . '=' . $item->get_discount_price();
            $body .= '&item_currency_' . $x . '=' . $ccy;
        }
        
        if ($salestax > 0) {
            $x++;
            $body .= '&item_name_' . $x . '=Sales+Tax&item_description_' . $x . '=';
            $body .= '&item_quantity_' . $x . '=1';
            $body .= '&item_price_' . $x . '=' . $salestax;
            $body .= '&item_currency_' . $x . '=' . $ccy;
        }
        
        $body .= '&ship_method_name_1=' . $selected_shipping;
        $body .= '&ship_method_price_1=' . $shippingcost;
        $body .= '&ship_method_currency_1=' . $ccy;
        
        if ($return_url != null) {
            $body .= '&continue-shopping-url=' . urlencode(yak_get_url($return_url, true));
        }
        
        $resp = yak_do_http($host, $url, $body, $headers, 'POST');
        
        $parsed_resp = array();
        parse_str($resp, $parsed_resp);
        
        if ($parsed_resp['_type'] == 'error') {
            die('ERROR: ' . $parsed_resp['error-message']);
        }
        
        yak_insert_orderlog($order_id, 'Google Checkout Serial Number: ' . $parsed_resp['serial-number']);
        yak_cleanup_after_order();
        
        return $parsed_resp['redirect-url'];
    }
}

add_action('yak-payment-settings', 'yak_google_settings');
add_action('yak-payment-apply-settings', 'yak_google_apply_settings');
add_filter('yak-redirect-google', 'yak_google_redirect', 10, 5);
add_filter('yak-payment-options', 'yak_google_payment_options');  
?>