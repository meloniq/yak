<?php
define("PAYPAL_SANDBOX", "PayPal (Sandbox)");
define("PAYPAL_LIVE", "PayPal (Live)");
define("PAYPAL_SANDBOX_URL", "https://www.sandbox.paypal.com/cgi-bin/webscr");
define("PAYPAL_URL", "https://www.paypal.com/cgi-bin/webscr");

define("PAYPAL_ACCOUNT", "yak_paypal_account");
define("PAYPAL_RETURN_URL", "yak_paypal_return_url");
define("PAYPAL_CANCEL_RETURN_URL", "yak_paypal_cancel_return_url");
define("PAYPAL_IDENTITY_TOKEN", "yak_paypal_identity_token");
define("PAYPAL_PAYMENT_NOTIFICATION", "yak_paypal_payment_notification");
define("PAYPAL_INCLUDE_SHIPPING_ADDRESS", "yak_paypal_include_shipping");
define("PAYPAL_PDT", "pdt");
define("PAYPAL_PDT_TEXT", "Payment Data Transfer");
define("PAYPAL_IPN", "ipn");
define("PAYPAL_IPN_TEXT", "Instant Payment Notification");
define("PAYPAL_PAGES", "yak_paypal_pages");


if (!function_exists('yak_paypal_payment_options')) {
    function yak_paypal_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[PAYPAL_SANDBOX] = 'SPECIAL: ' . PAYPAL_SANDBOX;
        $pages[PAYPAL_LIVE] = 'SPECIAL: ' . PAYPAL_LIVE;
        $options[PAYPAL_SANDBOX] = 'paypal';
        $options[PAYPAL_LIVE] = 'paypal';
        
        return $payments;
    }
}


if (!function_exists('yak_paypal_ipn')) {
    /**
     * Function to handle a PayPal Instant Payment Notification.
     */
    function yak_paypal_ipn() {
        global $order_table, $order_log_table, $wpdb;
        
        $order_id = $_REQUEST['custom'];
        
        if (empty($order_id)) {
            echo "ERROR: Missing parameter 'custom'";
            return;
        }
        
        // paypal requires all parameters to be sent back for verification
        $params = 'cmd=_notify-validate';
        $msg = '';
        foreach ($_REQUEST as $key => $value) {
            $params = $params . '&' . $key . '=' . urlencode(stripslashes($value));
            if ($msg != '') {
                $msg = $msg . ', ';   
            }
            $msg = $msg . $key . '=' . $value;
        }
        
        $payment_gross = $_REQUEST['mc_gross'];
        if (!isset($payment_gross) || $payment_gross == '') {
            $payment_gross = $_REQUEST['payment_gross'];	
        }
        
        $cc = yak_get_option(SELECTED_CURRENCY, '');
        $payment_currency = $_REQUEST['mc_currency'];
        if (!empty($payment_currency) && $payment_currency != $cc) {
            echo "ERROR: paypal currency mismatch: $payment_currency doesn't match expected value";
            yak_log("paypal currency mismatch: $payment_currency doesn't match $cc");
            yak_insert_orderlog($order_id, "currency '$payment_currency' does not match [ $cc ]");
            return;
        }
        
        $sql = $wpdb->prepare("select funds_received from $order_table where id = %d", $order_id);
        $row = $wpdb->get_row($sql);
        if ($row->funds_received > 0) {
            echo "INFO: IPN notification has already been processed for this order";
            // we've already processed this
            return;   
        }
        
        $paypal_account = strtolower(yak_get_option(PAYPAL_ACCOUNT, ''));
        $business = strtolower($_REQUEST['business']);
        if ($business != $paypal_account) {    
            echo "ERROR: paypal business mismatch: $business or $receiver_email doesn't match expected value";
            yak_log("paypal business mismatch: $business or $receiver_email doesn't match $msg");
            yak_insert_orderlog($order_id, "business '$business' or receiver_email '$receiver_email' does not match [ $msg ]");
            return;
        }
        
        $payment_types = yak_get_option(PAYMENT_TYPES_CASE_INSENSITIVE, null);
        
        // choose the right paypal url based on what's set in the payment types array
        if (in_array(PAYPAL_SANDBOX, $payment_types)) {
            $url = parse_url(PAYPAL_SANDBOX_URL);
        }
        else {
            $url = parse_url(PAYPAL_URL);
        }
        
        // call paypal to verify
        $response = yak_do_http($url['scheme'] . '://' . $url['host'], $url['path'], $params);
        yak_log("IPN order_id=$order_id payment_gross=$payment_gross -- params=$params -- response=$response");
        
        if (!(strpos($response, 'VERIFIED') === false)) {
            $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $payment_gross, $order_id);
            $wpdb->query($sql);
            
            yak_send_confirmation_email($order_id);
            
            yak_insert_orderlog($order_id, "PayPal verified order $order_id. response: [$response]");
        }
        else {
            yak_insert_orderlog($order_id, "PayPal response *NOT* verified for order $order_id. response: [$response] data: [ $msg ]");
        }
        
        yak_check_order($order_id);
    }
}
    

if (!function_exists('yak_paypal_pdt')) {
    /**
     * Function to handle a PayPal Payment Data Transfer
     */
    function yak_paypal_pdt() {
        global $order_table, $order_log_table, $wpdb;
        
        // only process if payment notification is set to PDT (otherwise just return true)
        // this means the pdt function can be used on the success page whether or not pdt is actually
        // used for payment notification
        if (yak_get_option(PAYPAL_PAYMENT_NOTIFICATION, '') == PAYPAL_PDT && !empty($_GET['tx'])) {    
            $params = 'cmd=_notify-synch&tx=' . $_GET['tx'] . '&at=' . yak_get_option(PAYPAL_IDENTITY_TOKEN, '');
            
            $payment_types = yak_get_option(PAYMENT_TYPES_CASE_INSENSITIVE, null);
            
            // choose the right paypal url based on what's set in the payment types array
            if (in_array(PAYPAL_SANDBOX, $payment_types)) {
                $url = parse_url(PAYPAL_SANDBOX_URL);
            }
            else {
                $url = parse_url(PAYPAL_URL);
            }

            $submit_url = $url['scheme'] . '://' . $url['host'];

            yak_log("YAK PDT url " . $submit_url);
            yak_log("YAK PARAMS " . $params);
                        
            $response = yak_do_http($submit_url, $url['path'], $params, null, 'GET');
            
            if (!(strpos($response, 'SUCCESS') === false)) {
                $payment_gross = yak_get_tag_value($response, 'mc_gross=', "\n");            
                $order_id = yak_get_tag_value($response, 'custom=', "\n");
                
                yak_cleanup_after_order();
                
                $cc = yak_get_option(SELECTED_CURRENCY, '');
                $payment_currency = $_REQUEST['mc_currency'];
                if (!empty($payment_currency) && $payment_currency != $cc) {
                    echo "ERROR: paypal currency mismatch: $payment_currency doesn't match expected value";
                    yak_log("paypal currency mismatch: $payment_currency doesn't match $cc");
                    yak_insert_orderlog($order_id, "currency '$payment_currency' does not match [ $cc ]");
                    return false;
                }
                
                $sql = $wpdb->prepare("update $order_table set funds_received = %f where id = %d", $payment_gross, $order_id);
                $wpdb->query($sql);
                
                yak_check_order($order_id);
                
                yak_send_confirmation_email($order_id);
                
                yak_insert_orderlog($order_id, "PayPal response successful for id $order_id [ $response ]");
                return true;
            }
            else {
                $order_id = null;
                if (isset($_GET['order_id'])) {
                    $order_id = $_GET['order_id'];
                }
                else if (isset($_GET['cm'])) {
                    $order_id = $_GET['cm'];
                }
                if (isset($order_id)) {
                    yak_insert_orderlog($order_id, "PayPal response not successful for id $order_id [ $response ]");
                }
                return false;
            }
        }
        else {
            return true;
        }
    }
}


if (!function_exists('yak_paypal_pdt_success_tag')) {
    /**
     * [yak_paypal_pdt_success]message to display on success[/yak_paypal_pdt_success]
     */
    function yak_paypal_pdt_success_tag($attrs, $content = null) {
        $pdt = yak_paypal_pdt();
        $_REQUEST['yak_paypal_pdt'] = $pdt;
        if ($pdt) {
            return $content;
        }
        else {
            return "";
        }
    }
}


if (!function_exists('yak_paypal_pdt_failure_tag')) {
    /**
     * [yak_paypal_pdt_failure]message to display on failure[/yak_paypal_pdt_failure]
     *
     * NOTE: Must be used in conjunction with the success tag (which must also be used first)
     */
    function yak_paypal_pdt_failure_tag($attrs, $content = null) {
        $pdt = $_REQUEST['yak_paypal_pdt'];
        if ($pdt) {
            return "";
        }
        else {
            return $content;
        }
    }
}

if (!function_exists('yak_paypal_head_wp')) {
    /**
     * Stuff to do at the head of the page.
     */
    function yak_paypal_head_wp() {
        // Fix a problem with Paypal landing back at the root page of the blog, rather
        // than the proper page -- so cleanup the order if we find some common
        // paypal params in the POST
        if (!empty($_GET['custom']) && !empty($_GET['txn_id'])) {
            yak_paypal_ipn();
            yak_cleanup_after_order();
        }
        
        if (!empty($_GET['merchant_return_link'])) {
            yak_cleanup_after_order();
        }

    }
}

if (!function_exists('yak_paypal_settings')) {
    function yak_paypal_settings() {
        global $model;
        
        $model[PAYPAL_ACCOUNT] = yak_get_option(PAYPAL_ACCOUNT, '');
        $model[PAYPAL_RETURN_URL] = yak_get_option(PAYPAL_RETURN_URL, '');
        $model[PAYPAL_CANCEL_RETURN_URL] = yak_get_option(PAYPAL_CANCEL_RETURN_URL, '');
        $model[PAYPAL_IDENTITY_TOKEN] = yak_get_option(PAYPAL_IDENTITY_TOKEN, '');
        $model[PAYPAL_PAYMENT_NOTIFICATION] = yak_get_option(PAYPAL_PAYMENT_NOTIFICATION, '');
        $model[PAYPAL_INCLUDE_SHIPPING_ADDRESS] = yak_get_option(PAYPAL_INCLUDE_SHIPPING_ADDRESS, '');
           
        ?>
        <h3><?php _e('PayPal settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Account', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo PAYPAL_ACCOUNT ?>" value="<?php echo $model[PAYPAL_ACCOUNT] ?>" size="60"
                        title="<?php _e('Your account name is usually your email address.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Return Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>PAYPAL_RETURN_URL, 'selected'=>$model[PAYPAL_RETURN_URL], 'values'=>$model[PAGES],
                                'title'=>__('The page to return to on a successful purchase.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Cancel return Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>PAYPAL_CANCEL_RETURN_URL, 'selected'=>$model[PAYPAL_CANCEL_RETURN_URL], 'values'=>$model[PAGES],
                                'title'=>__('The page to return to if the customer cancels their purchase.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Identity token', 'yak-admin') ?></th>
                <td><input type="text" name="<?php echo PAYPAL_IDENTITY_TOKEN ?>" value="<?php echo $model[PAYPAL_IDENTITY_TOKEN] ?>" size="70"
                        title="<?php _e('The identity token is used if you select PDT.', 'yak-admin') ?>" /></td>
            </tr>
            <tr>
                <th><?php _e('Use PDT or IPN', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>PAYPAL_PAYMENT_NOTIFICATION, 'selected'=>$model[PAYPAL_PAYMENT_NOTIFICATION], 'values'=>array(PAYPAL_IPN=>PAYPAL_IPN_TEXT, PAYPAL_PDT=>PAYPAL_PDT_TEXT),
                                'title'=>__('Both Payment Data Transfer and Instant Payment Notification are used by paypal to let you know that a payment has been processed.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Send shipping address?', 'yak-admin') ?></th>
                <td><input type="checkbox" name="<?php echo PAYPAL_INCLUDE_SHIPPING_ADDRESS ?>" <?php yak_html_checkbox($model[PAYPAL_INCLUDE_SHIPPING_ADDRESS]) ?>
                            title="<?php _e('If selected, the customer shipping address will be included in the details submitted to PayPal', 'yak-admin') ?>" /></td>
            </tr>
        </table>
        <?php
    }
}

if (!function_exists('yak_paypal_apply_settings')) {
    function yak_paypal_apply_settings() {
        yak_admin_options_set(PAYPAL_ACCOUNT, null, false, true);
        yak_admin_options_set(PAYPAL_RETURN_URL);
        yak_admin_options_set(PAYPAL_CANCEL_RETURN_URL);
        yak_admin_options_set(PAYPAL_IDENTITY_TOKEN);
        yak_admin_options_set(PAYPAL_PAYMENT_NOTIFICATION);
        yak_admin_options_set(PAYPAL_INCLUDE_SHIPPING_ADDRESS, 'off');
    }
}

if (!function_exists('yak_paypal_redirect')) {
    function yak_paypal_redirect($payment_type, $order_id, $items, $shippingcost) {
        $cc = yak_get_option(SELECTED_CURRENCY, '');
        if (isset($cc) && $cc != '') {
            $cc = '&currency_code=' . $cc;	
        }
        else {
            $cc = '';	
        }
        
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        $ptypeval = $payment_types[$payment_type];
        
        if ($ptypeval == PAYPAL_SANDBOX) {
            $pp_url = PAYPAL_SANDBOX_URL;   
        }
        else {
            $pp_url = PAYPAL_URL;
        }
        
        $url = yak_get_url($pp_url . '?cmd=_cart&upload=1&submit=PayPal' . 
                '&business=' . urlencode(yak_get_option(PAYPAL_ACCOUNT, '')) . $cc . 
                '&return=' . urlencode(yak_get_url(yak_get_option(PAYPAL_RETURN_URL, ''), true) . '&order_id=' . $order_id) . 
                '&cancel_return=' . urlencode(yak_get_url(yak_get_option(PAYPAL_CANCEL_RETURN_URL, ''), true)) . '&no_note=1');
          
        if (yak_get_option(PAYPAL_INCLUDE_SHIPPING_ADDRESS, '') == 'on') {
            $caddress = yak_get_address('shipping', false);
        
            if (!empty($caddress->addr1)) {
                $url .= '&first_name=' . urlencode($caddress->get_first_name())
                     .  '&last_name=' . urlencode($caddress->get_last_name()) 
                     .  '&address1=' . urlencode($caddress->addr1)
                     .  '&city=' . urlencode($caddress->city)
                     .  '&country=' . urlencode($caddress->country)
                     .  '&address_override=1';
                     
                if (!empty($caddress->addr2)) {
                    $url .= '&address2=' . urlencode($caddress->addr2);
                }
                if (!empty($caddress->state)) {
                    $url .= '&state=' . urlencode($caddress->state);
                }
                if (!empty($caddress->postcode)) {
                    $url .= '&zip=' . urlencode($caddress->postcode);
                }
            }
        }
        
        $total_items = 0.0;
        foreach ($items as $key => $item) {
            $total_items += $item->quantity;
        }
        
        $total_price = 0.0;
        $count = 1;
        foreach ($items as $key => $item) {
            // special case for sales tax
            // FIXME: refactor this
            if ($item->type == SALES_TAX_PRODUCT_TYPE) {
                $url = $url . '&tax_cart=' . number_format($item->price, 2, '.', '');
            }
            else if ($item->id == null) {
                continue;
            }
            else {
                $itemname = urlencode(yak_get_title($item->id, $item->cat_id));
            
                $total_price += $item->get_discount_total();
            
                $price = number_format($item->get_discount_price(), 2, '.', '');
            
                if ($item->quantity > 0) {
                    $url = $url . '&item_name_' . $count . '=' . urlencode($item->name) . '&amount_' . $count . '=' . 
                            $price . '&quantity_' . $count . '=' . $item->quantity; 
                    $count += 1;
                }
            }
        }
        
        $total_price += $shippingcost;
        
        $price_rounding = yak_get_option(PRICE_ROUNDING, 0);
        
        if (yak_bccomp($total_price, 0.0, $price_rounding) == 0) {
            $url = yak_get_url(yak_get_option(PAYPAL_RETURN_URL, ''), true) . '&order_id=' . $order_id;
            yak_insert_orderlog($order_id, "Total order cost is $total_price (don't need to submit zero-value orders to PayPal)");
            yak_check_order($order_id);
        }
        else {
            $url .= '&custom=' . $order_id . '&handling_cart=' . $shippingcost;
            if (yak_get_option(PAYPAL_PAYMENT_NOTIFICATION, '') == PAYPAL_IPN) {
                $url .= '&notify_url=' . urlencode(yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/yak-paypal-ipn.php');
            }
            yak_log("paypal redirect url = $url");
        }
        return $url;
    }
}

add_action('yak-payment-settings', 'yak_paypal_settings');
add_action('yak-payment-apply-settings', 'yak_paypal_apply_settings');
add_filter('yak-redirect-paypal', 'yak_paypal_redirect', 10, 4);
add_filter('yak-payment-options', 'yak_paypal_payment_options');
add_shortcode('yak_paypal_pdt_success', 'yak_paypal_pdt_success_tag');
add_shortcode('yak_paypal_pdt_failure', 'yak_paypal_pdt_failure_tag');
?>