<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-currencies.php');

if (!function_exists('yak_redirect_page')) {
    function yak_redirect_page($order_id, $items, $shippingcost, $send_conf_email, $page_uri) {
        yak_cleanup_after_order();
        
        if ($send_conf_email) {
            yak_send_confirmation_email($order_id);
        }
        
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        
        $order_num = yak_get_order_num($order_id);
        
        return yak_get_url($page_uri, true) . 'order_id=' . $order_num;
    }
}


if (!function_exists('yak_get_payment')) {
    function yak_get_payment($payment_key) {
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        foreach ($payment_types as $key=>$val) {
            if ($key == $payment_key) {
                $payments =& yak_get_payment_opts();
                $options = $payments['options'];
                return $options[$val];
            }
        }
        return null;
    }
}


if (!function_exists('yak_get_payment_opts')) {
    function yak_get_payment_opts() {
        $pages = array();
        $options = array();
        $payments = array('pages'=>$pages, 'options'=>$options);
        
        return apply_filters('yak-payment-options', $payments);
    }
}


if (!function_exists('yak_confirmation_detail')) {
    /**
     * Create the order detail section of a confirmation message
     *
     * @param $order_id the id of the order
     * @param $detail template for each line of the detail message
     * @param $results an array of sql results for the order detail
     * @param $pre_delim the delimiter to use at the beginning of multi-select-option details
     * @param $delim the delimiter to use at the end of multi-select-option details
     */
    function yak_confirmation_detail($order_id, $detail, $results, $pre_delim = '     ', $delim = "\n") {
        global $wpdb, $order_meta_table;
        
        $totalprice = 0;
        $totalquantity = 0;
        $detailmsg = '';
        foreach ($results as $result) {
            // dump 'additional' items (those without an id)
            if ($result->post_id == null) {
                continue;
            }
            
            $price = $result->price;
            $qty = $result->quantity;
            $totalprice += ($price * $qty);
            $itemprice = $price * $qty;
            $totalquantity += $qty;
            $itemname = $result->itemname;
            
            $additionalinfo = '';
            if ($result->multi_select_count > 0) {
                $sql = $wpdb->prepare("select value 
                                       from $order_meta_table
                                       where order_id = %d 
                                       and name like concat(%s, '%%')", $order_id, $itemname);
                $options = $wpdb->get_results($sql);
                foreach ($options as $opt) {
                    $additionalinfo .= $pre_delim . $opt->value . $delim;
                }
            }
            if (!empty($result->meta)) {
                foreach ($result->meta as $key=>$val) {
                    $additionalinfo .= $pre_delim . $key . ': ' . $val . $delim;
                }
            }
            
            $detailmsg = $detailmsg . sprintf($detail, $itemname, $additionalinfo, $qty, yak_format_money($price, false), yak_format_money($itemprice, false));
        }
        return array($detailmsg, $totalprice, $totalquantity);
    }
}


if (!function_exists('yak_confirmation_additional')) {
    /**
     * Add in order details which aren't actually items (metadata stuff)
     */
    function yak_confirmation_additional($order_id, $detail, $results, $pre_delim = '     ', $delim = "\n") {
        $totalprice = 0;
        $totalquantity = 0;
        $detailmsg = '';
        foreach ($results as $result) {
            // dump items which do have a post id (i.e. products)
            if ($result->post_id != null) {
                continue;
            }
            $price = $result->price;
            $qty = $result->quantity;
            $totalprice += $result->total;
            $itemprice = $result->total;
            $itemname = $result->itemname;
                        
            $detailmsg = $detailmsg . sprintf($detail, $itemname, yak_format_money($itemprice, true));
        }
        return array($detailmsg, $totalprice, $totalquantity);
    }
}

if (!function_exists('yak_send_confirmation_email')) {
    /**
     * Send a confirmation email for an order.
     *
     * @param $order_id the id of the order
     */
    function yak_send_confirmation_email($order_id) {
        global $wpdb, $order_table, $order_detail_table, $order_meta_table;
        
        $conf_email = yak_get_option(CONFIRMATION_EMAIL_ADDRESS, '');
        
        if (!isset($conf_email) || $conf_email == '') {
            return;
        }
        
        $mail = yak_get_option(CONFIRMATION_MESSAGE, '');
        $subject = yak_get_option(CONFIRMATION_SUBJECT, __('Order Confirmation', 'yak'));
        
        $orders = yak_get_orders(null, null, null, null, false, true, true, $order_id);
        $order = $orders[0];
        
        $mail = yak_generate_mail($mail, $order_id, $order->order_num, $order, $order->items);
        $subject = yak_generate_subject($subject, $order_id, $order);
        
        if (empty($mail)) {
            yak_log("WARNING: no confirmation message has been set");
            return;
        }
        
        $billing_addr = $order->get_billing_address();
        $shipping_addr = $order->get_shipping_address();
        
        $email = $billing_addr->email;
        if (empty($email)) {
            $email = $shipping_addr->email;
        }
        
        if (defined('YAK_DEBUG')) {
            yak_log("Mail message to $email : $mail");
        }
        
        yak_sendmail($conf_email, $email, $subject, $mail); 
        yak_sendmail($conf_email, $conf_email, $subject . ' - ' . __('the following email has been sent to ', 'yak') . $email, $mail);
        yak_insert_orderlog($order_id, "Sent order confirmation to: $email, notification to: $conf_email");
    }
}


if (!function_exists('yak_test_confirmation_email')) {
    /**
     * Send a confirmation email for an order.
     *
     * @param $order_id the id of the order
     */
    function yak_test_confirmation_email() {
        $conf_email = yak_get_option(CONFIRMATION_EMAIL_ADDRESS, '');
        
        if (!isset($conf_email) || $conf_email == '') {
            return;
        }
        
        $mail = yak_get_option(CONFIRMATION_MESSAGE, '');
        $subject = yak_get_option(CONFIRMATION_SUBJECT, __('Order Confirmation', 'yak'));
        
        $order = new YakOrder(-1, '11111-55555-44444', null, 'PAYMENT-TYPE', 0, 10.00, 129.90, '', 'SHIPPING-TYPE');
        $order->shipping_addr = new YakAddress('joe@test.com', 'Joe Test', null, '555-1234', '12 Test Rd', null, 'Testville', 'Testington', null, null, '12345', 'NZ', 'shipping');
        $order->billing_addr = new YakAddress('joe@test.com', 'Joe Test', null, '555-1234', '12 Test Rd', null, 'Testville', 'Testington', null, null, '12345', 'NZ', 'shipping');
        $order->country_code = 'NZ';
        
        $order_detail = array();
        
        $detail->id = 10;
        $detail->itemname = 'Test Item';
        $detail->price = 59.95;
        $detail->quantity = 2;
        $detail->total = $detail->price * $detail->quantity;
        $detail->post_id = 1;
        $detail->cat_id = -1;
        $detail->multi_select_count = 0;
        $order_detail[] = $detail;
        
        $mail = yak_generate_mail($mail, 1000, '11111-55555-44444', $order, $order_detail);
        $subject = yak_generate_subject($subject, 1000, $order);
        
        if (defined('YAK_DEBUG')) {
            yak_log("Mail message: $mail");
        }
        
        yak_sendmail($conf_email, $conf_email, __('Test email: ', 'yak-admin') . $subject, $mail);
    }
}


function yak_generate_mail($mail, $order_id, $order_num, $order, $order_detail) {
    global $countries;
    
    $totalprice = 0;
    $totalquantity = 0;
    
    $billing_addr = $order->get_billing_address();
    $shipping_addr = $order->get_shipping_address();

    $email = $billing_addr->email;
    $name = $shipping_addr->recipient;
    $phone = $shipping_addr->phone;

    $address = $name . "\n" . $shipping_addr->as_string('email', 'recipient', 'phone', 'country');
    $address .= "\n" . $countries[$shipping_addr->country];
    
    $baddress = $billing_addr->recipient . "\n" . $billing_addr->as_string('email', 'recipient', 'phone', 'country');
    $baddress .= "\n" . $countries[$billing_addr->country];

    $payment_type = $order->payment_type;
    $shipping = $order->shipping_cost;
    
    if (yak_str_contains($mail, '[order_detail]')) {
        $detail = __('Item', 'yak') . ": %s\n" .
                  "%s" .
                  __('Quantity', 'yak') . ": %d\n" .
                  __('Price', 'yak') . ": %s\n" .
                  __('Total' , 'yak') . ": %s\n\n";
    
        $confdet = yak_confirmation_detail($order_id, $detail, $order_detail);
        $detailmsg = $confdet[0];
        $totalprice += $confdet[1];
        $totalquantity += $confdet[2];
        
        $confdet = yak_confirmation_additional($order_id, "%s %s\n", $order_detail);
        $detailmsg .= $confdet[0];
        $totalprice += $confdet[1];
        $totalquantity += $confdet[2];
        
        $mail = str_replace('[order_detail]', $detailmsg, $mail);
    }
    
    if (yak_str_contains($mail, '[shipping_cost]')) {
        $mail = str_replace('[shipping_cost]', yak_format_money($shipping, true), $mail);
    }
    
    if (yak_str_contains($mail, '[html_order_detail]')) {
        $detail = "<tr>\n"
                . "  <td>%s%s</td>\n"
                . "  <td>%s</td>\n"
                . "  <td>%s</td>\n"
                . "  <td>%s</td>\n"
                . "</tr>\n";
    
        $detailmsg = "<table width=\"40%\" border=\"1\">\n"
                   . "  <tr>\n"
                   . "    <th>" . __('Item', 'yak') . "</th>\n"
                   . "    <th>" . __('Quantity', 'yak') . "</th>\n"
                   . "    <th>" . __('Price', 'yak') . "</th>\n"
                   . "    <th>" . __('Total', 'yak') . "</th>\n"
                   . "  </tr>\n";
        
        $confdet = yak_confirmation_detail($order_id, $detail, $order_detail, '<br />&nbsp;&nbsp;', '');
        $detailmsg .= $confdet[0];
        $totalprice += $confdet[1];
        $totalquantity += $confdet[2];

        $detailmsg .= "</table>\n";
        
        $confdet = yak_confirmation_additional($order_id, "<p>%s %s</p>\n", $order_detail, '', '');
        $detailmsg .= $confdet[0];
        $totalprice += $confdet[1];
        $totalquantity += $confdet[2];
        
        $detailmsg .= "<p>" . __('Shipping costs', 'yak') . ' ' . yak_format_money($shipping, true) . "</p>\n";
        
        $mail = str_replace('[html_order_detail]', $detailmsg, $mail);
    }
    
    $totalprice += $shipping;
    
    $mail = str_replace('[shipping_type]', $order->selected_shipping_type, $mail);
    $mail = str_replace('[order_id]', $order_num, $mail);
    $mail = str_replace('[order_cost]', yak_format_money($totalprice, true), $mail);
    $mail = str_replace('[payment_type]', $payment_type, $mail);
    $mail = str_replace('[selected_shipping_type]', $order->selected_shipping_type, $mail);
    $mail = str_replace('[shipping_address]', $address, $mail);
    $mail = str_replace('[billing_address]', $baddress, $mail);
    $mail = str_replace('[html_shipping_address]', str_replace("\n", "<br />", $address), $mail);
    $mail = str_replace('[html_billing_address]', str_replace("\n", "<br />", $baddress), $mail);
    $mail = str_replace('[name]', $name, $mail);
    $mail = str_replace('[phone]', $phone, $mail);
    
    if (yak_str_contains($mail, '[special_instructions]')) {
        $mail = str_replace('[special_instructions]', $order->meta['Special Instructions'], $mail);
    }
    
    $mail = apply_filters('yak-mail', $mail, $order, $order_detail);
    
    return $mail;
}

if (!function_exists('yak_generate_subject')) {
    function yak_generate_subject($subject, $order_id, $order) {
        $subject = str_replace('[order_id]', $order->order_num, $subject);
    
        return $subject;
    }
}

if (!function_exists('yak_get_next_payment_page')) {
    function yak_get_next_payment_page($ptypeval) {
        $payments =& yak_get_payment_opts();
        $options = $payments['options'];

        if ($options[$ptypeval] != null) {
            $next_page = apply_filters('yak-next-page-' . $options[$ptypeval], null);
            
            if (!empty($next_page) && defined('YAK_DEBUG')) {
                yak_log("Next Payment Action for $ptypeval = " . $next_page);
            }
            
            return $next_page;
        }
        
        return null;
    }
}

if (!function_exists('yak_in_blacklist')) {
    function yak_in_blacklist($shipping_address, $billing_address) {
        $blacklist = yak_get_option(BLACKLIST, '');
        
        $shipping_email = null;
        if ($shipping_address != null && !empty($shipping_address->email)) {
            $shipping_email = $shipping_address->email;
            $shipping_email_wildcard = '*' . substr($shipping_email, strpos($shipping_email, '@'));
        }
        
        $billing_email = null;
        if ($billing_address != null && !empty($billing_address->email)) {
            $billing_email = $billing_address->email;
            $billing_email_wildcard = '*' . substr($billing_email, strpos($shipping_email, '@'));
        }
        
        if ($shipping_email != null 
            && (yak_str_contains($blacklist, $shipping_email)
                || yak_str_contains($blacklist, $shipping_email_wildcard))) {
            return true; 
        }
        
        if ($billing_email != null 
            && (yak_str_contains($blacklist, $billing_email)
                || yak_str_contains($blacklist, $billing_email_wildcard))) {
            return true; 
        }
        
        return false;
    }
}
?>