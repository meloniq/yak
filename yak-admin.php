<?php
/*
See yak-for-wordpress.php for information and license terms
*/

if (!function_exists('yak_add_rewrite_rules')) {
    /**
     * add rewrite rules for the product feed.
     */
    function yak_add_rewrite_rules($wp_rewrite) {
        $new_rules = array(
            'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index(1)
        );
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}


if (!function_exists('yak_create_product_feed')) {
    /**
     * create a xml product feed (this is used for installations which need remote
     * products -- i.e. 2 yaks which share product info)
     */
    function yak_create_product_feed() {
        global $post;
        header("Content-Type: text/xml");
        echo "
<inventory>
";

        while (have_posts()) {
            the_post();
            $url = yak_get_blogurl();
            $content = get_the_content($more_link_text, $stripteaser, $more_file);
        	$content = apply_filters('the_content', $content);
        	$content = str_replace(']]>', ']]&gt;', $content);

            $action = yak_get_tag_value($content, 'action="', '"');
            if ($action != null) {
                $new_action = yak_overlap($url, $action);
                $content = str_replace($action, $new_action, $content);
            }
    
            $content = str_replace('<button id="addbutton"', '<input type="hidden" name="check_referrer" value="true" /><button id="addbutton"', $content);
    
            echo "
<item id=\"$post->ID\">
<title>$post->post_title</title>
<content><![CDATA[";

echo $content;

echo "]]></content>
<test></test>
</item>
";
        }

        echo "</inventory>";
    }
}


if (!function_exists('yak_add_feeds')) {
    /**
     * hooks into WP to add the product feed and the rewrite rules to support it.
     */
    function yak_add_feeds() {
        global $wp_rewrite;
        add_feed('yak-products', 'yak_create_product_feed');
        add_action('generate_rewrite_rules', 'yak_add_rewrite_rules');
        $wp_rewrite->flush_rules();
    }
}

if (!function_exists('yak_admin')) {
    /**
     * used by the admin panel hook
     */
    function yak_admin() {
        load_plugin_textdomain('yak-admin', 'wp-content/plugins/yak-for-wordpress/lang');
        
        if (current_user_can('view_yak_admin')) {
            add_menu_page('YAK', 'YAK', 'view_yak_orders', 'yak', 'yak_admin_orders');
        
            $orders_text = __('Orders', 'yak-admin');
            $products_text = __('Products', 'yak-admin');
            $sales_reports_text = __('Sales Reports', 'yak-admin');
        
            add_submenu_page('yak', $orders_text, $orders_text, 'view_yak_orders', 'yak', 'yak_admin_orders');
            add_submenu_page('yak', $products_text, $products_text, 'edit_posts', 'yak-view-products', 'yak_admin_products');
            add_submenu_page('yak', $sales_reports_text, $sales_reports_text, 'view_yak_sales_reports', 'yak-view-reports', 'yak_admin_reports');
        
            $general_options = __('General Options', 'yak-admin');
            $shipping_options = __('Shipping Options', 'yak-admin');
            $misc_options = __('Misc Options', 'yak-admin');
        
            add_submenu_page('yak', $general_options, $general_options, 'view_yak_settings', 'yak-general-options', 'yak_admin_general_options');
            add_submenu_page('yak', $shipping_options, $shipping_options, 'view_yak_settings', 'yak-shipping-options', 'yak_admin_shipping_options');
            add_submenu_page('yak', $misc_options, $misc_options, 'view_yak_settings', 'yak-misc-options', 'yak_admin_misc_options');
            do_action('yak-options-panels');
        }
    }
}

if (!function_exists('yak_admin_general_options')) {
    /**
     * Update options, and load the data for presenting the options screen.
     */
    function yak_admin_general_options() {
        global $wpdb, $countries, $promo_table, $promo_users_table;
        
        require_once('yak-promo-utils.php');
        
        if (current_user_can('edit_yak_settings')) {
            // update basic settings
            if (isset($_POST['options_update2'])) {
                yak_admin_options_set(MAINTENANCE_MODE, 'off');
                yak_admin_options_set(SHOW_OUT_OF_STOCK_MSG);
                yak_admin_options_set(CUSTOM_OUT_OF_STOCK_MSG);
                yak_admin_options_set(CONFIRMATION_EMAIL_ADDRESS);
                yak_admin_options_set(CONFIRMATION_SUBJECT);
                yak_admin_options_set(CONFIRMATION_MESSAGE);
                yak_admin_options_set(DEFAULT_SPECIAL_INSTRUCTIONS);
                yak_admin_options_set(TERMS_AND_CONDITIONS);
                yak_admin_options_set(EMPTY_BASKET_MESSAGE);
                yak_admin_options_set(REDIRECT_ON_BUY_TO);
                yak_admin_options_set(DISPLAY_PRODUCT_OPTIONS, 'off');
                yak_admin_options_set(PRODUCT_CATEGORY_NAME);
                yak_admin_options_set(PRODUCT_PAGE_SIZE);
                yak_admin_options_set(ORDER_NUMBER_TYPE);
                yak_admin_options_set(USE_SSL, 'off');
                yak_admin_options_set(HIDDEN_LINK, 'off');
                yak_admin_options_set(DUPLICATE_HANDLING);
                yak_admin_options_set(AJAX_BUY_BUTTON, 'off');
                yak_admin_options_set(HIDE_UPDATE_BUTTON, 'off');
            
                if (!empty($_POST[TEST_CONFIRMATION_EMAIL])) {
                    require_once('yak-payment-utils.php');
                    yak_test_confirmation_email();
                }
            }
         
            // update price & quantity settings if necessary
            if (isset($_POST['options_update3'])) {
                yak_admin_options_set(AUTO_DISCOUNT);            
                yak_admin_options_set(SELECTED_CURRENCY);
                yak_admin_options_set(PRICE_ROUNDING);
                yak_admin_options_set(HIDE_QUANTITY, 'off');
                yak_admin_options_set(QUANTITY_INPUT_SIZE);
                yak_admin_options_set(QUANTITY_INPUT_BUY_BUTTON, 'off');
            
                yak_admin_options_set(LOW_STOCK_THRESHOLD);
                yak_admin_options_set(LOW_STOCK_EMAIL);
            
                yak_admin_options_set(OPTIONS_DROPDOWN_INCLUDE_PRICE, 'off');
                yak_admin_options_set(UNLIMITED_QUANTITY, 'off');
            }
        
            // update download settings if necessary
            if (isset($_POST['options_update4'])) {
                yak_admin_options_set(DOWNLOAD_URI);
                yak_admin_options_set(DOWNLOAD_EMAIL);
                yak_admin_options_set(DOWNLOAD_EMAIL_ADDRESS);
                yak_admin_options_set(DOWNLOAD_USE_XSENDFILE, 'off');
            }
         
            // update payment types
            if (isset($_POST['options_update5'])) {
                $payments = array();
                $payments_ci = array();
                $size = count($_POST['payment_type_names']);
                for ($i = 0; $i < $size; $i++) {
                    $name = $_POST['payment_type_names'][$i];
                    $redirect = $_POST['payment_type_redirects'][$i];
            
                    // if we're using manual credit card payment, force SSL on
                    // but allow no SSL for localhost (i.e. testing)
                    if ($redirect == CREDIT_CARD && !defined('YAK_DEBUG')) {
                        update_option(USE_SSL, 'on');
                    }
            
                    if (isset($name) && $name != '') {
                        $payments[$name] = $redirect;
                        $payments_ci[strtolower($name)] = $redirect;
                    }
                }
        
                update_option(PAYMENT_TYPES, $payments);
                update_option(PAYMENT_TYPES_CASE_INSENSITIVE, $payments_ci);

                do_action('yak-payment-apply-settings');
            }
        
            // advanced options
            if (isset($_POST['options_update6'])) {
                yak_admin_options_set(SPECIAL_OPTIONS_TEXT);
                yak_admin_options_set(NO_CACHE_PAGES, null, true);
                
                foreach ($_POST as $key=>$val) {
                    if (yak_str_contains($key, 'yak_ordertracker')) {
                        if (empty($val)) {
                            delete_option($key);
                        }
                        else {
                            yak_admin_options_set($key, $val);
                        }
                    }
                }

                yak_admin_options_set(HTTP_PROXY_URL);
                yak_admin_options_set(REMOTE_GRAB_SERVER);
                yak_admin_options_set(REMOTE_GRAB_PATH);
                yak_admin_options_set(BLACKLIST);
            }
        
            // update the promotions (if any)
            if (isset($_POST['options_update7'])) {
                $size = sizeof($_POST['promo_code']);
                for ($i = 0; $i < $size; $i++) {
                    $promo_id = $_POST['promo_id'][$i];
                    $promo_code = $_POST['promo_code'][$i];
                
                    if (empty($promo_id) && empty($promo_code)) {
                        continue;
                    }
                
                    $promo_description = $_POST['promo_description'][$i];
                    $promo_type = $_POST['promo_type'][$i];
                    $promo_value = $_POST['promo_value'][$i];
                    $promo_expiry = $_POST['promo_expiry'][$i];
                    $promo_users = $_POST['promo_users'][$i];
                    $promo_products = $_POST['promo_products'][$i];
                    $promo_products_inclusion = $_POST['promo_products_inclusion'][$i];
            
                    if (yak_str_contains($promo_type, 'threshold')) {
                        $threshold = $promo_code;
                    }
                    else {
                        $threshold = null;
                    }
                    
                    $apply_users = false;
                
                    if (!empty($promo_id) && empty($promo_code)) {
                        $sql = $wpdb->prepare("delete from $promo_users_table where promo_id = %d", $promo_id);
                        $wpdb->query($sql);
                        $sql = $wpdb->prepare("delete from $promo_table where promo_id = %d", $promo_id);
                        $wpdb->query($sql);
                    }
                    else if (!empty($promo_id)) {
                        $sql = "update $promo_table
                                set code = %s, promo_type = %s, description = %s, value = %s, products_inclusion = %d ";
                    
                        $args = array($promo_code, $promo_type, $promo_description, $promo_value, $promo_products_inclusion);
                    
                        if ($promo_expiry != null) {
                            $sql .= ", expiry_date = '%s' ";
                            $args[] = $promo_expiry;
                        }
                    
                        if ($threshold != null) {
                            $sql .= ", threshold = %f ";
                            $args[] = $threshold;
                        }
                        
                        if (!empty($promo_products)) {
                            $sql .= ", products = %s ";
                            $args[] .= $promo_products;
                        }
                    
                        $sql .= "where promo_id = %d";
                        $args[] = $promo_id;

                        $sql = $wpdb->prepare($sql, $args);
                        if (defined(YAK_DEBUG)) {
                            yak_log("Promo update sql: " . $sql);
                        }
                        $wpdb->query($sql);
                        $apply_users = true;
                    }
                    else if (!empty($promo_code)) {
                        $sql1 = "insert into $promo_table (code, promo_type, description, value, products_inclusion ";
                        $sql2 = "values (%s, %s, %s, %s, %s";
                        $args = array($promo_code, $promo_type, $promo_description, $promo_value, $promo_products_inclusion);
                    
                        if ($promo_expiry != null) {
                            $sql1 .= ", expiry_date";
                            $sql2 .= ", '%s'";
                            $args[] = $promo_expiry;
                        }
                    
                        if ($threshold != null) {
                            $sql1 .= ", threshold";
                            $sql2 .= ", %f";
                            $args[] = $threshold;
                        }
                        
                        if (!empty($promo_products)) {
                            $sql1 .= ", products";
                            $sql2 .= ", %s";
                            $args[] = $promo_products;
                        }
                    
                        $sql1 .= ") ";
                        $sql2 .= ")";
                    
                        $sql = $wpdb->prepare($sql1 . $sql2, $args);
                        if (defined(YAK_DEBUG)) {
                            yak_log("Promo insert sql: " . $sql);
                        }
                        $wpdb->query($sql);
                        $promo_id = $wpdb->insert_id;
                        $apply_users = true;
                    }
            
                    // specific users can be allowed access to a particular code
                    // in this case, split the comma separated list of usernames
                    // then look up via the user_nicename in WP_users    
                    if ($apply_users) {
                        $sql = $wpdb->prepare("delete from $promo_users_table where promo_id = %d", $promo_id);
                        $wpdb->query($sql);
                        if (!empty($promo_users)) {
                            $users = explode(',', $promo_users);
                            $users_size = count($users);
                            for ($j = 0; $j < $users_size; $j++) {
                                if (empty($users[$j])) {
                                    continue;
                                }
                                $sql = $wpdb->prepare("insert into $promo_users_table (promo_id, user_id)
                                                       select %d, ID
                                                       from $wpdb->users u
                                                       where u.user_nicename = lower(%s)", $promo_id, $users[$j]);
                                $wpdb->query($sql);
                            }
                        }
                    }
                }
            }
        }
        
        // list of pages for some dropdowns
        $pages = get_pages();
        
        // load data for display
        global $model;
        $model[MAINTENANCE_MODE] = yak_get_option(MAINTENANCE_MODE, 'off');
        $model[QUANTITY_INPUT_SIZE] = yak_get_option(QUANTITY_INPUT_SIZE, '3');
        $model[SELECTED_CURRENCY] = yak_get_option(SELECTED_CURRENCY, 'USD');
        $model[SHOW_OUT_OF_STOCK_MSG] = yak_get_option(SHOW_OUT_OF_STOCK_MSG, 'yes');
        $model[CUSTOM_OUT_OF_STOCK_MSG] = yak_get_option(CUSTOM_OUT_OF_STOCK_MSG, '');
        $model[CONFIRMATION_EMAIL_ADDRESS] = yak_get_option(CONFIRMATION_EMAIL_ADDRESS, '');
        $model[CONFIRMATION_SUBJECT] = yak_get_option(CONFIRMATION_SUBJECT, '');
        $model[CONFIRMATION_MESSAGE] = stripslashes(yak_get_option(CONFIRMATION_MESSAGE, ''));
        $model[ORDER_NUMBER_TYPE] = yak_get_option(ORDER_NUMBER_TYPE, GENERATED);
        $model[REDIRECT_ON_BUY_TO] = yak_get_option(REDIRECT_ON_BUY_TO, '');
        $model[AUTO_DISCOUNT] = yak_get_option(AUTO_DISCOUNT, '0.9');
        $model[PRICE_ROUNDING] = yak_get_option(PRICE_ROUNDING, '0');
        $model[UNLIMITED_QUANTITY] = yak_get_option(UNLIMITED_QUANTITY, 'off');
        $model[HIDE_QUANTITY] = yak_get_option(HIDE_QUANTITY, 'off');
        $model[DISPLAY_PRODUCT_OPTIONS] = yak_get_option(DISPLAY_PRODUCT_OPTIONS, 'off');
        $model[PRODUCT_CATEGORY_NAME] = yak_get_option(PRODUCT_CATEGORY_NAME, 'products');
        $model[PRODUCT_PAGE_SIZE] = yak_get_option(PRODUCT_PAGE_SIZE, '10');
        $model[USE_SSL] = yak_get_option(USE_SSL, 'off');
        $model[HIDDEN_LINK] = yak_get_option(HIDDEN_LINK, 'on');
        $model[DEFAULT_SPECIAL_INSTRUCTIONS] = stripslashes(yak_get_option(DEFAULT_SPECIAL_INSTRUCTIONS, ''));
        $model[TERMS_AND_CONDITIONS] = stripslashes(yak_get_option(TERMS_AND_CONDITIONS, ''));
        $model[EMPTY_BASKET_MESSAGE] = stripslashes(yak_get_option(EMPTY_BASKET_MESSAGE, ''));
        $model[DUPLICATE_HANDLING] = yak_get_option(DUPLICATE_HANDLING, 'error');
        $model[AJAX_BUY_BUTTON] = yak_get_option(AJAX_BUY_BUTTON, 'off');
        $model[HIDE_UPDATE_BUTTON] = yak_get_option(HIDE_UPDATE_BUTTON, 'off');
        $model[QUANTITY_INPUT_BUY_BUTTON] = yak_get_option(QUANTITY_INPUT_BUY_BUTTON, 'off');
        
        $model[LOW_STOCK_THRESHOLD] = yak_get_option(LOW_STOCK_THRESHOLD, '');
        $model[LOW_STOCK_EMAIL] = yak_get_option(LOW_STOCK_EMAIL, '');
        
        $model[OPTIONS_DROPDOWN_INCLUDE_PRICE] = yak_get_option(OPTIONS_DROPDOWN_INCLUDE_PRICE, 'off');
        
        $model[DOWNLOAD_URI] = yak_get_option(DOWNLOAD_URI, '');
        $model[DOWNLOAD_EMAIL] = stripslashes(yak_get_option(DOWNLOAD_EMAIL, ''));
        $model[DOWNLOAD_EMAIL_ADDRESS] = yak_get_option(DOWNLOAD_EMAIL_ADDRESS, '');
        $model[DOWNLOAD_USE_XSENDFILE] = yak_get_option(DOWNLOAD_USE_XSENDFILE, 'off');
        
        $model[PAYMENT_TYPES] = yak_get_option(PAYMENT_TYPES, null);
    
        $model[SPECIAL_OPTIONS_TEXT] = yak_get_option(SPECIAL_OPTIONS_TEXT, '');
        $model[NO_CACHE_PAGES] = yak_get_option(NO_CACHE_PAGES, array());
        $model[HTTP_PROXY_URL] = yak_get_option(HTTP_PROXY_URL, '');
        
        $model[ADDRESS_NAME] = yak_get_option(ADDRESS_NAME, 'on');
        $model[ADDRESS_COMPANY_NAME] = yak_get_option(ADDRESS_COMPANY_NAME, 'off');
        $model[ADDRESS_PHONE] = yak_get_option(ADDRESS_PHONE, 'on');
        $model[ADDRESS_SUBURB] = yak_get_option(ADDRESS_SUBURB, 'on');
        $model[ADDRESS_POSTCODE] = yak_get_option(ADDRESS_POSTCODE, 'on');
        $model[ADDRESS] = yak_get_option(ADDRESS, 'on');
        $model[ADDRESS_SEPARATE_BILLING] = yak_get_option(ADDRESS_SEPARATE_BILLING, 'on');
        
        $model[SHIPPING_NOTES] = yak_get_option(SHIPPING_NOTES, '');
        
        $model[REMOTE_GRAB_SERVER] = yak_get_option(REMOTE_GRAB_SERVER, '');
        $model[REMOTE_GRAB_PATH] = yak_get_option(REMOTE_GRAB_PATH, '');
        $model[BLACKLIST] = yak_get_option(BLACKLIST, '');
                
        $model[CATEGORIES] = array(); 
        foreach (get_categories('hide_empty=0') as $cat) {
            $model[CATEGORIES][$cat->category_nicename] = $cat->cat_name;
        }
        
        $model[PAGE_IDS] = array();
        $model[PAGES] = array();
        $model[PAGE_IDS][''] = '';
        $model[PAGES][''] = '';
        foreach ($pages as $page) {
            $model[PAGE_IDS][$page->ID] = $page->post_title;
            $model[PAGES]['?page_id=' . $page->ID] = $page->post_title;
        }
        
        $model[PROMOTIONS] = yak_get_promotions();
        
        // redirect to the settings screen
        include 'yak-view-general-options.php';
    }
}


if (!function_exists('yak_admin_shipping_options')) {
    /**
     * Update options, and load the data for presenting the shipping options screen.
     */
    function yak_admin_shipping_options() {
        global $wpdb, $countries;
        
        if (current_user_can('edit_yak_settings') && isset($_POST['options_update1'])) {
            yak_admin_options_set(INCLUDE_SHIPPING_COSTS);
            if (isset($_POST[COOKIE_LIFETIME])) {
                update_option(COOKIE_LIFETIME, $_POST[COOKIE_LIFETIME] * 24 * 60 * 60);
            }
            yak_admin_options_set(DEFAULT_COUNTRY);
            yak_admin_options_set(ADDRESS_NAME, 'off');
            yak_admin_options_set(ADDRESS_COMPANY_NAME, 'off');
            yak_admin_options_set(ADDRESS_PHONE, 'off');
            yak_admin_options_set(ADDRESS_SUBURB, 'off');
            yak_admin_options_set(ADDRESS_POSTCODE, 'off');
            yak_admin_options_set(ADDRESS, 'off');
            yak_admin_options_set(ADDRESS_SEPARATE_BILLING, 'off');
            yak_admin_options_set(SHIPPING_NOTES);
            yak_admin_options_set(SHIPPING_WEIGHT_CALC);
            yak_admin_options_set(SHIPPING_OPTIONS);
        
            $optnames = explode("\n", $_POST[SHIPPING_OPTION_NAMES]);
            $optcount = count($optnames);
            for ($i = 0; $i < $optcount; $i++) {
                $optnames[$i] = substr($optnames[$i], 0, 60);
            }
            update_option(SHIPPING_OPTION_NAMES, implode("\n", $optnames));
        }
        
        $shipping_option_names = yak_get_shipping_options();        
        
        if (current_user_can('edit_yak_settings') && isset($_POST['options_update2'])) {
            yak_admin_options_shipping_updzones();
        }
        
        // load the distinct set of zones
        $rows = $wpdb->get_results("select distinct option_value 
                                    from $wpdb->options 
                                    where option_name like 'yak_%_zone'
                                    and option_value is not null and option_value != ''
                                    order by option_value");
        $zones = array();
        foreach ($rows as $row) {
            $zones[] = $row->option_value;
        }
        
        if (current_user_can('edit_yak_settings')) {
            if (isset($_POST['options_update3'])) {
                foreach ($shipping_option_names as $shipping_option_name=>$ignore) {
                    $opt = new YakShippingOption($shipping_option_name);
                    foreach ($zones as $zone) {
                        $fixed = yak_get_shipping_varname($opt->code, $zone, 'fixed');
                        $fixeditemfirst = yak_get_shipping_varname($opt->code, $zone, 'fixeditemfirst');
                        $fixeditem = yak_get_shipping_varname($opt->code, $zone, 'fixeditem');
                        $weightfirst = yak_get_shipping_varname($opt->code, $zone, 'weightfirst');
                        $weight = yak_get_shipping_varname($opt->code, $zone, 'weight');
                        
                        yak_admin_options_set($fixed, null, false, false, 'no');
                        yak_admin_options_set($fixeditemfirst, null, false, false, 'no');
                        yak_admin_options_set($fixeditem, null, false, false, 'no');
                        yak_admin_options_set($weightfirst, null, false, false, 'no');
                        yak_admin_options_set($weight, null, false, false, 'no');
                    }
                }
            }

            // update payment-shipping pairs
            if (isset($_POST['options_update4'])) {
                $pairs = array();
                $pairs_ci = array();
                $size = count($_POST['payment_type_names']);
                for ($i = 0; $i < $size; $i++) {
                    $payment = $_POST['payment_type_names'][$i];
                    $shipping = $_POST['shipping_type_names'][$i];
                      
                    if (isset($payment) && $payment != '') {
                        $pairs[$payment][] = $shipping;
                        $pairs_ci[strtolower($payment)][] = $shipping;
                    }
                }

                update_option(PAYMENT_SHIPPING_PAIRS, $pairs);
                update_option(PAYMENT_SHIPPING_PAIRS_CASE_INSENSITIVE, $pairs_ci);
            }
        }
        
        // load data for display
        global $model;
        
        $model[COOKIE_LIFETIME] = yak_get_option(COOKIE_LIFETIME, '2592000') / (24*60*60);
        $model[INCLUDE_SHIPPING_COSTS] = yak_get_option(INCLUDE_SHIPPING_COSTS, 'yes');
        $model[DEFAULT_COUNTRY] = yak_get_option(DEFAULT_COUNTRY, 'thailand');
        $model[ADDRESS_NAME] = yak_get_option(ADDRESS_NAME, 'on');
        $model[ADDRESS_COMPANY_NAME] = yak_get_option(ADDRESS_COMPANY_NAME, 'off');
        $model[ADDRESS_PHONE] = yak_get_option(ADDRESS_PHONE, 'on');
        $model[ADDRESS_SUBURB] = yak_get_option(ADDRESS_SUBURB, 'on');
        $model[ADDRESS_POSTCODE] = yak_get_option(ADDRESS_POSTCODE, 'on');
        $model[ADDRESS] = yak_get_option(ADDRESS, 'on');
        $model[ADDRESS_SEPARATE_BILLING] = yak_get_option(ADDRESS_SEPARATE_BILLING, 'on');
        
        $model[SHIPPING_NOTES] = yak_get_option(SHIPPING_NOTES, '');
        
        $model[SHIPPING_WEIGHT_CALC] = yak_get_option(SHIPPING_WEIGHT_CALC, '');
        
        $model[SHIPPING_OPTION_NAMES] = yak_get_option(SHIPPING_OPTION_NAMES, '');
        
        foreach ($countries as $country=>$name) {
            $key = 'yak_' . $country . '_zone';
            $model[$key] = yak_get_option($key, '');
        }
        
        $model[SHIPPING_OPTIONS] = array();
        foreach ($shipping_option_names as $shipping_option_name=>$ignore) {
            $opt = new YakShippingOption($shipping_option_name);
            
            foreach ($zones as $zone) {
                $fixed = yak_get_shipping_varname($opt->code, $zone, 'fixed');
                $fixeditemfirst = yak_get_shipping_varname($opt->code, $zone, 'fixeditemfirst');
                $fixeditem = yak_get_shipping_varname($opt->code, $zone, 'fixeditem');
                $weightfirst = yak_get_shipping_varname($opt->code, $zone, 'weightfirst');
                $weight = yak_get_shipping_varname($opt->code, $zone, 'weight');
                
                $z = new YakShippingZone($zone, yak_get_option('yak_zone_' . $zone . '_countries', ''), 
                        yak_get_option($fixed), 
                        yak_get_option($fixeditemfirst), 
                        yak_get_option($fixeditem), 
                        yak_get_option($weightfirst), 
                        yak_get_option($weight));
                $opt->add_zone($z);
            }
            
            $model[SHIPPING_OPTIONS][] = $opt;
        }
        $model[PAYMENT_TYPES] = yak_get_option(PAYMENT_TYPES, null);
        $model[PAYMENT_SHIPPING_PAIRS] = yak_get_option(PAYMENT_SHIPPING_PAIRS, null);
        // redirect to the shipping settings screen
        include 'yak-view-shipping-options.php';
    }
}


if (!function_exists('yak_admin_misc_options')) {
    /**
     * Display and update misc admin options.
     */
    function yak_admin_misc_options() {
        global $wpdb, $model, $order_table, $address_table, $order_dl_table;

        require_once('yak-promo-utils.php');

        if (current_user_can('edit_yak_settings')) {
            if (isset($_POST['update-options-google-analytics'])) {
                yak_admin_options_set(GOOGLE_ANALYTICS_ID);
                yak_admin_options_set(GOOGLE_ANALYTICS_TAX_CALC);
                yak_admin_options_set(GOOGLE_ANALYTICS_AFFILIATION);   
            }
            
            if (isset($_POST['update-options-resenddl'])) {
                $action = $_REQUEST['yak-resenddl'];        
                $prod = explode(',', $_REQUEST['product']);
                $subject = $_REQUEST['subject'];
                $email = $_REQUEST['email'];

                $emails = array();
                if (!empty($action)) {
                    $sql = $wpdb->prepare("select *
                                           from wp_yak_product_detail
                                           where post_id = %d
                                           and cat_id = %d", $prod[0], $prod[1]);
                    $row = $wpdb->get_row($sql);

                    $dl_file = $row->dl_file;

                    $rows = yak_get_order_details($prod[0], $prod[1]);
                    if ($action != 'Check') {
                        foreach ($rows as $row) {
                            $sql = "insert into $order_dl_table (order_id, dl_file, download_attempts)
                                          values (" . $row->order_id . ", '$dl_file', 0)";
                            $wpdb->query($sql);

                            yak_send_dl_email($row->order_id, $row->email_address, stripslashes($subject), stripslashes($email));            
                        }

                        $_REQUEST['email_count'] = __y('Sending email to %s purchaser(s):', 'yak-admin', count($rows));
                    }
                    else {
                        $_REQUEST['email_count'] = __y('%s purchaser(s) will receive the update mail:', 'yak-admin', count($rows));
                    }
                }
            }
            
            foreach ($_POST as $key => $value) {
                if (!(strpos($key, "yak-update-options-") === false)) {
                    do_action($key);
                }
            }
        }
        
        // redirect to the advanced settings screen
        include 'yak-view-misc-options.php';
    }
}


if (!function_exists('yak_admin_options_shipping_updzones')) {
    /**
     * Update country shipping zones.
     */
    function yak_admin_options_shipping_updzones() {
        global $countries;
        
        $zones_list = array();
        $enabled_countries = array();
        foreach ($countries as $country=>$name) {
            $key = 'yak_' . $country . '_zone';
            $zone = $_POST[$key];
            update_option($key, $zone);

            if (!empty($zone)) {
                $enabled_countries[$country] = $name;

                if (!isset($zones_list[$zone])) {
                    $zones_list[$zone] = array();
                }
                $zones_list[$zone][] = $country;
            }
        }

        // build a comma-separated list of country codes for each zone
        foreach ($zones_list as $key=>$value) {
            $country_list = implode(', ', $value);
            update_option('yak_zone_' . $key . '_countries', $country_list);
        }

        update_option(ENABLED_COUNTRIES, $enabled_countries);
    }
}


if (!function_exists('yak_admin_orders')) {
    /**
     * Display the orders-admin panel for this plugin - and update orders accordingly when an admin makes changes
     */
    function yak_admin_orders() {
        global $wpdb, $model, $order_table, $address_table, $address_table, $order_log_table, $order_meta_table, $order_detail_table, $product_detail_table;
        
        $model['messages'] = array();
        
        // do the update of order details
        if (current_user_can('edit_yak_orders') && isset($_POST['orders_update'])) {
            $size = count($_POST['id']);
            for ($i = 0; $i < $size; $i++) {            
                $id = $_POST['id'][$i];
                $funds = $_POST['funds_received'][$i];
                $original_funds = $_POST['original_funds_received'][$i];
                $action = yak_default($_POST['action'][$i], '');
                $note = $_POST['note'][$i];
                
                if (yak_str_contains($funds, ',')) {
                    $funds = str_replace(',', '.', $funds);
                    $original_funds = str_replace(',', '.', $original_funds);
                }
                
                $status = null;
                
                $check_order = false;
                if ($action == 'payment_processed') {
                    yak_insert_orderlog($id, 'Payment processed');
                    yak_admin_update_order($id, PAYMENT_PROCESSED, $funds);
                    
                    $check_order = false;
                }
                else if ($action == 'send_stock') {
                    yak_insert_orderlog($id, 'Stock sent');
                    yak_admin_update_order($id, STOCK_SENT, $funds);
                    $check_order = true;
                }
                else if ($action == 'cancel_order') {
                    yak_admin_cancel_order($id, $funds);
                }
                else if ($action == 'delete') {
                    yak_admin_delete_order($id);
                }
                else if ($action == 'refund') {
                    yak_insert_orderlog($id, 'Order refunded');
                    yak_admin_update_order($id, REFUNDED, $funds);                    
                    $wipe_cc_details = true;
                }
                else if ($action == 'reset') {
                    yak_insert_orderlog($id, 'Order reset to unfulfilled');
                    yak_admin_update_order($id, 'reset', $funds);
                }
                else if ($funds != $original_funds) {
                    // just update the funds received if no status
                    yak_admin_update_order($id, null, $funds);
                }
                
                if (isset($note) && $note != '') {
                    yak_insert_orderlog($id, $note);   
                }
                
                if ($check_order) {
                    yak_check_order($id);   
                }
                
            }
        }
        
        // load data for display       
        $status = $_POST['status'];
        $order_year = $_POST['year_order_date'];
        $order_month = $_POST['month_order_date'];
        $payment_type = $_POST['payment_type'];
        $search = $_POST['search'];
        
        if (isset($_POST['orders_query']) || isset($_POST['orders_update'])) {
            $orders = yak_get_orders($status, null, $order_year, $order_month, true, true, true, null, null, $payment_type, $search);
            $model['orders'] = $orders;
            $model['order_year'] = $order_year;
            $model['order_month'] = $order_month;
        }
        
        include 'yak-view-orders.php';
    }
}


if (!function_exists('yak_admin_products')) {
    /**
     * Display the products-admin panel for this plugin - and update products accordingly when an admin makes changes
     */
    function yak_admin_products() {
        global $wpdb, $product_detail_table;
        
        $upd = isset($_POST['products_update']);
        
        $products = yak_get_products();
        
        reset($products);
        while (list($key, $value) = each($products)) {       
            $product =& $products[$key];
        
            $types = yak_get_product_categories($product->id, $product->status, true, true);
        
            if ($upd) {
                $title = $_POST['title_' . $product->id];
                $price = $_POST['price_' . $product->id];
                $discount_override = $_POST['discount_override_' . $product->id];
                $multi_select_options = $_POST['multi_select_' . $product->id];
                $multi_select_min = $_POST['multi_select_min_' . $product->id];
                $multi_select_max = $_POST['multi_select_max_' . $product->id];
                $multi_select_cols = $_POST['multi_select_cols_' . $product->id];
                $require_login = $_POST['require_login_' . $product->id];
                $description = $_POST['description_' . $product->id];
                $custom_price = $_POST['custom_price_' . $product->id];
                
                $product->title = $title;
                $product->price = $price;
                $product->discount_override = $discount_override;
                $product->multi_select_options = $multi_select_options;
                $product->multi_select_min = $multi_select_min;
                $product->multi_select_max = $multi_select_max;
                $product->multi_select_cols = $multi_select_cols;
                $product->description = $description;
                if ($require_login == 'on') {
                    $product->require_login = true;
                }
                else {
                    $product->require_login = false;
                }
                if ($custom_price == 'on') {
                    $product->custom_price = true;
                }
                else {
                    $product->custom_price = false;                    
                }
                
                yak_update_product($product->id, $price, $title, $discount_override, $multi_select_options, $multi_select_min, 
                    $multi_select_max, $multi_select_cols, $require_login, $description, $custom_price);
                
                $types = yak_update_product_types($product->id, $types);
            }
            
            $product->types = $types;
        }
        
        unset($product);
        
        global $model;
        $model['products'] = $products;
        
        include 'yak-view-products.php';
    }
}


if (!function_exists('yak_admin_reports')) {
    /**
     * Display the reports admin panel
     */
    function yak_admin_reports() {
        global $wpdb, $order_table, $order_detail_table;
        
        global $model;
        $model = array();

        $year = $_REQUEST['year'];
        
        if (!isset($year) || $year == '') {
            $year = date('Y');   
        }
        $model['year'] = $year;
        
        $year_start = $year . '-01-01';
        $year_end = $year . '-12-31';
        
        // query for yearly total
        $sql = $wpdb->prepare("select sum(o.shipping_cost + (od.price * od.quantity)) as total 
                               from $order_table o, $order_detail_table od 
                               where o.id = od.id 
                               and o.time >= %s
                               and o.time <= %s
                               and o.status = 'STOCK SENT'", $year_start, $year_end);
        $row = $wpdb->get_row($sql);
        $model['year_total'] = $row->total;
        
        $sql = $wpdb->prepare("select distinct year(time) as yr from $order_table order by time desc");
        $rows = $wpdb->get_results($sql);
        $years = array();
        foreach ($rows as $row) {
            $years[] = $row->yr;
        }
        $model['years'] = $years;
        
        // query for monthly totals
        $sql = $wpdb->prepare("select month(o.time) as mth, sum(o.shipping_cost + (od.price * od.quantity)) as total 
                               from $order_table o, $order_detail_table od 
                               where o.id = od.id 
                               and o.time >= %s 
                               and o.time <= %s
                               and o.status = 'STOCK SENT'
                               group by month(time) 
                               order by month(time)", $year_start, $year_end);
        $rows = $wpdb->get_results($sql);
        $monthly_totals = array();
        foreach ($rows as $row) {
            $mt = new YakTotal($row->mth, $row->total);
            $monthly_totals[] = $mt;
        }
        $model['monthly_totals'] = $monthly_totals;

        // query for top sellers for the year
        $sql = $wpdb->prepare("select od.post_id, od.cat_id, count(*) as total_sold, sum(o.shipping_cost + (od.price * od.quantity)) as total_value
                               from $order_detail_table od, $order_table o 
                               where od.id = o.id
                               and o.time >= %s
                               and o.time <= %s
                               and o.status = 'STOCK SENT'
                               group by post_id, cat_id 
                               order by total_sold desc, total_value desc
                               limit 5", $year_start, $year_end);
        $rows = $wpdb->get_results($sql);
                                    
        $year_best = array();
        foreach ($rows as $row) {
            $total = new YakTotal(yak_get_title($row->post_id, $row->cat_id), $row->total_sold, $row->total_value);
            $year_best[] = $total;
        }
        $model['year_best'] = $year_best;
        
        // query for top sellers for the month
        $sql = $wpdb->prepare("select month(o.time) as mth, monthname(o.time) as mthname, od.post_id, od.cat_id, count(*) as total_sold, sum(o.shipping_cost + (od.price * od.quantity)) as total_value
                                    from $order_detail_table od, $order_table o 
                                    where od.id = o.id
                                    and o.time >= %s
                                    and o.time <= %s
                                    and o.status in ('STOCK SENT', 'PAYMENT PROCESSED')
                                    group by mth, post_id, cat_id 
                                    order by mth asc, total_sold desc, total_value desc", $year_start, $year_end);
        $rows = $wpdb->get_results($sql);
                                    
        $month_best = array();
        foreach ($rows as $row) {
            if (!array_key_exists($row->mth, $month_best)) {
                $month_best['' . $row->mth] = array();
            }
             
            if (count($month_best['' . $row->mth]) >= 5) {
                continue;   
            }
            
            $total = new YakTotal($row->mthname, $row->total_sold, $row->total_value, yak_get_title($row->post_id, $row->cat_id));
            
            $month_best['' . $row->mth][] = $total;
        }
        $model['month_best'] = $month_best;
        
        include 'yak-view-reports.php';
    }
}


if (!function_exists('yak_admin_cancel_order')) {
    function yak_admin_cancel_order($order_id, $funds_received = null) {
        global $wpdb, $order_table, $order_detail_table, $product_detail_table;
        
        yak_insert_orderlog($order_id, 'Order cancelled');
        $wipe_cc_details = true;
        
        $sql = $wpdb->prepare("select post_id, cat_id, quantity from $order_detail_table where id = %d", $order_id);
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $result) {
            $pid  = $result->post_id;
            $cid = $result->cat_id;
            $oqty = $result->quantity;
            
            if (empty($pid)) {
                continue;
            }
            
            $sql = $wpdb->prepare("update $product_detail_table set quantity = quantity + %d where post_id = %d and cat_id = %d and quantity is not null",
                    $oqty, $pid, $cid);
                    
            if (defined('YAK_DEBUG')) {
                yak_log("SQL: $sql");
            }
            
            $wpdb->query($sql);
        }
        
        yak_admin_update_order($order_id, CANCELLED, $funds_received);
    }
}

if (!function_exists('yak_admin_delete_order')) {
    /**
     * Delete an order which is cancelled or in error. This is a proper delete, rather than
     * just a status change.
     */
    function yak_admin_delete_order($order_id) {
        global $wpdb, $model, $order_table;
        
        $sql = $wpdb->prepare("select status from $order_table where id = %d", $order_id);
        $row = $wpdb->get_row($sql);
        if ($row->status == CANCELLED || $row->status == ERROR) {
            $sql = $wpdb->prepare("update $order_table set deleted = 1 where id = %d", $order_id);
            $wpdb->query($sql);
        }
        else {
            $model['messages'][$order_id] = __('You cannot delete this order - it must be either CANCELLED or in ERROR', 'yak-admin');
        }
    }
}

if (!function_exists('yak_admin_update_order')) {
    function yak_admin_update_order($order_id, $status, $funds_received = null) {
        global $wpdb, $order_table, $order_meta_table;
        
        if ($status == null && $funds_received == null) {
            return;
        }
        else if ($status == 'reset') {
            $status = '';
        }
    
        $sqlparts = array();
        $arr = array();
        
        if ($status != null) {
            $sqlparts[] = "status = %s";
            $arr[] = $status;
        }
        else if ($status == '') {
            $sqlparts[] = "status = null";
        }
        
        if ($funds_received != null) {
            $sqlparts[] = "funds_received = %f";
            $arr[] = $funds_received;
        }
        
        $arr[] = $order_id;
        
        if (defined('YAK_DEBUG')) {
            yak_log("yak update order $order_id : status='$status' funds=$funds_received");
        }
        
        $sql = $wpdb->prepare("update $order_table set " . implode(',', $sqlparts) . " where id = %d", $arr);
        if (defined('YAK_DEBUG')) {
            yak_log("SQL: $sql");
        }
        $wpdb->query($sql);
        
        if ($status == STOCK_SENT || $status == PAYMENT_PROCESSED) {
            $sql = $wpdb->prepare("update $order_meta_table set value = '****************'
                                   where order_id = %d and name = 'CC number'", $order_id);
            $wpdb->query($sql);
        }
    }
}


if (!function_exists('yak_init_admin')) {
    function yak_init_admin() {
        $url = $_SERVER["REQUEST_URI"];
        if (yak_str_contains($_REQUEST['page'], 'yak') || yak_str_contains($url, 'post') || yak_str_contains($url, 'page'))
        {
            yak_init_resources(true);
        }
    }
}


if (!function_exists('yak_meta_box')) {
    function yak_meta_box() {
        add_meta_box('yak','YAK Product Details','yak_editproduct','post','advanced');
        add_meta_box('yak','YAK Product Details','yak_editproduct','page','advanced');
    }
}


add_action('admin_menu', 'yak_admin');
add_action('admin_init', 'yak_init_admin');
add_action('admin_menu', 'yak_meta_box');

// xml feeds
add_action('init', 'yak_add_feeds');
?>