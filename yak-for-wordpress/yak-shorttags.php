<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-currencies.php');

if (!function_exists('yak_back_to_address_tag')) {
    /**
     * [yak_back_to_address]
     *
     * Button to jump back to address input in the case of an error
     */
    function yak_back_to_address_tag($attrs) {
        $checkout_param = $_SESSION['checkout_param'];
        $checkout_param_val = $_SESSION['checkout_param_val'];

        return yak_buy_begin_tag() 
             . '<button id="back_to_address" class="yak_large_button" type="submit">Back to address</button>'
             . '<input type="hidden" name="action" value="jump-to-address" />'
             . '<input type="hidden" name="' . $checkout_param . '" value="' . $checkout_param_val . '" />'
             . yak_buy_end_tag();
    }
}


if (!function_exists('yak_back_to_cc_tag')) {
    /**
     * [yak_back_to_cc]
     *
     * Button to jump back to credit card form in the case of an error
     */
    function yak_back_to_cc_tag($attrs) {
        $checkout_param = $_SESSION['checkout_param'];
        $checkout_param_val = $_SESSION['checkout_param_val'];
    
        ob_start();
        include 'yak-view-shipping-snippet.php';
        $shipping_form = ob_get_contents();
        ob_end_clean();
    
        return yak_buy_begin_tag() 
             . '<button id="back_to_credit_card" class="yak_large_button" type="submit">Back to credit card</button>'
             . '<input type="hidden" name="action" value="jump-to-payment" />'
             . $shipping_form
             . '<input type="hidden" name="' . $checkout_param . '" value="' . $checkout_param_val . '" />'
             . yak_buy_end_tag();
    }
}


if (!function_exists('yak_buy_tag')) {
    /**
     * [yak_buy]
     *
     * Output the html for the buy button
     */
    function yak_buy_tag($attrs) {
        $pp = yak_get_product(null, true);
    
    	extract(shortcode_atts(array(
    		'id' => null,
    		'price' => 'off',
    		'split' => false,
    		'redirect_name' => null,
    		'redirect_id' => null,
    		'error_message_spacer' => '&nbsp;',
    		'custom_price_msg' => null
    	), $attrs));
    	
    	// hack for NextGen Gallery plugin
    	if ($id == null && count($attrs) > 0 && is_numeric($attrs[0])) {
    	    $id = $attrs[0];
	    }
        
        if ($id != $pp->ID) {
            $pp = yak_get_product($id);
        }
        
        $attrs['yak_post'] = $pp;

        $begin = yak_buy_begin_tag();
        $content = yak_buy_content_tag($attrs);
        $end = yak_buy_end_tag();
        
        return $begin . "\n" . $content . "\n" . $end;
    }
}


if (!function_exists('yak_buy_begin_tag')) {
    /**
     * [yak_buy_begin]
     * Begin the output of the buy button html (output the form element)
     */
    function yak_buy_begin_tag($attrs = null) {
        $url = apply_filters('the_permalink', yak_get_blogurl() . '/index.php');
        return '<form name="buynow" action="' . $url . '#buynow_button" method="post">';
    }
}


if (!function_exists('yak_buy_content_tag')) {
    /**
     * [yak_buy_content]
     *
     * Output the content of the buy button (drop down box for the types, the image of the buy button, etc).
     */
    function yak_buy_content_tag($attrs = null) {
        global $wpdb, $post, $wp_query, $order_table, $order_detail_table;
        
        extract(shortcode_atts(array(
    		'yak_post' => null,
    		'price' => 'off',
    		'split' => false,
    		'redirect_name' => null,
    		'redirect_id' => null,
    		'error_message_spacer' => '&nbsp;',
    		'custom_price_msg' => null
    	), $attrs));
        
        $rtn = '';
        
        $productpost = yak_get_product_post($yak_post);
        
        // data we'll use for submitting the form (do we redirect to another page,
        // or direct back to this page or post?)
        $redirect = yak_get_option(REDIRECT_ON_BUY_TO, '');
        if ($redirect != '') {
            $param_name = 'page_id';
            $id = $redirect;
        }
        else if (!$wp_query->is_page) {
            $param_name = 'p';
            $id = $productpost->ID;
        }
        else {
            $param_name = 'page_id';
            $id = $productpost->ID;
        }
        
        // the product being bought is either a post, or a page
        if (strtolower($productpost->post_type) == 'page') {
            $productpost_param_name = 'page_id';
        }
        else {
            $productpost_param_name = 'p';   
        }
        
        // if maintenance mode is on
        if (yak_get_option(MAINTENANCE_MODE, 'off') == 'off') {
            $disabled_attr = '';
            $disabled = false;
            $hover_title = '';
        }
        else {
            $disabled_attr = ' disabled="disabled" ';
            $disabled = true;
            $hover_title = ' title="' . __('Purchase is currently disabled, please try again later', 'yak') . '" ';
        }
        
        // if price isn't set then don't bother displaying the button, nor the out of stock
        // message -- this is so you can add all the functions in your post but have nothing
        // displayed until you set the yak_* fields
        $prod = yak_get_product($productpost->ID);
        
        if (!empty($prod) && (!empty($prod->price))) {
            if (yak_get_option(UNLIMITED_QUANTITY, '') == 'on') {
                $include_empty = true;
            }
            else {
                $include_empty = false;
            }

            $group_idx = 1;

            $categories = yak_get_product_categories($productpost->ID, $productpost->post_type, $include_empty);
            if (sizeof($categories)) {
                $rtn = '<a name="buynow_button"></a>';
                
                if (!empty($prod->multi_select_options)) {
                    $count = 0;
                    $inc_title = false;
                    
                    // number of columns in the table
                    $mscols = yak_default($prod->multi_select_cols, 2);
                    
                    // loop over the multi-select options
                    $options = $prod->get_multi_select_options();
                    foreach ($options as $val) {
                        // explode the value
                        if (yak_str_contains($val, ',')) {
                            $v = explode(',', $val);
                            $name = $v[0];
                            $img = "<img src=\"" . $v[1] . "\" alt=\"$name\" /><br />";
                            $title = $v[1];
                        }
                        else {
                            $name = $val;
                            $img = '';
                            $title = '';
                        }
                        
                        // if this is a title block
                        if ($name == 'TITLE') {
                            if ($count > 0) {
                                if ($count % $mscols != 0) {
                                    $rtn .= '<td></td>';
                                }
                                $rtn .= '</tr></table>';
                                $group_idx++;
                            }
                            $rtn .= '<strong>' . __($title, 'yak') . '</strong><br />';
                            $inc_title = true;
                        }
                        else {
                            if ($count == 0 || $inc_title) {
                                $rtn .= '<table class="yak-multi">';
                                $count = 0;
                                $inc_title = false;
                            }
                        
                            if ($count % $mscols == 0) {
                                if ($count > 0) {
                                    $rtn .= '</tr>';
                                }
                                $rtn .= '<tr>';
                            }
                            $rtn .= "<td class=\"yak-multi\">$img " . __($name, 'yak') . " <input name=\"multiselect" . $group_idx . "[]\" type=\"checkbox\" value=\"$name\" $disabled_attr /></td>";

                            if ($count + 1 == count($options)) {
                                $rtn .= '</tr>';
                            }
                            
                            $count++;
                        }
                    }
                    $rtn .= '</table>';
                }
                
    
                $special_options = get_post_meta($productpost->ID, 'yak_special_options', true);
                if (isset($special_options) && $special_options != '') {
                    $rtn .= '<div><span>' . yak_get_option(SPECIAL_OPTIONS_TEXT, '') . '</span>
                                 <span>' . yak_html_select(array('name'=>'special_option', 'values'=>split("\n", $special_options), 'nokey'=>true, 'disabled'=>$disabled)) . '</span></div>';
                }
                
                $auto_discount = yak_get_option(AUTO_DISCOUNT, 1);
                
                $include_price = (yak_get_option(OPTIONS_DROPDOWN_INCLUDE_PRICE, 'off') == 'on') || ($price == 'on');
                $arr = array();
                foreach ($categories as $cat) {
                    $last = $cat->cat_id;
                    
                    if (strtolower($cat->name) == 'default') {
                        continue;   
                    }
                    else if ($prod->price != 'custom') {
                        // add price to description in option dropdown
                        $append_price = '';
                        if ($include_price) {
                            $price = $cat->override_price;
                            if (empty($price)) {
                                $price = $prod->price;
                            }
                            
                            if ($cat->discount_override != null && $cat->discount_override != 1 && $cat->discount_override > 0) {
                                $price = $price * $cat->discount_override;
                            }
                            else {
                                $price = $price * $auto_discount;
                            }
                            
                            $append_price = ' (' . yak_format_money($price, true) . ')';
                        }
                        $arr[$cat->cat_id] = __($cat->name, 'yak') . $append_price;
                    }
                }
                
                if ($prod->custom_price) {
                    $rtn .= '<span><input type="text" name="yak_custom_price" size="8" />&nbsp;&nbsp;</span>';
                }
                else if (yak_get_option(QUANTITY_INPUT_BUY_BUTTON, 'off') == 'on') {
                    $rtn .= '<span><input type="text" name="' . OVERRIDE_QUANTITY . '" size="3" value="1" title="'
                        . __('Enter the quantity you would like to add to the cart', 'yak') . '" />&nbsp;&nbsp;</span>';
                }
                
                $catsize = sizeof($arr);
                if ($catsize > 0 && (yak_get_option(DISPLAY_PRODUCT_OPTIONS, 'off') == 'on' || $catsize > 1)) {
                    $rtn .= '<span>' . yak_html_select(array('name'=>'category', 'values'=>$arr, 'class'=>'yak_select', 'disabled'=>$disabled));
                    
                    if ($split) {
                        $rtn .= "<br />";
                    }
                    else {
                        $rtn .= "</span><span>";
                    }
                }
                else {
                    $rtn .= '<span><input type="hidden" name="category" value="' . $last . '" />';   
                }
                         
                $msmin = yak_default($prod->multi_select_min, 0);
                $msmax = yak_default($prod->multi_select_max, 0);
                                 
                if ($redirect_name != null && $redirect_id != null) {
                    $param_name = $redirect_name;
                    $id = $redirect_id;
                }
                
                if (yak_get_option(AJAX_BUY_BUTTON, 'off') == 'on') {
                    $ajax = 'true';
                }
                else {
                    $ajax = 'false';
                }
                
                if (!empty($productpost->custom_price)) {
                    $custom_price = $productpost->custom_price;
                }
                else {
                    $custom_price = 0;
                }
                
                $rtn .= '<button id="addbutton_' . $productpost->ID . '" name="addbutton" class="yak_button" type="submit" '
                      . 'onclick="return validateBuy(this, ' . $msmin . ',' . $msmax . ', ' . $custom_price 
                      . ', ' . $group_idx . ', ' . $ajax . ', \''. yak_get_blogurl() . '\')"' . $disabled_attr . $hover_title . '><span>' 
                      . str_replace(' ', '&nbsp;', __('Add to cart', 'yak')) . '</span></button></span>' 
                      . '<input type="hidden" name="buynow" value="' . $productpost->ID . '" />' 
                      . '<input type="hidden" name="buynow_param" value="' . $productpost_param_name . '" />' 
                      . '<input type="hidden" name="' . $param_name . '" value="' . $id .'" />';
                      
                // were there any messages when the button was pressed (set in yak_init)?
                $buynow_error_message = $GLOBALS['buynow_error_message_' . $productpost->ID];
                if (isset($buynow_error_message) && $buynow_error_message != '') {
                    $rtn .= $error_message_spacer . $buynow_error_message;   
                }
            }
            else if (yak_get_option(SHOW_OUT_OF_STOCK_MSG, 'no') == 'yes') {
                $rtn .= '<br />(' . yak_get_option(CUSTOM_OUT_OF_STOCK_MSG, __('Currently out of online stock', 'yak')) . ')';
            }
        }
        
        return $rtn;
    }
}


if (!function_exists('yak_buy_end_tag')) {
    /**
     * [yak_buy_end]
     *
     * Output the the end of the buy button (close the form).
     */
    function yak_buy_end_tag($attrs = null) {
        return '</form>';
    }
}


if (!function_exists('yak_cancelorder_tag')) {
    /**
     * [yak_cancelorder]
     *
     * Cancel an order
     */    
    function yak_cancelorder_tag($attrs) {
        if (isset($_SESSION['order_id'])) {
            yak_admin_cancel_order($_SESSION['order_id']);
            yak_cleanup_after_order();
        }
    	return "";
    }
}


if (!function_exists('yak_checkout_tag')) {
    /**
     * Display the checkout page.
     */
    function yak_checkout_tag($attrs) {
        global $post, $model, $credit_card_payments; 
        
        $param_name = yak_get_post_param();
        
        // get the payment type if set
        if (isset($_POST[PAYMENT_TYPE])) {
            $_SESSION[PAYMENT_TYPE] = $_POST[PAYMENT_TYPE];   
        }
        
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        $ptype = $_SESSION[PAYMENT_TYPE];        
        $ptypeval = $payment_types[$ptype];
        
        $items = $_SESSION[ITEMS_NAME];
        
        $_SESSION['checkout_param'] = $param_name;
        $_SESSION['checkout_param_val'] = $_REQUEST[$param_name];
        
        $action = $_REQUEST['action'];

        // update button was hit, or address confirmation        
        if ($action == 'update' || $action == 'address') {        
            $order_value = 0.0;
            $order_items = 0;
            if (isset($items) && $items != null) {
                foreach ($items as $key => $item) {
                    $itemkey = 'item_' . $key;
                    
                    $_POST[$itemkey] = round($_POST[$itemkey]);
                    
                    if (!isset($_POST[$itemkey]) || empty($_POST[$itemkey]) || $_POST[$itemkey] == 0) {
                        $items[$key] = NULL;
                        unset($items[$key]);
                    }
                    else {
                        $qty = $_POST[$itemkey];
                        
                        if (!is_numeric($qty) || $qty < 0) {
                            $items[$key] = NULL;
                            unset($items[$key]);
                            continue;
                        }
                        
                        $order_items += $qty;
                        $items[$key]->quantity = $qty;
                        $items[$key]->item_weight = yak_get_product_weight($items[$key]->id, $items[$key]->cat_id);
                        $order_value += (yak_calc_price($items[$key]->id, $items[$key]->cat_id, $items[$key]->price) * $qty);
                    }
                    
                    if ($order_items == 0) {
                        unset($_SESSION['current_order_items']);
                        unset($_SESSION['current_order_value']);
                    }
                    else {
                        $_SESSION['current_order_items'] = $order_items;
                        $_SESSION['current_order_value'] = $order_value;
                    }
                }
            }
        }
    
        // reset the items in the session
        $_SESSION[ITEMS_NAME] = $items;
        
        // start building the 'model' (data used in the next screenflow)
        $model = array('items' => $items, 'param_name' => $param_name, 'post_id' => $post->ID);
        
        if (isset($GLOBALS['buynow_error_message'])) {
            return $GLOBALS['buynow_error_message'];
        }
        else if (!isset($_SESSION[ITEMS_NAME]) || sizeof($items) < 1) {
            // drop out if no items found in the session
            $msg = stripslashes(yak_get_option(EMPTY_BASKET_MESSAGE, ''));
            
            if (empty($msg)) {
                return __('You have no items in your shopping basket', 'yak');
            }
            else {
                return $msg;
            }
        }
    
        if ($action == 'update' || $action == 'address') {
            require_once('yak-promo-utils.php');
            
            if (!empty($_POST['promo_code'])) {
                $promo_code = $_POST['promo_code'];
                $promos = yak_get_promotions($promo_code, true);
                if (sizeof($promos) > 0) {
                    if (!yak_allowed_promo($promos[0])) {
                        if (!empty($_REQUEST['error_message'])) {
                            $model['error_message'] = '<p>' . $_REQUEST['error_message'] . '</p>';
                        }
                        else {
                            $model['error_message'] = '<p>' . __("You don't have access to this promotion code", 'yak') . '</p>';
                        }
                        $action = 'cart';
                    }
                    else {
                        $_SESSION['promo_code'] = $promo_code;
                    }
                }
                else {
                    $model['error_message'] = '<p>' . __('Invalid promotion code', 'yak') . '</p>';
                    $action = 'cart';
                }
            }
            else {
                unset($_SESSION['promo_code']);
            }
        }
    
        // default to displaying the first cart screen
        if ($action == 'update' || empty($action)) {
            $action = 'cart';            
        }
        
        // store the address data in the model
        if ($action == 'address' || $action == 'jump-to-address') {
            $model['shipping_address'] = yak_get_address('shipping');
            $model['billing_address'] = yak_get_address('billing');
            $model[SHIPPING_OPTIONS] = yak_get_shipping_options($ptype);
            $action = 'address';
        }
        
        if ($action == 'jump-to-payment') {
            $action = null;
            $next_page = yak_get_next_payment_page($ptypeval);
            if (!empty($next_page)) {
                // special case for Credit Card payments
                if (yak_str_contains($next_page, '-cc')) {
                    $action = 'cc';
                }
            }
        }
        else if ($action == 'confirm') {
            $errors = yak_validate_address('shipping');
            
            if (yak_checkout_error($model, __('Error(s) have occurred validating the shipping address information you provided', 'yak'), $errors)) {
                // drop back to display the address
                $action = 'address';
            }
            else {
                $model['shipping_address'] = yak_get_address('shipping');
                $model['billing_address'] = yak_get_address('billing');
                $model[SHIPPING_OPTIONS] = yak_get_shipping_options($ptype);
                $model[SELECTED_SHIPPING_OPTION] = $_POST[SELECTED_SHIPPING_OPTION];
                
                $next_page = yak_get_next_payment_page($ptypeval);
                if (!empty($next_page)) {
                    // special case for Credit Card payments
                    if (yak_str_contains($next_page, '-cc')) {
                        $action = 'cc';
                    }
                }
            }
        }
        else if ($action == 'confirm_cc') {            
            // we've come from credit card form, so process accordingly
            
            $errors = array();
            
            if (!check_credit_card($_POST['cc_number'], $_POST['cc_type'], $errornumber, $errortext)) {
                $errors[] = $errortext;
            }
            
            if (mktime(0, 0, 0, $_POST['cc_expiry_month'], date('d'), $_POST['cc_expiry_year']) < mktime()) {
                $errors[] = __('Credit card has already expired', 'yak');   
            }
            
            if (empty($_POST['cc_name'])) {
                $errors[] = __('No cardholder name provided', 'yak');
            }
            
            if (empty($_POST['cc_security_code'])) {
                $errors[] = __('No security code provided', 'yak');
            }
            
            if (yak_checkout_error($model, __('Error(s) in your credit card details', 'yak'), $errors)) {
                $action = 'cc';
            }
            else {
                $model['shipping_address'] = yak_get_address('shipping');
                $model['billing_address'] = yak_get_address('billing');
                
                $_SESSION['cc'] = array('number' => $_POST['cc_number'],
                                        'security_code' => $_POST['cc_security_code'],
                                        'type' => $_POST['cc_type'],
                                        'name' => $_POST['cc_name'],
                                        'expiry' => $_POST['cc_expiry_month'] . '/' . $_POST['cc_expiry_year']);

                $action = 'confirm';
            }
        }
        else if ($action == 'confirm_accrecv') {
            // we've come from the accounts receivable form, so process accordingly
            
            $errors = array();
            
            if (empty($_POST['accrecv_number'])) {
                $errors[] = __('No account number provided', 'yak');
            }
            
            if (empty($_POST['accrecv_name'])) {
                $errors[] = __('No account name provided', 'yak');
            }
            
            if (yak_checkout_error($model, __('Error(s) in your account details', 'yak'), $errors)) {
                $action = 'accrecv';
            }
            else {
                $model['shipping_address'] = yak_get_address('shipping');
                $model['billing_address'] = yak_get_address('billing');
                
                $_SESSION['accrecv'] = array('number' => $_POST['accrecv_number'],
                                             'name' => $_POST['accrecv_name']);

                $action = 'confirm';
            }
        }
        else if ($action == 'confirm2') {
            // final confirmation caused an error, so redisplay confirmation screen
            // with the error message
            
            $model['error_message'] = '<p>' . $_POST['error_message'] . '</p>';
            
            $model['shipping_address'] = yak_get_address('shipping');
            $model['billing_address'] = yak_get_address('billing');
            
            $action = 'confirm';   
        }
        else if ($action == 'redirect_to_confirm') {
            $model['shipping_address'] = yak_get_address('shipping');
            $model['billing_address'] = yak_get_address('billing');
            $action = 'confirm';
        }
        
        // if payment type is credit card, redirect to credit card input form
        if ($action == 'cc') {
            global $cards;
            $card_types = yak_get_option('yak_cc_types');

            $model['cc_types'] = array();
            foreach ($cards as $name=>$card) {
                if (in_array($name, $card_types)) {
                    $model['cc_types'][$card['name']] = $card['name'];
                }   
            }
            
            $model['cc_expiry_months'] = yak_get_expiry_months();            
            $model['cc_expiry_years'] = yak_get_expiry_years();
            
            $action = 'cc';
        }
        
        if (!empty($_SESSION['promo_code'])) {
            $model['promo_code'] = $_SESSION['promo_code'];
        }
        
        ob_start();
        if (!empty($next_page)) {
            include $next_page;
        }
        else {
            include 'yak-view-' . $action . '.php';
        }
        $rtn = ob_get_contents();
        ob_end_clean();
    
        return $rtn;
    }
}


if (!function_exists('yak_cleanup_tag')) {
    /**
     * [yak_cleanup]
     *
     * cleanup after successful order
     */    
    function yak_cleanup_tag($attrs) {
        yak_cleanup_after_order();
    	return "";
    }
}


if (!function_exists('yak_customer_address_tag')) {
    function yak_customer_address_tag($attrs) {
        global $countries;
        
        $addr = yak_get_address('shipping');
        
        if (isset($addr) && !empty($addr)) {
            $saddr = $addr->as_string('recipient', 'country', 'email', 'phone');
            $saddr = str_replace("\n", "\n<br />", $saddr . "\n" . $countries[$addr->country]);
        
            return $saddr;
        }
        else {
            return "";
        }
    }
}

if (!function_exists('yak_customer_name_tag')) {
    function yak_customer_name_tag($attrs) {
        $addr = yak_get_address('shipping');
        
        if (isset($addr) && !empty($addr)) {
            return $addr->recipient;
        }
        else {
            return "";
        }
    }
}


if (!function_exists('yak_customer_phone_tag')) {
    function yak_customer_phone_tag($attrs) {
        $addr = yak_get_address('shipping');
        
        if (isset($addr) && !empty($addr)) {
            return $addr->phone;
        }
        else {
            return "";
        }
    }
}


if (!function_exists('yak_error_message_tag')) {
    /**
     * [yak_error_message]
     */
    function yak_error_message_tag($attrs) {
        return $_SESSION['error_message'];
    }
}


if (!function_exists('yak_get_remote_tag')) {
    /**
     * [yak_get_remote id="23"]
     */
    function yak_get_remote_tag($attrs) {
        $remote_server = yak_get_option(REMOTE_GRAB_SERVER);
        $remote_path = yak_get_option(REMOTE_GRAB_PATH);

        extract(shortcode_atts(array(
    		'id' => null
    	), $attrs));        

        if (empty($remote_server)) {
            return "REMOTE SERVER NOT SET";
        }
        else if ($id == null) {
            return "REMOTE ID NOT SPECIFIED";
        }
        else {
            $response = yak_do_http($remote_server, $remote_path, 'p='. $id . '?&feed=yak-products', null, 'GET');
                
            $start_tag = '<content><![CDATA[';
            $end_tag = ']]></content>';
            $rpos = strpos($response, $start_tag);
            $rpos2 = strpos($response, $end_tag, $rpos);
                
            return substr($response, $rpos + strlen($start_tag), $rpos2 - ($rpos + strlen($start_tag)));
        }
    }
}


if (!function_exists('yak_google_analytics_tag')) {
    function yak_google_analytics_tag($attrs) {
        $profile_id = yak_get_option(GOOGLE_ANALYTICS_ID, '');
    
        if (!empty($profile_id)) {
            $order_num = yak_order_id_tag(array());
            $affiliation = yak_get_option(GOOGLE_ANALYTICS_AFFILIATION, '');
            $tax_calc = yak_get_option(GOOGLE_ANALYTICS_TAX_CALC, 0);

            $region = $addr->region;
            if (empty($region)) {
                $region = $addr->state;
            }
        
            $orders = yak_get_orders(null, $order_num, null, null, false, false);
            $order = $orders[0];
            $addr = yak_get_address('shipping');
        
            $tax = $orders->total * $tax_calc;
        
            $content = "
            <script type=\"text/javascript\">
                pageTracker._addTrans(\"$order_num\", \"$affiliation\", \"$order->total\", \"$tax\", \"$order->shipping_cost\", \"$addr->city\", \"$region\", \"$addr->country\");";

            foreach ($order->items as $item) {
                // this bit of naffness is so I don't have to do another query
                // to get the product name and category
                if (yak_str_contains($item->itemname, '(')) {
                    $category = yak_get_tag_value($item->itemname, '(', ')');
                    $prodname = substr($item->itemname, 0, strpos($item->itemname, '('));
                }
                else {
                    $category = '';
                    $prodname = $item->itemname;
                }
            
                $sku = yak_get_sku($item->post_id, $item->cat_id, $item->sku);
            
                $content .= "
                pageTracker._addItem(\"$order_num\", \"$sku\", \"$prodname\", \"$category\", \"$item->price\", \"$item->quantity\");";
            }
          
            $content .= "
                pageTracker._trackTrans();
            </script>";
        }
        else {
            $content = '';
        }
        return $content;
    }
}


if (!function_exists('yak_order_id_tag')) {
    /**
     * [yak_order_id]
     *
     * Get the id from the GET or POST request data
     */
    function yak_order_id_tag($attrs) {
		// special case on PayPal return.  Lookup the order number based on the order id
		// we sent to PayPal
		if (isset($_GET['cm'])) {
			$order_id = yak_get_order_num($_GET['cm']);
		}
		else {
			// otherwise, default processing to display the order id in the GET or POST request.
        	$order_id = $_GET['order_id'];
        	if (empty($order_id)) {
            	$order_id = $_POST['order_id'];
        	}
        }
        return $order_id;
    }
}


if (!function_exists('yak_order_value_tag')) {
    /**
     * [yak_order_value]
     */
    function yak_order_value_tag($attrs) {
        return yak_order_value(false);
    }
}


if (!function_exists('yak_order_tracker_tag')) {
    /**
     * [yak_ordertracker]
     *
     * embed a customer order tracker in the page
     */
    function yak_order_tracker_tag($attrs) {
        global $user_ID, $wpdb, $order_log_table, $post;
    
        if (empty($user_ID) || $user_ID < 1) {
            echo '<p>You must <a href="' . esc_url(wp_login_url($_SERVER['REQUEST_URI'])) . '">login</a> to view this page.</p>';
            return;
        }
    
        $order_num = $_REQUEST['order_num'];
    
        echo '<form name="orderTracker" method="post" action="' , yak_get_permalink() , '">';
    
        $orders = yak_get_orders(null, null, null, null, false, false, false, null, $user_ID);
    
        echo '<select name="order_num">';    
    
        foreach ($orders as $order) {
            echo '  <option value="' . $order->order_num . '" ';
        
            if ($order_num == $order->order_num) {
                echo 'selected="selected"';
            }
        
            echo '>' . mysql2date(get_option('date_format'), $order->time ) . ' - ' . $order->order_num . '</option>';
        }
    
        echo '</select>&nbsp;';
        echo '<button type="submit">→</button><br />';
    
        if (!empty($order_num)) {
            $orders = yak_get_orders(null, $order_num, null, null, false, false, true, null, $user_ID);
            if ($orders == null || count($orders) < 1) {
                echo '<p>You do not have access to this order</p>';
            }
            else {
                $order = $orders[0];
        
                $sql = $wpdb->prepare("select * 
                                       from $order_log_table l
                                       where l.order_id = %d
                                       and (l.message = 'Stock sent'
                                       or l.message like 'Sent download%%')
                                       order by time desc
                                       limit 1", $order->id);
                if (defined('YAK_DEBUG')) {
                    yak_log("SQL: $sql");
                }
                $log = $wpdb->get_row($sql);
            
                $msg = yak_get_option('yak_ordertracker_' . str_replace(' ', '_', $order->status), $order->status);
                $msg = str_replace('[yak_shipping_date]', $log->time, $msg);
        
                echo "
                <table>
                    <tr>
                        <td>
    <strong>Order Date:</strong><br />
    Order date: $order->time
                        </td>
                    </tr>
                    <tr>
                        <td>
    <strong>Order Status:</strong><br />
    $msg
                        </td>
                    </tr>
                    <tr>
                        <td>
    <strong>Shipping Details:</strong><br />";

                if ($order->selected_shipping_type != 'default') {
                    echo "Selected Shipping: $order->selected_shipping_type <br />";
                }
            
            echo str_replace("\n", '<br />', $order->get_shipping_address_string());
            echo "      </td>
                    </tr>
                    <tr>
                        <td>
    <strong>Order Details:</strong><br />
    Payment Type: $order->payment_type <br />";

            $detail = '<table>
                        <tr>
                            <th>' . __('Item', 'yak') . '</th>
                            <th class="yak_numeric">' . __('Price', 'yak') . '</th>
                            <th class="yak_numeric">' . __('Qty', 'yak') . '</th>
                            <th class="yak_numeric">' . __('Subtotal', 'yak') . '</th>
                        </tr>';
            $total = $order->shipping_cost;
            foreach ($order->items as $item) {
                $total += $item->total;
                $detail .= '<tr>
                                <td class="yak_left">' . $item->itemname . '</td>
                                <td class="yak_numeric">' . yak_format_money($item->price) . '</td>
                                <td class="yak_numeric">' . $item->quantity . '</td>
                                <td class="yak_numeric">' . yak_format_money($item->total) . '</td>
                            </tr>';
            }
            $detail .= '</table>';

            echo "
    Total cost: " . yak_format_money($total) . "<br /><br />
    $detail
                        </td>
                    </tr>
                </table>";
            }
        }
        
        foreach ($_GET as $name=>$val) {
            echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
        }
        
        echo "</form>";
    }
}


if (!function_exists('yak_price_tag')) {
    /**
     * [yak_price id="23" type="large" discount="true"]
     *
     * embed the product price (type, id, and discount are optional) -- discount defaults to true
     */
    function yak_price_tag($attrs = null) {
        $pp = yak_get_product(null, true);
    
    	extract(shortcode_atts(array(
    		'id' => null,
    		'type' => null,
    		'discount' => "true"
    	), $attrs));

        if ($id == null) {
	        $id = $pp->ID;
	    }

        if ($discount == "true") {
            $discount = true;
        }
        else {
            $discount = false;
        }

    	return '<span class="yak_price">' . yak_price($id, $type, $discount, false) . '</span>';
    }
}


if (!function_exists('yak_product_page_tag')) {
    /**
     * [yak_product_page]
     *
     * Create a page of all products.
     */
    function yak_product_page_tag($attrs) {
        global $product_page, $post;
        
        $id = $post->ID;
        
        $pagesize = yak_get_option(PRODUCT_PAGE_SIZE, 10);
        
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];   
        }
        else {
            $offset = 0;
        }
        
        $product_page = true;
        $products = yak_get_products('date', 'desc', null, $offset, $pagesize + 1);
    
        $s = '';
        
        $count = 0;
        foreach ($products as $ppost) {
            $count += 1;

            if ($count > $pagesize) {
                break;
            }           
            
            global $yak_post;
            $yak_post = $ppost;
            
            $s .= '<div class="yak_product"><h3><a href="' . apply_filters('the_permalink', get_permalink($ppost->id)) . '">' . apply_filters('the_title', $ppost->post_title) . '</a></h3>';
            
            if (yak_str_contains($ppost->content->post_content, '<!--more-->')) {
                $content = explode('<!--more-->', $ppost->content->post_content, 2);
                $content = $content[0];
            }
            else {
                $content = $ppost->content->post_content;   
            }
            
            $ppostc = $ppost->content;
            $yak_post = $ppostc;
            $s .= apply_filters('the_content', $content);
            
            $s .= '</div>';
        }
        
        $s .= '<br />';
        
        $url = apply_filters('the_permalink', yak_get_blogurl() . '?' . yak_get_post_param() . '=' . $id);
        
        $off1 = $offset - $pagesize;
        $off2 = $offset + $pagesize;
        
        $total_products = yak_get_product_count();
        $pages = $total_products / $pagesize;
        
        if ($pages > 1) {
            $s .= "<center>";
        
            if ($offset > 0) {
                $s .= '<a href="' . $url . '&offset=' . $off1 . '">' . __('&lt;&lt; Previous', 'yak') . '</a>';   
            }
        
            if ($pages > 0) {
                if ($offset > 0) {
                    $s .= ' | ';   
                }
                for ($p = 0; $p < $pages; $p++) {
                    $page_offset = $p * $pagesize;
                    if ($page_offset != $offset) {
                        $s .= '<a href="' . $url . '&offset=' . $page_offset . '">' . ($p + 1) . '</a>';
                    }
                    else {
                        $s .= ($p + 1);   
                    }
                    if ($p < $pages - 1) {
                        $s .= ' | ';   
                    }
                }
            }
        
            if ($count > $pagesize) {
                if ($offset > 0 || $pages > 0) {
                    $s .= ' | ';   
                }
                $s .= '<a href="' . $url . '&offset=' . $off2 . '">' . __('Next &gt;&gt;', 'yak') . '</a>';
            }
        
            $s .= "</center>";
        }
        
        $product_page = false;
        
        return $s;
    }
}


if (!function_exists('yak_quantity_tag')) {
    /**
     * [yak_quantity id="23" type="large"]
     *
     * return the available quantity of a product (type and id are both optional)
     */
    function yak_quantity_tag($attrs) {
        $pp = yak_get_product(null, true);
    
    	extract(shortcode_atts(array(
    		'id' => null,    	    
    		'type' => null
    	), $attrs));
    	
    	$count = count($attrs);
        if ($id == null && $count > 0) {
    	    $id = $attrs[0];
    	    if ($count > 1) {
    	        $type = $attrs[1];
    	    }
	    }
	    
	    if ($id == null) {
	        $id = $pp->ID;
	    }
	    
	    return yak_get_quantity($id, $type);
    }
}

if (!function_exists('yak_sku_tag')) {
    /**
     * [yak_sku id="23" type="large"]
     *
     * return the SKU of a product (type and id are both optional)
     */
    function yak_sku_tag($attrs) {
        $pp = yak_get_product(null, true);
    
    	extract(shortcode_atts(array(
    		'id' => null,    	    
    		'type' => 'default'
    	), $attrs));
    	
    	$count = count($attrs);
        if ($id == null && $count > 0) {
    	    $id = $attrs[0];
    	    if ($count > 1) {
    	        $type = $attrs[1];
    	    }
	    }
	    
	    if ($id == null) {
	        $id = $pp->ID;
	    }

    	$prod_type = yak_get_product_type($id, null, $type);
    	
    	$sku = null;
    	$cat_id = 0;
    	if (isset($prod_type)) {
    	    $sku = $prod_type->sku;
    	    $cat_id = $prod_type->cat_id;
    	}
    	
    	return yak_get_sku($id, $cat_id, $sku);
    }
}

if (!function_exists('yak_description_tag')) {
    /**
     * [yak_description id="23"]
     *
     * return the short description of a product (id is optional)
     */
    function yak_description_tag($attrs = null) {
    	extract(shortcode_atts(array(
    		'id' => null
    	), $attrs));

        if ($id != null) {
            $pp = yak_get_product($post_id);
        }
        else {
            $pp = yak_get_product(null, true);
        }

        if (isset($pp)) {
    	    return $pp->description;
    	}
    	else {
    	    return "";
    	}
    }
}

add_shortcode('yak_back_to_address', 'yak_back_to_address_tag');
add_shortcode('yak_back_to_cc', 'yak_back_to_cc_tag');
add_shortcode('yak_buy', 'yak_buy_tag');
add_shortcode('yak_buy_begin', 'yak_buy_begin_tag');
add_shortcode('yak_buy_content', 'yak_buy_content_tag');
add_shortcode('yak_buy_end', 'yak_buy_end_tag');
add_shortcode('yak_cancelorder', 'yak_cancelorder_tag');
add_shortcode('yak_checkout', 'yak_checkout_tag');
add_shortcode('yak_cleanup', 'yak_cleanup_tag');
add_shortcode('yak_customer_address', 'yak_customer_address_tag');
add_shortcode('yak_customer_name', 'yak_customer_name_tag');
add_shortcode('yak_customer_phone', 'yak_customer_phone_tag');
add_shortcode('yak_description', 'yak_description_tag');
add_shortcode('yak_error_message', 'yak_error_message_tag');
add_shortcode('yak_get_remote', 'yak_get_remote_tag');
add_shortcode('yak_google_analytics', 'yak_google_analytics_tag');
add_shortcode('yak_order_id', 'yak_order_id_tag');
add_shortcode('yak_order_value', 'yak_order_value_tag');
add_shortcode('yak_ordertracker', 'yak_order_tracker_tag');
add_shortcode('yak_price', 'yak_price_tag');
add_shortcode('yak_product_page', 'yak_product_page_tag');
add_shortcode('yak_quantity', 'yak_quantity_tag');
add_shortcode('yak_sku', 'yak_sku_tag');
?>