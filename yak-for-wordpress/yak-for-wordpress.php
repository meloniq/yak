<?php
/*
Plugin Name: YAK for WordPress
Plugin URI: http://www.wordpress.org/extend/plugins/yak-for-wordpress/
Description: A shopping cart plugin for WordPress
Version: 3.4.8
Author: a filly ate it
Author URI: http://afillyateit.com/yak-for-wordpress

    Copyright 2006-2015  a filly ate it (email : support 'at' afillyateit.com)

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
define('YAK_VERSION_NUMBER', '3.4.8');

require_once('yak-model.php');
require_once('yak-utils.php');
require_once('yak-static.php');
require_once('yak-creditcard.php');
require_once('yak-order-widget.php');
require_once('yak-shorttags.php');
require_once('yak-admin.php');
require_once('yak-paypal.php');

require_once('yak-currencies.php');
require_once('yak-payment-utils.php');
require_once('yak-payment-redirect.php');

if (in_array($_REQUEST['page_id'], yak_get_option(NO_CACHE_PAGES, array()))) {
    ini_set('session.cache_limiter', 'private');
}

// more memory required
if (yak_return_bytes(ini_get('memory_limit')) < 15728640) {
    ini_set("memory_limit", "15M");
}

if (file_exists(ABSPATH . 'wp-includes/pluggable.php')) {
    require_once(ABSPATH . 'wp-includes/pluggable.php');
}
else {
    require_once(ABSPATH . 'wp-includes/pluggable-functions.php');
}


if (!function_exists('yak_register_plugin_links')) {
    function yak_register_plugin_links($links, $file) {
        if (yak_str_contains($file, 'yak-for-wordpress')) {
            $links[] = '<a href="admin.php?page=yak-general-options">' . __('Settings') . '</a>';
            $links[] = '<a href="http://wordpress.org/extend/plugins/yak-for-wordpress/installation/" target="_BLANK">' . __('Installation', 'yak-admin') . '</a>';
            $links[] = '<a href="http://wordpress.org/extend/plugins/yak-for-wordpress/faq/" target="_BLANK">' . __('FAQ', 'yak-admin') . '</a>';
            $links[] = '<a href="http://afillyateit.com/yak-for-wordpress/handbook" target="_BLANK">' . __('Handbook', 'yak-admin') . '</a>';
        }
        return $links;
    }
}


if (!function_exists('yak_calc_price')) {
    /**
     * Calculate price of a product taking into account any automatic discount
     */
    function yak_calc_price($id, $cat_id = null, $custom_price = null) {
        $discount = yak_get_option(AUTO_DISCOUNT, 1);
        $price_rounding = yak_get_option(PRICE_ROUNDING, 0);
        $prod = yak_get_product($id);
        if (!empty($prod->discount_override)) {
            $discount = $prod->discount_override;
        }

        if ($prod->custom_price && !empty($custom_price)) {
            $price = $custom_price;
        }
        else {
            $price = $prod->price;
        }
        
        if ($cat_id != null) {
            $prod_type = yak_get_product_type($id, $cat_id);
            $override_price = $prod_type->override_price;
            if (!empty($override_price)) {
                $price = $override_price;
            }
        }

        $rtn = number_format(0.0 + ($price * $discount), $price_rounding, '.', '');
        
        return $rtn;
    }
}


if (!function_exists('yak_calc_shipping')) {
    /**
     * calculate the shipping cost by weight and country
     */
    function yak_calc_shipping($total_weight, $total_items, $shipping_country, $selected_shipping_option) {
        if (empty($selected_shipping_option)) {
            return 0.0;
        }
        
        $zone = yak_get_option('yak_' . $shipping_country . '_zone');
        $selected_shipping_option = str_replace(' ', '', trim($selected_shipping_option));

        $shipping = yak_get_option(yak_get_shipping_varname($selected_shipping_option, $zone, 'fixed'), '');
        $shipping_fixed_first = yak_get_option(yak_get_shipping_varname($selected_shipping_option, $zone, 'fixeditemfirst'), 0);
        $shipping_fixed = yak_get_option(yak_get_shipping_varname($selected_shipping_option, $zone, 'fixeditem'), '');
        $shipping_weight_first = yak_get_option(yak_get_shipping_varname($selected_shipping_option, $zone, 'weightfirst'), '');
        $shipping_weight = yak_get_option(yak_get_shipping_varname($selected_shipping_option, $zone, 'weight'), '');
        
        $act_shipping_cost = yak_calc_shipping_internal($total_items, $total_weight, $shipping, $shipping_fixed, $shipping_fixed_first, $shipping_weight, $shipping_weight_first);
        if ($act_shipping_cost == null) {
            $act_shipping_cost = 0.0;
        }
        
        return $act_shipping_cost;
    }
}


if (!function_exists('yak_calc_shipping_internal')) {
    /**
     * The 'internals' of shipping calculation
     *
     * @param total_items the total number of items
     * @param total_weight the total weight of all items
     * @param shipping the default shipping value (returned first if present)
     * @param shipping_fixed fixed shipping value (per item calc)
     * @param shipping_fixed_first fixed shipping value for the first item
     * @param shipping_weight weight-based shipping value (calculated by weight)
     * @param shipping_weight_first weight-based shipping value for the first 100gms
     */
    function yak_calc_shipping_internal($total_items, $total_weight, $shipping, $shipping_fixed, $shipping_fixed_first, $shipping_weight, $shipping_weight_first) {
        if (!empty($shipping)) {
            return $shipping;
        }
        else if (!empty($shipping_fixed)) {
            $act_shipping_cost = 0.0;
            if ($total_items >= 1) {
                $act_shipping_cost += $shipping_fixed_first;
            }
            if ($total_items > 1) {
                $act_shipping_cost += (($total_items - 1) * $shipping_fixed);
            }
            return $act_shipping_cost;
        }
        else if (!empty($shipping_weight) && $total_weight > 0) {
            $weight_calc = yak_get_option(SHIPPING_WEIGHT_CALC, DEFAULT_SHIPPING_WEIGHT_CALC);
            $act_shipping_cost = 0.0;
            if (!empty($shipping_weight_first)) {
                $act_shipping_cost += $shipping_weight_first;
                $total_weight -= $weight_calc;
            }
            if ($total_weight > 0) {
                $act_shipping_cost += ($shipping_weight * ceil($total_weight / $weight_calc));
            }
            return $act_shipping_cost;
        }
        else {
            return null;
        }
    }
}


if (!function_exists('yak_checkout_error')) {
    /**
     * Handle an error in the checkout process.
     *
     * @param $model the array containing content
     * @param $prompt initial error message
     * @param $errors an array of error messages
     */    
    function yak_checkout_error(&$model, $prompt, $errors) {
        if (sizeof($errors) > 0) {
            $model['error_message'] = "<p>$prompt</p><ul>";
                
            foreach ($errors as $err) {
                $model['error_message'] = $model['error_message'] . "<li>$err</li>"; 
            }
                
            $model['error_message'] = $model['error_message'] . '</ul>';
            
            return true;
        }
        else {
            return false;
        }
    }
}


/**
 * If all funds have been received for an order, and there is downloadable content,
 * send an email containing the link for the dl.
 *
 * @param $order_id the id of the order to check
 */
if (!function_exists('yak_check_order')) {
    function yak_check_order($order_id) {
        global $wpdb, $order_table, $order_dl_table, $order_detail_table, $product_detail_table;
        
        $orders = yak_get_orders(null, null, null, null, false, true, true, $order_id);
        $order = $orders[0];
        
        $sql = $wpdb->prepare("select count(*) as total 
                              from $order_dl_table 
                              where order_id = %d
                              and uid is not null", $order_id);
        $dl = $wpdb->get_row($sql);
        
        // final update status
        $new_status = null;
        
        $order_num = $order->order_num;
        $rounding = yak_get_option(PRICE_ROUNDING, 2);
        $funds_received = round($order->funds_received, $rounding);
        $total_cost = round($order->total + $order->shipping_cost, $rounding);

        if ($funds_received >= $total_cost) {
            require_once('yak-payment-utils.php');
            $payment_type = yak_get_payment($order->payment_type);

            if ($payment_type != null) {
                do_action('yak-finalise-order-' . $payment_type, $order_id);
            }
            
            $shipping_addr = $order->get_shipping_address();

            // hook for 3rd party integration, affiliates processing, etc
            $email = $shipping_addr->email;
            $recipient = $shipping_addr->recipient;
            $country = $shipping_addr->country;
            $actual_cost = $order->total + yak_default($order->shipping_discount, 0) + yak_default($order->price_discount, 0);
            
            $order_items = array();
            foreach ($order->items as $item) {
                $order_items[] = array('id' => $item->id, 'name' => $item->itemname, 'price' => $item->price, 'quantity' => $item->quantity);
            }
            
            if (defined(YAK_DEBUG)) {
                yak_log("Triggering yak-order action for $order_id");
            }
            
            do_action('yak-order', array('order_id' => $order_id, 'email' => $email, 'recipient' => $recipient, 
                            'total_cost' => $order->total, 'shipping_cost' => $order->shipping_cost, 
                            'actual_cost' => $actual_cost, 'country' => $country, 'user_id' => $order->user_id,
                            'items' => $order_items));
        
            if ($dl->total < 1) {
                $msg = yak_get_option(DOWNLOAD_EMAIL, '');
                $email = $shipping_addr->email;
                
                yak_send_dl_email($order_id, $email, __('Your purchased downloads', 'yak'), $msg);
            }
                            
            // how many items in this order are not downloadable (i.e. physical product)
            $sql = $wpdb->prepare("select count(*) as non_dl_count 
                                   from $order_detail_table od, $product_detail_table pd 
                                   where od.id = %d
                                   and od.post_id = pd.post_id 
                                   and od.cat_id = pd.cat_id 
                                   and (pd.dl_file is null or pd.dl_file = '')", $order_id);
            $row = $wpdb->get_row($sql);

            if ($row->non_dl_count < 1) {
                $new_status = STOCK_SENT;
            }
            else if (empty($order->status)) {
                $new_status = PAYMENT_PROCESSED;
            }
            
            if ($new_status != null) {
                $sql = $wpdb->prepare("update $order_table set status = %s
                                       where id = %d", $new_status, $order_id);
                $wpdb->query($sql);
            }
        }
    }
}


if (!function_exists('yak_send_dl_email')) {
    function yak_send_dl_email($order_id, $recipient_email, $subject, $msg, $dryrun=false) {
        global $wpdb, $order_dl_table;
        
        $sql = $wpdb->prepare("select * 
                               from $order_dl_table 
                               where order_id = %d
                               and uid is null", $order_id);
        $results = $wpdb->get_results($sql); 
    
        $dl_uri = yak_get_option(DOWNLOAD_URI, yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/yak-dl.php');
    
        if (empty($msg)) {
            yak_log("WARNING: No message set for the download email.  Unable to send");
            return;
        }
        
        $uris = '';
        if ($results) {
            foreach ($results as $result) {
                $id = $result->id;
                $uid = md5(uniqid(rand(), true));
            
                if (!$dryrun) {
                    $sql = $wpdb->prepare("update $order_dl_table set uid = %s
                                           where id = %d", $uid, $id);
                    $wpdb->query($sql);
                }
            
                $uris = $uris . "$dl_uri?uid=$uid\n"; 
            }
        
            // send email with download links
            if ($uris != '') {
                $fromEmail = yak_get_option(DOWNLOAD_EMAIL_ADDRESS, '');
            
                if (yak_str_contains($msg, '<html')) {
                    $uris = str_replace("\n", "<br />", $uris);
                }

                $mail = str_replace('[downloads]', $uris, $msg);
                if (!$dryrun) {
                    yak_sendmail($fromEmail, $recipient_email, $subject, $mail); 
            
                    $mail = __('The following email has been sent to', 'yak') . ' ' . $recipient_email . " :\r\n" . $mail;
                    yak_sendmail($fromEmail, $fromEmail, $subject, $mail);
            
                    yak_insert_orderlog($order_id, "Sent download email to email address: $recipient_email");
                    yak_log("Sent download email for order $order_num");
                
                    if (defined('YAK_DEBUG')) {
                        yak_log("Mail message: $mail");
                    }
                }
            }   
        }
    }
}


if (!function_exists('yak_cleanup_after_order')) {
    /**
     * Remove any order data from the session.
     */
    function yak_cleanup_after_order() {
        yak_log('Triggering cleanup');
        if (isset($_SESSION['order_id'])) {
            unset($_SESSION['order_id']);
            unset($_SESSION[ITEMS_NAME]);
            unset($_SESSION['cc']);
            unset($_SESSION['accrecv']);
            unset($_SESSION['promo_code']);
            unset($_SESSION['current_order_items']);
            unset($_SESSION['current_order_value']);
            unset($_SESSION['order_id']);
        }
    }
}


if (!function_exists('yak_contains_product')) {
    function yak_contains_product($items, $product_ids) {
        foreach ($items as $item) {
            foreach ($product_ids as $id) {
                if ($item->id == $id) {
                    return true;
                }
            }
        }
        return false;
    }
}


if (!function_exists('yak_editproduct')) {
    /**
     * Display the product edit tab when editing a post/page.
     */
    function yak_editproduct() {
        include 'yak-view-edit-product.php';
    }
}


if (!function_exists('yak_get_shipping_options')) {
    function yak_get_shipping_options($payment_type = null) {
        $names = split("\n", yak_get_option(SHIPPING_OPTION_NAMES, ''));
        $pairs = yak_get_option(PAYMENT_SHIPPING_PAIRS, null);        
        $shipping_options = array();
        foreach ($names as $name) {
            $opt = trim($name);
            if ($payment_type == null || $pairs == null || !array_key_exists($payment_type, $pairs) || in_array($opt, $pairs[$payment_type])) {
		        $shipping_options[$opt] = $opt;
			}
        }
        return $shipping_options;
    }
}


if (!function_exists('yak_gen_order_num')) {
    /**
     * Generate an order number if the GENERATED option is configured -- otherwise use
     * the internal order id.
     */
    function yak_gen_order_num($order_id) {
        global $wpdb, $order_table;
        
        if (yak_get_option(ORDER_NUMBER_TYPE, GENERATED) == GENERATED) {
    		$yr = date('Y') - 1970;
    		$dy = (date('m') * 31) + date('d');
    		$r1 = str_pad(rand(0, 99999), 5, '0');
    		$r2 = str_pad(rand(0, 99999), 5, '0');
    		$num = $yr . str_pad($dy, 3, '0') . '-' . $r1 . '-' . $r2;

            $sql = $wpdb->prepare("select count(*) as total 
                                   from $order_table 
                                   where order_num = %s", $num);
            $row = $wpdb->get_row($sql);
            if ($row->total > 0) {
                return yak_gen_order_num();   
            }
            else {
                return $num;   
            }
        }
        else {
            return $order_id;
        }
    }
}


if (!function_exists('yak_get_order_num')) {
    /**
     * Return an order number for an order by its internal order id.
     */
    function yak_get_order_num($order_id) {
        global $wpdb, $order_table;
        
        $sql = $wpdb->prepare("select order_num from $order_table where id = %d", $order_id);
        $row = $wpdb->get_row($sql);
        
        return $row->order_num;
    }
}


if (!function_exists('yak_order_value')) {
    /**
     * Return or output the value of an order in the session.
     *
     * @param $echo echo the link to the page or return it
     * @param $order_id the id of the order (if null then use the session value)
     */
    function yak_order_value($echo = true, $order_id = null) {
        global $wpdb, $order_table, $order_detail_table;
        
        if ($order_id == null) {
            $rtn = $_SESSION['order_value'];
        }
        else {
            $sql = $wpdb->prepare("select shipping_cost + sum(od.price * od.quantity) as total
                                   from $order_table o, $order_detail_table od
                                   where o.id = %d and od.id = o.id", $order_id);
            $row = $wpdb->get_row($sql);
            $rtn = $row->total;
        }
        
        if ($echo == true) {
            echo $rtn;   
        }
        
        return $rtn;
    }
}


if (!function_exists('yak_get_referrer')) {
    /**
     * Find the referrer of the page (used so that we can include a 'Return to' link)
     */
    function yak_get_referrer() {
        $check_referrer = $_POST['check_referrer'];
        if (isset($check_referrer) && $check_referrer == 'true' && isset($_SERVER['HTTP_REFERER'])) {
            $referrer = $_SERVER['HTTP_REFERER'];
            $response = yak_do_http($referrer, '', '', null, 'GET');
            $title = yak_get_tag_value($response, '<title>', '</title>');
            return '<a href="' . $referrer . '">' . __('Return to ', 'yak') . $title . '</a>';
        }
        else {
            return null;
        }
    }
}


if (!function_exists('yak_get_quantity')) {
    /**
     * Return the quantity available for a particular product.
     *
     * @param $post_id the post to lookup
     * @param $type the type of post (default to the value 'default')
     */
    function yak_get_quantity($post_id, $type = null) {
        global $wpdb, $product_detail_table;
        
        $qty = 0;
        if ($type != null) {
            $prod_type = yak_get_product_type($post_id, null, $type);
            
            if (isset($prod_type)) {
                $qty = 0 + $prod_type->qty;
            }
        }
        else {
            $row = $wpdb->get_row("select sum(quantity) as quantity
                                    from $product_detail_table
                                    where post_id = $post_id");
            if (!empty($row->quantity)) {
                $qty = $row->quantity;
            }
        }
        return $qty;
    }
}


if (!function_exists('yak_get_sku')) {
    /**
     * Return the SKU of an order. If no SKU is present then create one using the post id and category id.
     */
    function yak_get_sku($post_id, $cat_id, $sku) {
        if (!empty($sku)) {
            return $sku;
        }
        else {
            $num = str_pad($post_id, 7, '0', STR_PAD_LEFT) . str_pad($cat_id, 4, '0', STR_PAD_LEFT);
            $numarr = str_split($num);
            
            // check digit calc (taken from wikipedia).  Assume a number: 03600029145x
            // add the odd-numbered digits (0 + 6 + 0 + 2 + 1 + 5 = 14),
            // multiply by three (14 × 3 = 42),
            // add the even-numbered digits (42 + (3 + 0 + 0 + 9 + 4) = 58),
            // calculate modulo ten (58 mod 10 = 8),
            // subtract from ten (10 − 8 = 2).
            
            $check = 0;
            for ($x = 1; $x < count($numarr); $x += 2) {
                $check += $numarr[$x];
            }
            
            $check *= 3;
            
            for ($x = 0; $x < count($numarr); $x += 2) {
                $check += $numarr[$x];
            }
            
            $check = 10 - ($check % 10);
            
            return $num . $check;
        }
    }
}


if (!function_exists('yak_get_orders')) {
    /**
     * Return orders - effectively an order search.
     *
     * @param $status the status of the orders to return
     * @param $order_num return an order by its number (optional)
     * @param $year the year the order was made
     * @param $month the month the order was made
     * @param $include_log include the order log for each order
     * @param $include_meta include the metadata with each order
     * @param $include_details include the order details (items)
     * @param $order_id return an order by its internal id
     * @param $user_id return orders made by a specific user
     * @param $payment_type return orders made with a specific type
     * @param $search use the search term to find orders
     */
    function yak_get_orders($status, $order_num = null, $year = null, $month = null, $include_log = true, $include_meta = true, $include_details = true, 
            $order_id = null, $user_id = null, $payment_type = null, $search = null) {
        global $wpdb, $order_table, $order_detail_table, $order_log_table, $order_meta_table, $product_detail_table, $address_table;
        
        $posts_table =& $wpdb->posts;
        
        $args = array();
        
        $sql = "select o.id, o.user_id, o.order_num, o.time, o.shipping_address_id, o.billing_address_id, o.address, o.country_code, 
                       o.billing_address, o.billing_country_code as old_billing_country_code, o.payment_type, o.funds_received, o.shipping_cost,
                       sum(od.price * od.quantity) as total, o.status, o.price_discount, o.shipping_discount, o.selected_shipping_type,
                       a1.recipient as shipping_recipient, a1.company_name as shipping_company_name, 
                       a1.email_address as shipping_email_address, a1.phone_number as shipping_phone_number, 
                       a1.address_line1 as shipping_address_line1, a1.address_line2 as shipping_address_line2, 
                       a1.suburb as shipping_suburb, a1.city as shipping_city, a1.region as shipping_region, 
                       a1.state as shipping_state, a1.country_code as shipping_country_code, a1.postcode as shipping_postcode,
                       a2.recipient as billing_recipient, a2.company_name as billing_company_name, 
                       a2.email_address as billing_email_address, a2.phone_number as billing_phone_number,
                       a2.address_line1 as billing_address_line1, a2.address_line2 as billing_address_line2, 
                       a2.suburb as billing_suburb, a2.city as billing_city, a2.region as billing_region, 
                       a2.state as billing_state, a2.country_code as billing_country_code,
                       a2.postcode as billing_postcode
                from $order_table o 
                       left outer join $address_table a1 on o.shipping_address_id = a1.id 
                       left outer join $address_table a2 on o.billing_address_id = a2.id, $order_detail_table od 
                where o.id = od.id 
                and (o.deleted is null or o.deleted != 1) ";

        if ($status === '') {
            $sql .= "and (status is null or status = '') ";
        }
        else if ($status === PAYMENT_PROCESSED) {
            $sql .= " and (status = %s or status = %s) ";
            $args[] = PAYMENT_PROCESSED;
            $args[] = STOCK_SENT;
        }
        else if ($status != null && $status != '%') {
            $sql .= "and status = %s ";
            $args[] = $status;
        }
                
        if (!empty($year)) {
            $sql .= "and year(o.time) = %s ";
            $args[] = $year;
        }
        
        if (!empty($month) && $month != '00') {
            $sql .= "and month(o.time) = %s ";
            $args[] = $month;
        }
        
        if (!empty($order_num)) {
            $sql .= "and o.order_num = %s ";
            $args[] = $order_num;
        }
        
        if (!empty($order_id)) {
            $sql .= "and o.id = %d ";
            $args[] = $order_id;
        }
        
        if ($user_id != null) {
            $sql .= "and o.user_id = %d ";
            $args[] = $user_id;
        }
        
        if (!empty($search)) {
            $sql .= 'and (';
            $first = true;
            foreach (explode(' ', $search) as $term) {
                if ($first) {
                    $first = false;
                }
                else {
                    $sql .= ' or ';
                }
                
                if (is_numeric($term)) {
                    $sql .= "exists (select 1 from $order_detail_table od 
                                         where od.post_id = %d 
                                         and od.id = o.id) ";
                    $args[] = $term;
                }
                else {
                    $like_term = '%' . $term . '%';
                    
                    $sql .= "o.order_num = %s ";
                    $args[] = $term;
                    
                    $sql .= "or exists (select 1 from $order_detail_table od, $posts_table p
                                         where od.id = o.id
                                         and p.post_title like %s
                                         and p.ID = od.post_id
                                         and p.post_status != 'inherit') ";
                    $args[] = $like_term;
                    
                    $sql .= "or (a1.recipient like %s or a2.recipient like %s or a1.email_address like %s or a2.email_address like %s) ";
                    $args[] = $like_term;
                    $args[] = $like_term;
                    $args[] = $like_term;
                    $args[] = $like_term;
                    
                    $sql .= "or exists (select 1 from $wpdb->term_relationships tr, $wpdb->term_taxonomy tt, $wpdb->terms t
                                        where tr.object_id = od.post_id
                                        and tr.term_taxonomy_id = tt.term_taxonomy_id
                                        and tt.taxonomy = 'post_tag'
                                        and tt.term_id = t.term_id
                                        and t.slug = %s) ";
                    $args[] = strtolower($term);
                    
                    $sql .= "or exists (select 1 from $order_meta_table om
                                        where om.value like %s
                                        and om.order_id = o.id) ";
                    $args[] = $like_term;
                }
            }
            
            $sql .= ') ';
        }
                
        if (!empty($payment_type)) {
            $sql .= "and (o.payment_type = %s) ";
            $args[] = $payment_type;
        }
        
        $sql .= "group by o.id
                 order by o.id desc";
                 
        // query for orders and order detail (summary data)
        $sql = $wpdb->prepare($sql, $args);
        
        if (defined('YAK_DEBUG')) {
            yak_log("SQL: " . $sql);
        }
        
        $results = $wpdb->get_results($sql);
        
        // loop through the results and build more detail
        $orders = array();
        if ($results) {
            foreach ($results as $result) {
                $order_id = $result->id;
                
                $order = new YakOrder($order_id, $result->order_num, $result->time, $result->payment_type, 
                                      $result->funds_received, $result->shipping_cost, $result->total, $result->status, $result->selected_shipping_type);
                $order->address = $result->address;
                $order->country_code = $result->country_code;
                $order->billing_address = $result->billing_address;
                $order->billing_country_code = $result->old_billing_country_code;
                $order->user_id = $result->user_id;
                
                if (($result->shipping_address_id != null && $result->shipping_address_id > 0) 
                    || ($result->billing_address_id != null && $result->billing_address_id > 0)) {
                    foreach (array('shipping', 'billing') as $type) {
                        $res = get_object_vars($result);
                        $addr = new YakAddress($res[$type . '_email_address'],
                                               $res[$type . '_recipient'],
                                               $res[$type . '_company_name'],
                                               $res[$type . '_phone_number'],
                                               $res[$type . '_address_line1'],
                                               $res[$type . '_address_line2'],
                                               $res[$type . '_suburb'],
                                               $res[$type . '_city'],
                                               $res[$type . '_region'],
                                               $res[$type . '_state'],
                                               $res[$type . '_postcode'],
                                               $res[$type . '_country_code'],
                                               $type);
                        if ($type == 'shipping') {
                            $order->shipping_addr = $addr;
                        }
                        else {
                            $order->billing_addr = $addr;
                        }
                    }
                }
                
                if ($include_details) {
                    $sql = $wpdb->prepare("select od.post_id as id, od.itemname, od.price, od.quantity, (od.price * od.quantity) as total,
                                                od.post_id, od.cat_id, pd.sku, (select count(*) 
                                                 from $order_meta_table m 
                                                 where m.order_id = od.id 
                                                 and m.name like concat(od.itemname, '%%')) as multi_select_count
                                           from $order_detail_table od 
                                                left outer join $product_detail_table pd 
                                                    on od.post_id = pd.post_id and od.cat_id = pd.cat_id
                                           where id = %d", $order_id);
                    $order->items = $wpdb->get_results($sql);
                    
                    if ($include_meta) {
                        foreach ($order->items as $item) {
                            $sql = $wpdb->prepare("select name, value 
                                                   from $order_meta_table
                                                   where order_id = %d 
                                                   and post_id = %d
                                                   and cat_id = %d
                                                   order by id", $order_id, $item->id, $item->cat_id);
                            $item->meta = array();
                            $results = $wpdb->get_results($sql);
                            if (count($results) > 0) {
                                foreach ($results as $result) {
                                    $item->meta[$result->name] = $result->value;
                                }
                            }
                        }
                    }
                }
                
                if ($include_log) {
                    $sql = $wpdb->prepare("select time, message 
                                           from $order_log_table
                                           where order_id = %d", $order_id);
                    $order->log = $wpdb->get_results($sql);
                }
  
                if ($include_meta) {
                    $sql = $wpdb->prepare("select name, value 
                                           from $order_meta_table
                                           where order_id = %d 
                                           and post_id is null
                                           order by id", $order_id);
                    $results = $wpdb->get_results($sql);
                    $meta = array();
                    foreach ($results as $result) {
                        $meta[$result->name] = $result->value;
                    }
                    $order->meta = $meta;
                }
                
                $orders[] = $order;
            }
        }
        
        return $orders;
    }
}


if (!function_exists('yak_get_order_details')) {
    function yak_get_order_details($post_id, $cat_id) {
        global $wpdb, $order_table, $address_table;

        $sql = $wpdb->prepare("select distinct a.email_address, max(o.id) as order_id
                            from $order_table o, $address_table a
                            where o.shipping_address_id = a.id
                            and o.status in ('STOCK SENT', 'PAYMENT PROCESSED')
                            and exists (select 1 
                            			from wp_yak_order_detail od 
                            			where od.post_id = %d
                            			and od.cat_id = %d
                            			and od.id = o.id)
                            group by a.email_address", $post_id, $cat_id);
        if (defined('YAK_DEBUG')) {
            yak_log("SQL: " . $sql);
        }
        return $wpdb->get_results($sql);
    }
}


if (!function_exists('yak_get_order_payment_types')) {
    /**
     * Return the order payment types from the order table.
     */
    function yak_get_order_payment_types() {
        global $wpdb, $order_table;
        
        $results = $wpdb->get_results("select distinct payment_type from $order_table");
        
        $types = array();
        $types[''] = '';
        if ($results) {
            foreach ($results as $result) {
                $types[$result->payment_type] = $result->payment_type;
            }
        }
        return $types;
    }
}


if (!function_exists('yak_get_post_param')) {
    /**
     * Get the name of the post parameter depending upon whether we're displaying a post
     * or a page.
     */
    function yak_get_post_param() {
        global $wp_query;
        
        if (!$wp_query->is_page) {
            // if it's not a return 'p'
            return 'p';
        }
        else {
            // otherwise we're on a page
            return 'page_id';   
        }
    }
}


if (!function_exists('yak_get_product_categories')) {
    /**
     * Return an array of product categories for a post.
     *
     * @param $post_id the post to lookup
     * @param $post_type the type of the post (from post->post_type)
     * @param $include_zero include categories with no available quantity (defaults to true)
     */
    function yak_get_product_categories($post_id, $post_type=null, $include_empty=true, $include_zero=false) {
        global $wpdb, $product_table, $product_detail_table;
        
        if (!isset($post_type) || $post_type == null) {
            $sql = $wpdb->prepare("select post_type 
                                   from $wpdb->posts p
                                   where p.ID = %d", $post_id);
            $row = $wpdb->get_row($sql);
            $post_type = $row->post_type;
        }
        
        $rtn = array();

        $prod_cat_name = yak_get_option(PRODUCT_CATEGORY_NAME, 'products');
        
        if ($include_zero) {
            $op = ">=";
        }
        else {
            $op = ">";
        }
        
        $args = array();
        if ($post_type != 'page') {
            $sql = "select t.term_id as cat_id, t.name as cat_name, t2.name as parent_name, p.sku, p.quantity, p.override_price, 
                            pt.discount_override, pt.price, p.weight, p.dl_file
                    from $wpdb->term_relationships tr,
                    $wpdb->term_taxonomy tt,
                    $wpdb->terms t
                    left join $product_detail_table p on p.cat_id = t.term_id and p.post_id = %d
                    left join $product_table pt on pt.post_id = p.post_id,
                    $wpdb->terms t2
                    where tr.object_id = %d
                    and tr.term_taxonomy_id = tt.term_taxonomy_id
                    and tt.taxonomy = 'category'
                    and tt.term_id = t.term_id
                    and tt.parent = t2.term_id
                    and t2.slug = %s";
                    
            $args[] = $post_id;
            $args[] = $post_id;
            $args[] = $prod_cat_name;
                
            if ($include_empty) {
                $sql .= " and (p.quantity is null or p.quantity $op 0)";
            }
            else {
                $sql .= " and p.quantity $op 0";
            }
            
            $sql = $wpdb->prepare($sql, $args);
            $results = $wpdb->get_results($sql);
        }
        else {
            $sql = "select p.cat_id, 'default' as cat_name, '' as parent_name, p.sku, p.quantity, p.override_price, pt.discount_override,
                        pt.price, p.weight, p.dl_file
                    from $product_detail_table p, $product_table pt
                    where p.post_id = %d
                    and pt.post_id = p.post_id
                    and p.cat_id = -1";
            $args[] = $post_id;
            
            if ($include_empty == true) {
                $sql .= " and (p.quantity is null or p.quantity $op 0)";
            }
            else {
                $sql .= " and p.quantity $op 0";
            }
                    
            $sql = $wpdb->prepare($sql, $args);
            $results = $wpdb->get_results($sql); 

            if (sizeof($results) == 0 && $include_zero) {
                $results = $wpdb->get_results("select -1 as cat_id, 'default' as cat_name, '' as parent_name, 
                                               '' as sku, null as override_price, 0 as quantity, '' as weight, '' as dl_file");
            }
        }
                
        if (sizeof($results) > 0) {
            foreach ($results as $row) {
                if (!empty($row->parent_name) && strtolower($row->parent_name) != strtolower($prod_cat_name)) {
                    $name = $row->parent_name . ' ' . $row->cat_name;
                }
                else {
                    $name = $row->cat_name;
                }
                $pt = new YakProductType($post_id, $row->cat_id, $name, $row->sku, $row->quantity, $row->override_price, 
                                $row->weight, $row->dl_file);
                $pt->discount_override = $row->discount_override;
                $rtn[] = $pt;
            }
        }
        
        return $rtn;
    }
}


if (!function_exists('yak_load_product')) {
    /**
     * Create a Product object with the specified row data
     *
     * @param $row a table row containing product data
     */
    function yak_load_product($row) {
        $title = yak_fix_escaping($row->alt_title);
        $pp = new YakProduct($row->id, $row->post_title, $row->post_type, $title, $row->price);
        $pp->discount_override = $row->discount_override;
        $pp->multi_select_options = $row->multi_select_options;
        $pp->multi_select_min = $row->multi_select_min;
        $pp->multi_select_max = $row->multi_select_max;
        $pp->multi_select_cols = $row->multi_select_cols;
        $pp->require_login = ($row->require_login == 1);
        $pp->description = $row->description;
        if ($row->types > 0) {
            $pp->num_types = $row->types;
        }
        $pp->custom_price = ($row->custom_price == 1);
        
        return $pp;
    }
}


if (!function_exists('yak_get_product')) {
    /**
     * Return a product by id, or by using the WP 'loop' post, or overridden with a global variable $yak_post
     *
     * @param $post_id the id of the post
     * @param $use_cache cache the product in the $GLOBALS array, or use a previous cached value if present.
     */
    function yak_get_product($post_id = null, $use_cache = false) {
        global $wpdb, $post, $product_table, $yak_post;
        
        if (empty($post_id)) {
            // yak_post can be used to override `the_loop` post
            if (isset($yak_post)) {
                $pp = $yak_post;
                $post_id = $pp->ID;
            }
            else {
                $pp = $post;
                $post_id = $pp->ID;
            }
        }
        
        $cached_prod = $GLOBALS['product-' . $post_id];
        if ($use_cache && $cached_prod != null) {
            return $cached_prod;
        }
        else {
            $sql = $wpdb->prepare("select p.ID as id, p.post_title, p.post_type, pt.description, pt.alt_title, pt.price, p.post_content, pt.require_login,
                                        pt.discount_override, pt.multi_select_options, pt.multi_select_min, pt.multi_select_max, pt.multi_select_cols, 0 as types,
                                        pt.custom_price
                                   from $wpdb->posts p left join $product_table pt on p.id = pt.post_id 
                                   where p.id = %d", $post_id);
            $row = $wpdb->get_row($sql);
        
            $prod = yak_load_product($row);
            if ($use_cache) {
                $GLOBALS['product-' . $post_id] = $prod;
            }
            
            return $prod;
        }
    }
}


if (!function_exists('yak_get_products')) {
    /**
     * Return an array of products
     *
     * @param $order if 'title' order the array by title, otherwise order by post date
     * @param $offset the starting position for the query
     * @param $number the number of products (from the starting position) to return
     */
    function yak_get_products($orderby='title', $order='asc', $tag=null, $offset=0, $number=99999999) {
        global $wpdb, $product_table, $product_detail_table;
        
        $prod_cat_name = yak_get_option(PRODUCT_CATEGORY_NAME, 'products');
        
        $products = array();

        $sql = "select distinct p.ID as id, p.ID, p.post_title, p.post_type, p.post_date, pt.description, pt.price, pt.alt_title, 
                            p.post_content, pt.multi_select_options, pt.multi_select_max, pt.multi_select_cols, pt.require_login, 
                            count(tr.object_id) as types, pt.custom_price
                from $wpdb->posts p, $product_table pt, $wpdb->term_relationships tr,
                     $wpdb->term_taxonomy tt, $wpdb->terms t, $wpdb->terms t2
                where p.post_type = 'post'
                and p.post_status = 'publish'
                and p.id = pt.post_id
                and tr.object_id = p.id
                and tr.term_taxonomy_id = tt.term_taxonomy_id
                and tt.taxonomy = 'category'
                and tt.term_id = t.term_id
                and tt.parent = t2.term_id
                and t2.slug = '$prod_cat_name'";
                
        $sql2 = " union
            select distinct p.ID as id, p.ID, p.post_title, p.post_type, p.post_date, pt.description, pt.price, pt.alt_title, p.post_content, 
                    pt.multi_select_options, pt.multi_select_max, pt.multi_select_cols, pt.require_login, 1 as types, pt.custom_price
            from $wpdb->posts p, $product_table pt
            where p.post_type = 'page'
            and p.id = pt.post_id 
            and p.post_status = 'publish' ";
        
        if ($tag != null) {
            $tags = explode(',', $tag);
            $count = count($tags);
            for ($x = 0; $x < $count; $x++) {
                $tags[$x] = "'" . trim($tags[$x]) . "'";
            }
            $in_tag = implode(',', $tags);
            
            $sql .= " and exists (select 1 
                                 from $wpdb->terms t3, $wpdb->term_taxonomy tt3, $wpdb->term_relationships tr3
                                 where t3.slug in ($in_tag)
                				 and tt3.term_id = t3.term_id
                				 and tt3.term_taxonomy_id = tr3.term_taxonomy_id
                				 and tr3.object_id = pt.post_id)";
                	
            // no page based products should be included if a tag is included			 
            $sql2 = "";
        }
        
        $sql .= " group by id, post_title, post_type, description, price, alt_title, post_content, multi_select_options, multi_select_max, multi_select_cols, require_login";
        
        $sql .= $sql2;
        
        if ($orderby == 'title') {
            $sql .= " order by post_title";
        }
        else {
            $sql .= " order by post_date";
        }
        
        if ($order == 'asc') {
            $sql .= " asc";
        }
        else {
            $sql .= " desc";
        }
        
        $sql .= " limit $offset, $number";
        
        if (defined('YAK_DEBUG')) {
            yak_log("SQL: " . $sql);
        }
        
        $ar = $wpdb->get_results($sql);
        
        foreach ($ar as $post) {
            $title = yak_fix_escaping($post->alt_title);
            
            $product = yak_load_product($post);
            $product->content = $post;
            
            $products[] = $product;
        }
        
        return $products;
    }
}
    
 
if (!function_exists('yak_get_product_count')) {
    /**
     * Return a count of products.
     */
    function yak_get_product_count() {
        global $wpdb, $product_table;
        
        $row = $wpdb->get_row("select count(distinct p.id) as total
                               from $wpdb->posts p, $product_table pt
                               where p.post_type in ('page', 'post')
                               and p.post_status = 'publish'
                               and p.id = pt.post_id");
        return $row->total;
    }
}

if (!function_exists('yak_get_product_type')) {
    /**
     * Return the type of a product as a YakProductType object.
     *
     * @param $post_id the id of the post
     * @param $cat_id the category (optional)
     * @param $type the name of the type (basically the category)
     */
    function yak_get_product_type($post_id, $cat_id = null, $type = null) {
        global $wpdb, $product_detail_table;
        
        $args = array();
        $sql = "select pd.post_id, pd.cat_id, ifnull(t.name, 'default') as name, pd.sku, pd.quantity, 
                                      pd.dl_file, pd.weight, pd.override_price
                               from $product_detail_table pd 
                                      left outer join $wpdb->terms t on t.term_id = pd.cat_id
                               where pd.post_id = %d ";
                               
        $args[] = $post_id;
                               
        if ($cat_id != null) {
            $sql .= "and pd.cat_id = %d";
            $args[] = $cat_id;
        }
        else {
            $sql .= "and exists (select 1 from $wpdb->terms t 
                                 where t.term_id = pd.cat_id and t.name = %s)";
            $args[] = $type;
        }
        
        $sql = $wpdb->prepare($sql, $args);
        $row = $wpdb->get_row($sql);
        return new YakProductType($post_id, $row->cat_id, $row->name, $row->sku, $row->quantity, $row->override_price, 
                $row->weight, $row->dl_file);
    }
}


if (!function_exists('yak_get_product_types')) {
    /**
     * Return an array of product types based on the main product category (defaults to 'products').
     *
     * @param $first_empty if true then the first item will be empty. Useful for dropdowns were the first item is blank.
     * @param $exclusions an array of types to exclude from the returned set.
     */
    function yak_get_product_types($first_empty=true, $exclusions = null) {
        global $wpdb;

        $prod_cat_name = yak_get_option(PRODUCT_CATEGORY_NAME, 'products');
        
        $sql = $wpdb->prepare("select t.term_id, t.name 
                               from $wpdb->terms t, $wpdb->term_taxonomy tt 
                               where t.term_id = tt.term_id 
                               and tt.parent = (select t2.term_id 
                                            from $wpdb->term_taxonomy tt2, $wpdb->terms t2 
                                            where tt2.term_id = t2.term_id and t2.slug = %s)", $prod_cat_name);
        $results = $wpdb->get_results($sql);
        $types = array();
        if ($first_empty) {
            $types[''] = '';
        }

        foreach ($results as $row) {
            if ($exclusions != null && yak_in_list($row->name, array_values($exclusions))) {
                continue;
            }
            $types["$row->term_id"] = $row->name;
        }
        return $types;
    }
}


if (!function_exists('yak_get_address')) {
    /**
     * Return the customer address from either the $_SESSION or the $_COOKIE or, if
     * neither of these exist, then return the last address we have recorded for this
     * customer (if they're logged in)
     *
     * @param $type the type of address (shipping or billing)
     * @param $for_user_id lookup a specific user's address (this is an override for the default behaviour)
     */
    function yak_get_address($type, $for_user_id = null) {
        global $wpdb, $user_ID, $order_table, $address_table;
        
        $uaddr = null;
        
        if ($for_user_id == null) {
            $session_addr = $_SESSION[$type . ADDRESS_COOKIE_SUFFIX];
            $cookie_addr = $_COOKIE[$type . ADDRESS_COOKIE_SUFFIX];
        
            $uaddr = null;
            if (isset($session_addr)) {
                $addr = $session_addr;
                $uaddr = unserialize($addr);
            }
            else if (isset($cookie_addr)) {
                $addr = stripslashes($cookie_addr);
                $uaddr = unserialize($addr);
            }
            else if (!empty($user_ID) && $user_ID > 0) {
                $for_user_id = $user_ID;
            }
        }
        
        if ($for_user_id != null) {
            $sql = "select * 
                    from $address_table a
                    where exists (select 1 
                                  from $order_table o 
                                  where o.user_id = $for_user_id 
                                  and o." . $type . "_address_id = a.id)
                    order by id desc
                    limit 1";
            if (defined('YAK_DEBUG')) {
                yak_log("SQL: $sql");
            }
            $res = $wpdb->get_row($sql);
            if (isset($res)) {
                $uaddr = new YakAddress($res->email_address,
                                       $res->recipient,
                                       $res->company_name,
                                       $res->phone_number,
                                       $res->address_line1,
                                       $res->address_line2,
                                       $res->suburb,
                                       $res->city,
                                       $res->region,
                                       $res->state,
                                       $res->postcode,
                                       $res->country_code,
                                       $type);
            }
        }
        
        return $uaddr;
    }
}


if (!function_exists('yak_get_product_weight')) {
    /**
     * Return the weight of a product and type (category)
     *
     * @param $post_id the product post
     * @param $cat_id the category describing the type of the product (small, med, large, etc)
     */
    function yak_get_product_weight($post_id, $cat_id) {
        global $wpdb, $product_detail_table;
        
        $sql = $wpdb->prepare("select weight from $product_detail_table 
                               where post_id = %d and cat_id = %d", $post_id, $cat_id);
        $row = $wpdb->get_row($sql);

        return $row->weight;        
    }
}


if (!function_exists('yak_get_title')) {
    /**
     * Return the title of a product.
     *
     * @param $post_id the product post
     * @param $cat_id the category describing the type of the product (small, med, large, etc)
     */    
    function yak_get_title($post_id, $cat_id) {
        global $wpdb, $product_table, $product_detail_table;
        
        $posts = $wpdb->posts;
        $terms = $wpdb->terms;
        
        $sql = $wpdb->prepare("select p.post_title, pt.alt_title, t1.name as cat_name  
                               from $posts p left join $product_table pt on p.ID = pt.post_id,
                               $product_detail_table pd left join $terms t1 on pd.cat_id = t1.term_id
                               where p.ID = %d
                               and p.ID = pd.post_id 
                               and pd.cat_id = %d", $post_id, $cat_id);
        $row = $wpdb->get_row($sql);
                
        if (!isset($row)) {
            return __('[DELETED PRODUCT]', 'yak');
        }
        else if (isset($row->alt_title) && $row->alt_title != null) {
            $title = __($row->alt_title, 'yak');
        }
        else {
            $title = __($row->post_title, 'yak');
        }
                   
        if (isset($row->cat_name) && strtolower($row->cat_name) != 'default') {
            $title = $title . ' (' . __($row->cat_name, 'yak') . ')';
        }

        return yak_fix_escaping($title);   
    }
}


if (!function_exists('yak_get_totals')) {
    /**
     * Return an array containing the total quantity and total price of an array of items.
     */
    function yak_get_totals(&$items) {
        $total_qty = 0;
        $total_price = 0;
        foreach ($items as $key=>$item) {
            if (!isset($item->price)) {
                $item->price = yak_calc_price($item->id, $item->cat_id, $item->price);
            }
            $total_qty += $item->quantity;
            $total_price += ($item->price * $item->quantity);
        }
        $totals->quantity = $total_qty;
        $totals->price = $total_price;
        return $totals;
    }
}


if (!function_exists('yak_init_resources')) {
    function yak_init_resources($include_admin_js = false) {
        $yakurl = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/resources';
        
        wp_enqueue_script('jquery');
        
        wp_register_style('yak-css', "$yakurl/ui.css", null, YAK_VERSION_NUMBER, 'screen');
        wp_enqueue_style('yak-css');

        wp_enqueue_script('yak-ui', "$yakurl/ui.js", null, YAK_VERSION_NUMBER);
        if ($include_admin_js) {
            wp_enqueue_script('yak-admin-ui', "$yakurl/ui-admin.js", null, YAK_VERSION_NUMBER);
            wp_enqueue_script('jquery-tools', "$yakurl/jquery.tools.min.js");
            
            wp_register_style('yak-admin-css', "$yakurl/ui-admin.css", null, YAK_VERSION_NUMBER, 'screen');
            wp_enqueue_style('yak-admin-css');
        }
    }
}


if (!function_exists('yak_init')) {
    /**
     * Initialisation functions (executed before the rest of the page is rendered)
     */
    function yak_init() {
        global $wpdb, $user_ID, $order_table, $order_dl_table, $order_meta_table, $order_detail_table, $product_detail_table, $address_table;
        
        load_plugin_textdomain('yak', 'wp-content/plugins/yak-for-wordpress/lang');
        
        yak_init_resources();
        
        wp_localize_script('yak-ui', 'yak_ui_l10n', 
            array('state_text' => __('State', 'yak') . '&nbsp;:',
                  'region_text' => __('Region', 'yak') . '&nbsp;:',
                  'ajax_button_adding' => __('Adding...', 'yak'),
                  'ajax_button_added' => __('Added', 'yak')));
                
        if (session_id() == "") {
            @session_start();
        }
        
        $action = $_POST['action'];
        if ($action == 'update' || $action == 'address') {
            if (!empty($_POST[PAYMENT_TYPE])) {
                $payment_types = yak_get_option(PAYMENT_TYPES, array());
                $i = 0;
                foreach ($payment_types as $key=>$val) {
                    if ($_POST[PAYMENT_TYPE] == $key) {
                        setcookie('selected_payment_type', "$i", time() + yak_get_option(COOKIE_LIFETIME, 2592000), '/');
                        break;
                    }
                    $i++;
                }
            }
        }
        
        // confirm2 is the second (final) confirmation screen
        if ($action == 'confirm2') {    
            $addr = yak_get_address('shipping');
            $baddr = yak_get_address('billing');
            
            $items = $_SESSION[ITEMS_NAME];
            $total_qty = 0;
            
            // if T&C's are enabled, check that the customer has agreed to them
            if (yak_get_option(TERMS_AND_CONDITIONS, '') != '' && $_POST['tandcConfirmation'] != 'on') {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = yak_get_option(TERMS_AND_CONDITIONS, '');
                return;
            }
            else if (!empty($_COOKIE[BLACKLISTED_COOKIE]) && $_COOKIE[BLACKLISTED_COOKIE] == BLACKLISTED_COOKIE) {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = __('Sorry, an unexpected error occurred while processing your order. Please get in contact to continue.', 'yak');
                return;
            }
            
            $validation_message = apply_filters('yak-order-validate', null);
            
            if ($validation_message != null) {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = $validation_message;
                return;
            }
            
            // check if we've got enough stock for the purchases
            $not_enough_stock = false;
            $total_weight = 0;         
            foreach ($items as $i => $item) {
                $sql = $wpdb->prepare("select quantity
                                       from $product_detail_table
                                       where post_id = %d
                                       and cat_id = %d", $item->id, $item->cat_id);
                $qty = $wpdb->get_var($sql);

                // handle unlimited quantity settings
                $unlimited_qty = yak_get_option(UNLIMITED_QUANTITY, 'off');
                if ($qty == null && $unlimited_qty == 'off') {
                    $qty = 0;
                }

                if ($qty != null && $qty < $item->quantity) {
                    $item->quantity = $qty;
                    if ($item->quantity < 1) {
                        unset($items[$i]);   
                    }
                    $not_enough_stock = true;
                }
                $total_qty = $total_qty + $item->quantity;
                $total_weight += $item->get_total_weight();
            }
            $items = array_values($items);
            
            if ($not_enough_stock) {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = __('We have a shortage of stock for one (or more) of your selections. The quantities in your basket have been altered accordingly.', 'yak');
                return;
            }
            else if ($total_qty < 1) {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = __('Sorry, but you do not have any items in your order.', 'yak');
                return;
            }

            // now write the order to the database
            $selected_shipping = yak_default($_POST[SELECTED_SHIPPING_OPTION], '');
            if (empty($selected_shipping)) {
                $shipping_options = yak_get_shipping_options();
                $selected_shipping = reset($shipping_options);
            }
            
            $cty = str_replace(' ', '_', $_POST[SHIPPING_COUNTRY]);
            $shipping_cost = yak_calc_shipping($total_weight, $total_qty, $cty, $selected_shipping);
            
            $shipping_address = $addr->as_string('country');
            $shipping_cc = $addr->country;
            $billing_address = $baddr->as_string('country');
            $billing_cc = $baddr->country;
            
            $addrs = array($addr, $baddr);
            foreach ($addrs as &$address) {
                $sql = $wpdb->prepare("insert into $address_table (address_type, email_address, recipient, company_name, phone_number, 
                                            address_line1, address_line2, suburb, city, state, region, country_code, postcode)
                                        values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                        $address->type, $address->email, $address->recipient, $address->company_name, $address->phone, 
                        $address->addr1, $address->addr2, $address->suburb, $address->city, $address->state, $address->region, 
                        $address->country, $address->postcode);      
                $wpdb->query($sql);
                $address->id = $wpdb->insert_id;
            }
            
            if (isset($user_ID)) {
                $user = $user_ID;
                update_user_meta($user_ID, 'yak_shipping_address', $addr->id);
                update_user_meta($user_ID, 'yak_billing_address', $baddr->id);
            }
            else {
                $user = 'null';
            }
            
            $sql = $wpdb->prepare("insert into $order_table (time, user_id, shipping_cost, payment_type, 
                                        status, selected_shipping_type, shipping_address_id, billing_address_id)
                                   values (current_timestamp(), %s, %s, %s, '', %s, %d, %d)",
                    $user, $shipping_cost, $_SESSION[PAYMENT_TYPE], $selected_shipping, $addr->id, $baddr->id);
            $wpdb->query($sql);
            $order_id = $wpdb->insert_id;
            $_SESSION['order_id'] = $order_id;
            
            // order number can either be a sequence or generated, so we need to
            // update after the initial insert
            $order_num = yak_gen_order_num($order_id);
            $sql = $wpdb->prepare("update $order_table set order_num = '$order_num' 
                                   where id = %d", $order_id);
            $wpdb->query($sql);
            
            $order_value = 0;
            $total_discount = 0;

            // import the promo utils, rather than by default
            require_once('yak-promo-utils.php');
            
            $totals = yak_get_totals($items);
            
            // get the promo, if a code is set
            if (isset($_SESSION['promo_code'])) {
                $code = $_SESSION['promo_code'];
                $promo = yak_get_promotion($code);
                
                if (yak_str_contains($promo->promo_type, 'coupon_codes')) {
                    yak_use_coupon($promo->code, $code);
                    yak_insert_ordermeta($order_id, "Coupon code", $code);
                }
            }
            else {
                $promo = yak_get_promotion_by_threshold($totals->price, $totals->quantity);
            }
  
            $low_stock_threshold = yak_get_option(LOW_STOCK_THRESHOLD, 0);
            $low_stock_email = yak_get_option(LOW_STOCK_EMAIL, '');            
            $low_stock = array();
            
            $multi_select_duplicates = array();
            
            // iterate through the items and add to the order detail table
            // plus decrement the quantity from the product detail table
            foreach ($items as $key => $item) {
                if ($item->quantity < 1) {
                    continue;   
                }
                $item_name = yak_get_title($item->id, $item->cat_id);
                $item->price = yak_calc_price($item->id, $item->cat_id, $item->price);
                $item->discount = yak_calc_price_discount($item->id, $item->quantity, $item->price, $totals->quantity, $totals->price, $promo);
                $total_discount += ($item->discount * $item->quantity);
                $order_value += $item->get_discount_total();
                
                $sql = $wpdb->prepare("insert into $order_detail_table (id, itemname, price, quantity, post_id, cat_id) 
                                       values (%d, %s, %f, %d, %d, %d)",
                        $order_id, $item_name, $item->price, $item->quantity, $item->id, $item->cat_id);
                $wpdb->query($sql);
                              
                $sql = $wpdb->prepare("update $product_detail_table
                                       set quantity = quantity - %d
                                       where post_id = %d
                                       and cat_id = %d
                                       and quantity is not null", $item->quantity, $item->id, $item->cat_id);
                $wpdb->query($sql);
                
                // add the selected options to order meta (don't really have a better place for them)              
                if (isset($item->selected_options)) {
                    if (!isset($multi_select_duplicates[$item_name])) {
                        $multi_select_duplicates[$item_name] = 1;
                    }
                    else {
                        $multi_select_duplicates[$item_name]++;
                    }
                    $i = 1;
                    foreach ($item->selected_options as $msi) {
                        $iname = $item_name;
                        if ($multi_select_duplicates[$item_name] > 1) {
                            $iname .= ' (' . $multi_select_duplicates[$item_name] . ')';
                        }
                        yak_insert_ordermeta($order_id, $iname . ' item' . $i, $msi, $item->id, $item->cat_id);
                        $i += 1;
                    }
                }
                
                foreach ($item->meta as $metakey => $metaval) {
                    yak_insert_ordermeta($order_id, $metakey, $metaval, $item->id, $item->cat_id);
                    $i += 1;
                }
                
                $sql = $wpdb->prepare("select dl_file, quantity
                                       from $product_detail_table
                                       where post_id = %d
                                       and cat_id = %d", $item->id, $item->cat_id);
                $row = $wpdb->get_row($sql);
  
                if ($low_stock_threshold > 0 && !empty($low_stock_email) && $row->quantity <= $low_stock_threshold) {
                    $low_stock[] = $item_name . ' : ' . $row->quantity;
                }
  
                // if this is a downloadable product, create an order_dl record
                if (isset($row->dl_file) && $row->dl_file != '') {
                    $sql = $wpdb->prepare("insert into $order_dl_table (order_id, uid, dl_file, download_attempts) 
                                           values (%d, null, %s, 0)", $order_id, $row->dl_file);
                    $wpdb->query($sql);
                }
                
                if (isset($item->special_option) && $item->special_option != '') {
                    yak_insert_ordermeta($order_id, "special option for $item_name", $item->special_option);
                }
            }
            unset($multi_select_duplicates);
            
            $total_cost = $order_value;

            if (yak_in_blacklist($addr, $baddr)) {
                $_POST['action'] = 'confirm2';
                $_POST['error_message'] = __('Sorry, an unexpected error occurred while processing your order. Please get in contact to continue.', 'yak');
                yak_insert_orderlog($order_id, "Customer appears in blacklist, order processing failed");
                yak_admin_update_order($order_id, ERROR, 0);
                setcookie(BLACKLISTED_COOKIE, BLACKLISTED_COOKIE, time() + 315360000, '/');
                return;
            }
            
            if (count($low_stock) > 0) {
                yak_sendmail($low_stock_email, $low_stock_email, __('Low Stock Notification', 'yak-admin'), 
                    __y("WARNING: there is low stock for the following items:\n\n%s", 
                    'yak-admin', implode("\n", $low_stock)));
            }
            
            if ($promo != null) {
                $shipping_discount = yak_calc_shipping_discount($shipping_cost, $promo, $items);
                if ($shipping_discount > 0) {
                    $order_value -= $shipping_discount;
                    $total_discount = $shipping_discount;
                }
            }
            
            // add the promotion total discount to the order detail
            if ($promo != null) {
                $sql = $wpdb->prepare("insert into $order_detail_table (id, itemname, price, quantity) 
                                       values (%d, %s, %f, 1)",
                        $order_id, 'promo ' . $promo->code . ' discount', -$total_discount);
                $wpdb->query($sql);
                if (yak_str_contains($promo->promo_type, 'ship')) {
                    $s = $wpdb->prepare("update $order_table set shipping_discount = %d where id = %d", $total_discount, $order_id);
                }
                else {
                    $s = $wpdb->prepare("update $order_table set price_discount = %d where id = %d", $total_discount, $order_id);
                }
                $wpdb->query($s);
                
                do_action('yak-use-promotion', array('promo' => $promo, 'order_id' => $order_id, 'total_discount' => $total_discount));
            }
            
            if (isset($_POST['special_instructions']) && !empty($_POST['special_instructions'])) {
                $note = $_POST['special_instructions'];
                $default_instructions = stripslashes(yak_get_option(DEFAULT_SPECIAL_INSTRUCTIONS,''));
                if ($note != $default_instructions) {
                    yak_insert_ordermeta($order_id, 'Special Instructions', $note);
                }
            }

            // trigger action for listeners             
            $GLOBALS['yak-add-to-price'] = array();
            if ($baddr != null && !empty($baddr->country)) {
                $cty = $baddr->country;
                $st = $baddr->state;
            }
            else {
                $cty = $addr->country;
                $st = $addr->state;
            }
            do_action('yak-order-confirm', array('order_id'=>$order_id, 'total_cost'=>$total_cost, 
                    'state'=>$st, 'country'=>$cty, 'items'=>&$items));
            $add_to_price =& $GLOBALS['yak-add-to-price'];
            foreach ($add_to_price as $add) {
                $order_value += $add;
            }
            
            // finally add the shipping cost onto the order value
            $order_value += $shipping_cost;
            
            // find the payment option
            $payment_types = yak_get_option(PAYMENT_TYPES, null);
            $ptype = $_SESSION[PAYMENT_TYPE];
            $ptypeval = $payment_types[$ptype];
            
            $payments =& yak_get_payment_opts();
            $options = $payments['options'];
            $payment_option = $options[$ptypeval];
            
            if ($payment_option != null) {
                // do any payment-specific order confirmation
                do_action('yak-confirm-order-' . $payment_option, $order_id, $items);
            
                $additional_query_string = yak_convert_to_querystring(array(SELECTED_SHIPPING_OPTION, SHIPPING_IS_BILLING, 
                    SHIPPING_COUNTRY, SHIPPING_STATE, BILLING_COUNTRY, BILLING_STATE), $_POST);
            
                // call the payment option to find where we need to redirect to complete
                $url = apply_filters('yak-redirect-' . $payment_option, $_SESSION[PAYMENT_TYPE], $order_id, $items, 
                        $shipping_cost - $shipping_discount, $selected_shipping, $additional_query_string);
            
                $_SESSION['order_value'] = yak_format_money($order_value, true);
            
                // redirect to final page (perhaps external, such as PayPal)
                
                if (defined('YAK_DEBUG')) {
                    yak_log("Redirect URL: $url");
                }
                
                header("Location: $url");
            }
            
            // drop out
            exit;
        }
        else if ($_POST['action'] == 'confirm' && count(yak_validate_address('shipping')) == 0) {
            yak_process_address('shipping', 'shipping');
            
            if (isset($_POST[SHIPPING_IS_BILLING]) && $_POST[SHIPPING_IS_BILLING] == 'on') {
                yak_process_address('shipping', 'billing');
            }
            else {
                yak_process_address('billing', 'billing');   
            }
        }
        else if (isset($_POST['buynow'])) {
            // the buy button has been hit
            $items = NULL;
            if (isset($_SESSION[ITEMS_NAME])) {
                $items = $_SESSION[ITEMS_NAME];
            }
            else {
                $items = array();
                $_SESSION[ITEMS_NAME] = $items;
            }
            
            $p = $_POST['buynow'];
            $param_name = $_POST['buynow_param'];
            $cat_id = $_POST['category'];
            $special_option = $_POST['special_option'];
            
            $key = $p . '_' . $cat_id;
                        
            $pp = yak_get_product($p);
            $multi_select_count = count($_POST['multiselect1']);
            
            # get multi-select options
            $selected_options = array();
            if (!empty($pp->multi_select_options)) {
                for ($i = 1; $i < 100; $i++) {
                    if (!isset($_POST['multiselect' . $i])) {
                        break;
                    }
                    $selected_options = array_merge($selected_options, $_POST['multiselect' . $i]);
                }
                sort($selected_options);
                $key .= '_' . rawurlencode(str_replace(' ', '_', implode('_', $selected_options)));
            }
            
            #
            # do external validation (any third party add-ons which want to validate)
            #
            $validation_message = apply_filters('yak-buy-validate', null);
            
            global $user_ID;
            
            if ($pp->require_login == 'true' && (!isset($user_ID) || $user_ID == 0)) {
                $wp_register = yak_get_blogurl() . '/wp-register.php?redirect_to=' . get_permalink($pp->ID);
                $wp_login = yak_get_blogurl() . '/wp-login.php?redirect_to=' . get_permalink($pp->ID);
                $error_message = '<span class="yak_buyerror">'
                    . __y('You must be logged in to purchase this product. Click <a href="%s">here</a> to register, or <a href="%s">login</a> if you\'ve already registered.', 'yak',
                            $wp_register, $wp_login)
                    . '</span>';
                $GLOBALS['buynow_error_message'] = $error_message;
            }
            else if (!empty($pp->multi_select_options) && $multi_select_count == 0) {
                $error_message = '<span class="yak_buyerror">' . __('You haven\'t selected any items', 'yak') . '</span>';
            }
            else if (!empty($pp->multi_select_options) && $multi_select_count < $pp->multi_select_min) {
                $msg = __('You can select a minimum of %1 items', 'yak');
                $error_message = '<span class="yak_buyerror">' . str_replace('%1', $pp->multi_select_min, $msg) . '</span>';
            }
            else if (!empty($pp->multi_select_options) && $multi_select_count > $pp->multi_select_max) {
                $msg = __('You can select a maximum of %1 items', 'yak');
                $error_message = '<span class="yak_buyerror">' . str_replace('%1', $pp->multi_select_max, $msg) . '</span>';
            }
            else if ($validation_message != null) {
                #
                # external validation failed
                #
                $error_message = '<span class="yak_buyerror">' . $validation_message . '</span>';
            }
            else if (!isset($items[$key]) || empty($items[$key])) {
                $qty = 1;
                if (isset($_POST[OVERRIDE_QUANTITY])) {
                    $qty = $_POST[OVERRIDE_QUANTITY];
                }
                
                $items[$key] = new YakItem($param_name, $p, $cat_id, $qty);
                $items[$key]->name = yak_get_title($p, $cat_id);
                $items[$key]->weight = yak_get_product_weight($p, $cat_id);
                $items[$key]->special_option = $special_option;
                
                if (!empty($pp->multi_select_options)) {
                    $items[$key]->selected_options = $selected_options;
                }
                
                if ($pp->custom_price) {
                    $items[$key]->price = $_POST['yak_custom_price'];
                }
                
                do_action('yak-buy-item', array('item' => $items[$key]));
                
                $_SESSION[ITEMS_NAME] = $items;  // probably not needed, but I had some weird problems at one point

                yak_update_session_values($p, $cat_id, $qty, $items[$key]->price);

                $error_message = '<span class="yak_buyerror">' . __('This item has been added to your shopping cart', 'yak') . '</span>';
            }
            else if (yak_get_option(DUPLICATE_HANDLING, '') == 'increment') {
                $item = $items[$key];
                
                $qty = 1;
                if (isset($_POST[OVERRIDE_QUANTITY])) {
                    $qty = $_POST[OVERRIDE_QUANTITY];
                }
                
                $item->quantity += $qty;
                
                yak_update_session_values($p, $cat_id, $qty, $item->price);
            }
            else {
                $error_message = '<span class="yak_buyerror">' . __('This item is already in your shopping cart', 'yak') . '</span>';
            }
            
            $referrer = yak_get_referrer();
            if ($referrer != null) {
                $error_message .= '<br />' . $referrer;
            }
            $GLOBALS['buynow_error_message_' . $p] = $error_message;            
        }
    }
}


if (!function_exists('yak_insert_orderlog')) {
    /**
     * Insert into the yak order log table.
     *
     * @param $order_id the reference id of the order
     * @param $msg the message to insert
     */
    function yak_insert_orderlog($order_id, $msg) {
        global $wpdb, $order_log_table;
        
        if (defined('YAK_DEBUG')) {
            yak_log("Order Id: $order_id - $msg");
        }
        
        if (!isset($order_id)) {
            $order_id = 'null';   
        }
        
        $sql = $wpdb->prepare("insert into $order_log_table values (current_timestamp(), %s, %d)", $msg, $order_id);
        $wpdb->query($sql);
    }
}


if (!function_exists('yak_insert_ordermeta')) {
    /**
     * Insert a row into the meta table for an order.
     */
    function yak_insert_ordermeta($order_id, $name, $value, $post_id = null, $cat_id = null) {
        global $wpdb, $order_meta_table;
        if (empty($post_id)) {
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value, post_id, cat_id)
                                   values (%d, %s, %s, null, null)", 
                        $order_id, $name, $value);
        }
        else {
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value, post_id, cat_id)
                                   values (%d, %s, %s, %d, %d)", 
                        $order_id, $name, $value, $post_id, $cat_id);
        }
        $wpdb->query($sql);
    }
}


if (!function_exists('yak_install')) {
    /**
     * Installation routine to set up tables
     */
    function yak_install() {
        require_once('yak-install.php');        
    }
}


if (!function_exists('yak_process_address')) {
    /**
     * Retrieve an address from the _POST, and store in both COOKIE and SESSION.
     *
     * @param $type the type of address (shipping or billing)
     * @param $name the name to use in both cookie and session
     */
    function yak_process_address($type, $name) {
        global $countries;
        
        $address = new YakAddress($_POST[$type . '_email'],
                                  $_POST[$type . '_recipient'],
                                  $_POST[$type . '_company_name'],
                                  $_POST[$type . '_phone'],
                                  $_POST[$type . '_addr1'],
                                  $_POST[$type . '_addr2'],
                                  $_POST[$type . '_suburb'],
                                  $_POST[$type . '_city'],
                                  $_POST[$type . '_region'],
                                  $_POST[$type . '_state'],
                                  $_POST[$type . '_postcode'],
                                  $_POST[$type . '_country'],
                                  $type);
                                  
        if ($address->country == 'US' || $address->country == 'CA') {
            $address->region = null;
        }
        else {
            $address->state = null;
        }
                                  
        $saddr = serialize($address);
        setcookie($name . ADDRESS_COOKIE_SUFFIX, $saddr, time() + yak_get_option(COOKIE_LIFETIME, 2592000), '/');
        $_SESSION[$name . ADDRESS_COOKIE_SUFFIX] = $saddr;
    }
}


if (!function_exists('yak_price')) {
    /**
     * Return or output the price of the current product.
     *
     * @param $discount whether or not to discount the price (defaults to true)
     * @param $echo output this price
     * @param $type the type of product (for products with multiple types)
     */
    function yak_price($post_id, $type, $discount = true, $echo = true, $format = true) {
        if ($type == 'page') {
            $cat_id = -1;
        }        
        else if ($type != null) {
            $cat_id = get_cat_ID($type);
        }
        else {
            $cat_id = null;
        }
        
        if ($discount) {
            $price = yak_calc_price($post_id, $cat_id);
        }
        else {
            $prod = yak_get_product($post_id);
            $price = $prod->price;

            if ($cat_id != null) {
                $prod_type = yak_get_product_type($post_id, $cat_id);
                
                if (!empty($prod_type->override_price)) {
                    $price = $prod_type->override_price;
                }
            }
        }
        
        if ($format) {
            $rtn = yak_format_money($price, true);
        }
        else {
            $rtn = $price;
        }
        
        if ($echo) {
            echo $rtn;
        }
        
        return $rtn;
    }
}


if (!function_exists('yak_set_product_detail')) {
    /**
     * Insert or update product details
     *
     * @param $post_id the post to set product data for
     * @param $cat_id the category to set product data for
     * @param $qty the quantity of the product available
     * @param $price_override the override price for the product type
     * @param $dlfile the download file, if this is a downloadable product
     * @param $weight the weight of the product
     */
    function yak_set_product_detail($post_id, $cat_id, $sku, $qty, $override_price, $dlfile, $weight) {
        global $wpdb, $product_detail_table;
        
        $sql = $wpdb->prepare("select * 
                               from $product_detail_table
                               where post_id = %d and cat_id = %d", $post_id, $cat_id);
        $row = $wpdb->get_row($sql);
        
        if (empty($override_price)) {
            $override_price_fixed = 'null';
        }
        else {
            $override_price_fixed = $override_price;
        }
        
        if (empty($weight) || $weight == 0) {
            $weight_fixed = 'null';
            $weight = null;
        }
        else {
            $weight_fixed = $weight;
        }
        
        if (empty($sku)) {
            $sku = '';
        }
        
        if ($qty == null || $qty == '') {
            $qty = 'null';
        }
        
        if (!isset($row)) {
            $sql = $wpdb->prepare("insert into $product_detail_table (post_id, cat_id, sku, quantity, override_price, weight, dl_file)
                                   values (%d, %d, %s, $qty , $override_price_fixed, $weight_fixed, %s)",
                    $post_id, $cat_id, $sku, $dlfile);
            $wpdb->query($sql);
            yak_log("Adding product type ($sql)");
        }
        else if ($row->quantity != $qty || $row->override_price != $override_price || $row->dl_file != $dlfile
                || $row->weight != $weight || $row->sku != $sku) {
            $sql = $wpdb->prepare("update $product_detail_table 
                                   set quantity = $qty,
                                   override_price = $override_price_fixed, 
                                   dl_file = %s,
                                   weight = $weight_fixed,
                                   sku = %s
                                   where post_id = %d and cat_id = %d",
                    $dlfile, $sku, $post_id, $cat_id);
            $wpdb->query($sql);
            yak_log("Updating product type ($sql)");
        }
    }
}


if (!function_exists('yak_updproduct')) {
    /**
     * Called when a product or post is saved to set the price and title.
     *
     * @param $post_id the post or page id
     */
    function yak_updproduct($post_id) {
        if (!isset($_POST['yak_action'])) {
            return;   
        }

        // updproduct can be called twice, once from publish_post event and also from
        // edit_post. This could be changed so that only edit_post calls this event (I think),
        // so should come back and re-visit this code.
        if (isset($_POST['yak_updproduct_' . $post_id])) {
            return;
        }
        else {
            $_POST['yak_updproduct_' . $post_id] = $post_id;
        }
         
        $price = $_POST['yak_price'];
        $alt_title = $_POST['yak_title'];
        $discount_override = $_POST['yak_discount_override'];
        $multi_select = $_POST['yak_multi_select'];
        $multi_select_min = $_POST['yak_multi_select_min'];
        $multi_select_max = $_POST['yak_multi_select_max'];
        $multi_select_cols = $_POST['yak_multi_select_cols'];
        $require_login = $_POST[REQUIRE_LOGIN];
        $description = $_POST['yak_description'];
        $custom_price = $_POST[CUSTOM_PRICE];
        
        if (!empty($price) || !empty($alt_title) || !empty($discount_override) || !empty($multi_select)
                || !empty($multi_select_min) || !empty($multi_select_max) || !empty($multi_select_cols) 
                || !empty($require_login) || !empty($custom_price)) {
            yak_update_product($post_id, $price, $alt_title, $discount_override, $multi_select, $multi_select_min, $multi_select_max, 
                $multi_select_cols, $require_login, $description, $custom_price);
        }
        
        $product = yak_get_product($post_id);
        $types = yak_get_product_categories($product->id, $product->status, true, true);
        
        yak_update_product_types($product->id, $types);
    }
}


if (!function_exists('yak_update_product')) {
    /**
     * Insert, update, or delete product details (price and alternate title)
     *
     * @param $post_id the id of the post/page
     * @param $price the price of the product
     * @param $alt_title the alternate title
     */
    function yak_update_product($post_id, $price, $alt_title, $discount_override, $multi_select_options, $multi_select_min,
            $multi_select_max, $multi_select_cols, $require_login, $description, $custom_price) {
        global $wpdb, $product_table;

        $description = stripslashes($description);
        
        $sql = $wpdb->prepare("select price, alt_title, discount_override, multi_select_options, multi_select_min, multi_select_max, 
                                    multi_select_cols, require_login, description, custom_price
                               from $product_table
                               where post_id = %d", $post_id);
        $row = $wpdb->get_row($sql);
                               
        $defprice = yak_default($price, 0.0);
        if ($require_login == 'on') {
            $def_require_login = 1;
        }
        else {
            $def_require_login = yak_default($require_login, 0);
        }
        
        $discount_override_fix = yak_default($discount_override, 'null');
        $multi_select_min_fix = yak_default($multi_select_min, 'null');
        $multi_select_max_fix = yak_default($multi_select_max, 'null');
        $multi_select_cols_fix = yak_default($multi_select_cols, 'null');
        
        if ($custom_price == 'on') {
            $custom_price = 1;
        }
        else {
            $custom_price = yak_default($custom_price, 0);
        }
        
        if ((!empty($price) || !empty($alt_title) || $price >= 0) && (!isset($row) || $row == null)) {
            $sql = $wpdb->prepare("insert into $product_table (post_id, price, alt_title, discount_override, multi_select_options, 
                                        multi_select_min, multi_select_max, multi_select_cols, require_login, description, custom_price)
                                   values (%d, %f, %s, %f, %s, %s, %s, %s, %d, %s, %d)", 
                    $post_id, $defprice, $alt_title, $discount_override, $multi_select_options,
                    $multi_select_min, $multi_select_max, $multi_select_cols, $def_require_login, $description, $custom_price);
            $wpdb->query($sql);
            yak_log("Added product ($sql)");
        }
        else if ($row->price != $price || $row->alt_title != $alt_title || $row->discount_override != $discount_override 
                || $row->multi_select_options != $multi_select_options || $row->multi_select_min != $multi_select_min
                || $row->multi_select_max != $multi_select_max || $row->multi_select_cols != $multi_select_cols
                || $row->require_login != $def_require_login
                || $row->description != $description
                || $row->custom_price != $custom_price) {
            $sql = $wpdb->prepare("update $product_table
                                   set price = %f,
                                   alt_title = %s,
                                   discount_override = %s,
                                   multi_select_options = %s,
                                   multi_select_min = %s,
                                   multi_select_max = %s,
                                   multi_select_cols = %s,
                                   require_login = %d,
                                   description = %s,
                                   custom_price = %d
                                   where post_id = %d",
                    $defprice, $alt_title, $discount_override, $multi_select_options, 
                    $multi_select_min, $multi_select_max, $multi_select_cols, $def_require_login, $description, 
                    $custom_price, $post_id);
            $rows = $wpdb->query($sql);
            yak_log("Updated product ($sql)");
        }
    }
}


if (!function_exists('yak_update_product_types')) {
    function yak_update_product_types($product_id, $types) {
        global $wpdb, $product_detail_table;
        
        $return_types = array();
        foreach ($types as $type) {
            if (strtolower($type->name) == 'default' && isset($_POST['qty__-1'])) {
                $name_suffix = '_-1';
            }
            else {
                $name_suffix = $product_id . '_' . $type->cat_id;
            }
            $sku = $_POST['sku_' . $name_suffix];
            $qty = $_POST['qty_' . $name_suffix];
            $override_price = $_POST['price_' . $name_suffix];
            $oldqty = $_POST['oldqty_' . $name_suffix];
            $dlfile = $_POST['dl_file_' . $name_suffix];
            $weight = $_POST['weight_' . $name_suffix];
            $delete = $_POST['delete_' . $name_suffix];
            
            // if the delete flag is set, remove row and continue
            if (!empty($delete) && $delete == 'true') {
                $sql = $wpdb->prepare("delete from $product_detail_table
                                       where post_id = %d
                                       and cat_id = %d", $product_id, $type->cat_id);
                $wpdb->query($sql);
                $sql = $wpdb->prepare("delete from $wpdb->term_relationships
                                       where object_id = %d 
                                       and term_taxonomy_id = %d", $product_id, $type->cat_id);
                $wpdb->query($sql);
                continue;
            }
            
            // set default weight
            if (!isset($weight) || $weight == '') {
                $weight = 0;
            }
        
            if (!empty($qty) && $qty != '') {
                // calc quantity -- need to take into account the current quantity in the db (in case
                // someone has ordered in the meantime)
                $sql = $wpdb->prepare("select quantity from $product_detail_table 
                                       where post_id = %d 
                                       and cat_id = %d", $product_id, $type->cat_id);
                $row = $wpdb->get_row($sql);
                
                if ($row->quantity != null) {                                  
                    $diff = $oldqty - $row->quantity;
            
                    $qty = $qty - $diff;
                    if ($qty < 0) {
                        $qty = 0;   
                    }
                }
            }
            
            yak_set_product_detail($product_id, $type->cat_id, $sku, $qty, $override_price, $dlfile, $weight);
                      
            $type->qty = $qty;
            $type->price_override = $price_override;
            $type->dl_file = $dlfile;
            $type->weight = $weight;
            
            $return_types[] = $type;
        }
    
        $prod_cat_name = yak_get_option(PRODUCT_CATEGORY_NAME, 'products');
        $sql = $wpdb->prepare("select term_id 
                               from $wpdb->terms
                               where slug = %s", $prod_cat_name);
        $row = $wpdb->get_row($sql);
        $parent_id = $row->term_id;
        
        $pid = $product_id;
        if (count($_POST['newtype_'])) {
            $pid = "";
        }
        $newtypes = $_POST['newtype_' . $pid];
        $newtype_sku = $_POST['newtype_sku_' . $pid];
        $newtype_names = $_POST['newtype_name_' . $pid];
        $newtype_qty = $_POST['newtype_qty_' . $pid];
        $newtype_price = $_POST['newtype_price_' . $pid];
        $newtype_weight = $_POST['newtype_weight_' . $pid];
        $newtype_dl = $_POST['newtype_dl_file_' . $pid];
        $size = count($newtypes);
        for ($i = 0; $i < $size; $i++) {
            if (!empty($newtype_names[$i])) {
                $name = $newtype_names[$i];
                $cat_id = wp_create_category($name, $parent_id);
            }
            else if (!empty($newtypes[$i])) {
                $cat_id = $newtypes[$i];
                $name = get_the_category_by_ID(intval($cat_id));
            }
            else {
                continue;
            }
        
            $sql = $wpdb->prepare("select count(*) as total 
                                   from $wpdb->term_relationships
                                   where object_id = %d and term_taxonomy_id = %d", $product_id, $cat_id);
            $row = $wpdb->get_row($sql);

            if ($row->total < 1) {
                $sql = $wpdb->prepare("insert into $wpdb->term_relationships (object_id, term_taxonomy_id)
                                       values (%d, %d)", $product_id, $cat_id);
                $wpdb->query($sql);
            }
                 
            yak_set_product_detail($product_id, $cat_id, $newtype_sku[$i], $newtype_qty[$i], $newtype_price[$i], $newtype_dl[$i], $newtype_weight[$i]);
        
            $type = new YakProductType($product_id, $cat_id, $name, $newtype_sku[$i], $newtype_qty[$i], $newtype_price[$i], $newtype_weight[$i], $newtype_dl[$i]);
            $return_types[] = $type;
        }
    
        return $return_types;
    }
}


if (!function_exists('yak_update_session_values')) {
    function yak_update_session_values($product_id, $cat_id, $qty = 1, $custom_price = null) {
        $price = yak_calc_price($product_id, $cat_id, $custom_price) * $qty;
        if (!isset($_SESSION['current_order_value'])) {
            $_SESSION['current_order_value'] = $price;
            $_SESSION['current_order_items'] = $qty;
        }
        else {
            $_SESSION['current_order_value'] += $price;
            $_SESSION['current_order_items'] += $qty;
        }
    }
}


if (!function_exists('yak_validate_address')) {
    /**
     * Validate the address in a post.
     *
     * @param $type the type of address (shipping or billing)
     */
    function yak_validate_address($type) {
        global $required_address_fields;
        
        $errors = array();
        foreach ($required_address_fields as $field => $message) {
            $val = $_POST[$type . '_' . $field];
            if (empty($val)) {
                $errors[] = __($message, 'yak');
            }
            else if ($field == 'email') {
                require_once(ABSPATH . 'wp-includes/formatting.php');
                if (!is_email($val)) {
                    $errors[] = __('Email address is invalid', 'yak');
                }
            }
        }

        return $errors;
    }
}

// WordPress actions
add_action('init', 'yak_init');
add_action('activate_yak-for-wordpress/yak-for-wordpress.php', 'yak_install');
add_action('publish_post', 'yak_updproduct');
add_action('publish_page', 'yak_updproduct');
add_action('edit_post', 'yak_updproduct');

// widget
add_filter('plugin_row_meta', 'yak_register_plugin_links', 10, 2);
?>