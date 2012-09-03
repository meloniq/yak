<?php
/*
Plugin Name: YAK Add-on Module - Sales Tax
Description: Sales Tax add-on module for YAK-for-WordPress
Version: 3.3.7
Author: a filly ate it
Author URI: http://afillyateit.com
*/
define("ENABLE_SALES_TAX", "yak_enable_sales_tax");
define("DISPLAY_ZERO_TAX_CALC", "yak_display_zero_tax_calc");

if (file_exists(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-currencies.php')) {
    require_once(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-currencies.php');
}

function yak_admin_sales_tax() {
    global $states, $canada_states, $countries;
    
    // update basic options
    if (isset($_POST['options_update1'])) {            
        yak_admin_options_set(ENABLE_SALES_TAX, 'off');
        yak_admin_options_set(DISPLAY_ZERO_TAX_CALC, 'off');
    }
    else if (isset($_POST['options_update2'])) {
        yak_update_tax_option($countries, 'yak_cty_tax_');
    }
    else if (isset($_POST['options_update3'])) {
        yak_update_tax_option($states, 'yak_us_tax_');
    }
    else if (isset($_POST['options_update4'])) {
        yak_update_tax_option($canada_states, 'yak_ca_tax_');
    }
    
    include 'yak-view-sales-tax.php';
}

function yak_update_tax_option($zones, $prefix) {
    foreach ($zones as $zone=>$ignore) {
        $key = $prefix . $zone;
        $tax = $_POST[$key];
        
        if (!empty($tax)) {
            update_option($key, $tax);
        }
        else {
            delete_option($key);
        }
    }
}

function yak_sales_tax_options() {
    $salestax = __('Sales Tax', 'yak-admin');
    add_submenu_page('yak', $salestax, $salestax, 'view_yak_settings', 'yak-sales-tax-options', 'yak_admin_sales_tax');
}

function yak_calc_sales_tax($args) {
    if (yak_get_option(ENABLE_SALES_TAX, '') == 'on') {
        $state = $args['state'];
        $country = $args['country'];
        
        $calc = 0;
        if (!empty($state) && yak_get_option('yak_us_tax_' . $state, 0) > 0) {
            $calc = yak_get_option('yak_us_tax_' . $state);
        }
        else if (!empty($state) && yak_get_option('yak_ca_tax_' . $state, 0) > 0) {
            $calc = yak_get_option('yak_ca_tax_' . $state);
        }
        
        if (!empty($country) && yak_get_option('yak_cty_tax_' . $country, 0) > 0) {
            $calc += yak_get_option('yak_cty_tax_' . $country);
        }
        
        $total_price = $args['total_cost'];
        
        $price_rounding = yak_get_option(PRICE_ROUNDING, 0);
        $sales_tax = round($total_price * $calc, $price_rounding);
        
        $add_to_price =& $GLOBALS['yak-add-to-price'];
        $add_to_price[] = $sales_tax;
        
        return $sales_tax;
    }
    return 0.0;
}

function yak_sales_tax_display($args) {
    if (yak_get_option(ENABLE_SALES_TAX, '') == 'on') {
        $sales_tax = yak_calc_sales_tax($args);
        
        if ($sales_tax == 0 && yak_get_option(DISPLAY_ZERO_TAX_CALC, 'off') == 'off') {
            return;
        }
        
        $disp_label = __('Sales Tax', 'yak');
        $disp_sales_tax = yak_format_money($sales_tax);
        
        echo <<<EOD
        <tr>
            <td class="yak_left">$disp_label</td>
            <td></td>
            <td></td>
            <td class="yak_numeric" id="sales-tax">$disp_sales_tax</td>
        </tr>
EOD;
    }
}

function yak_sales_tax_order_confirm($args) {
    if (yak_get_option(ENABLE_SALES_TAX, '') == 'on') {
        $sales_tax = yak_calc_sales_tax($args);
        
        if ($sales_tax == 0) {
            return;
        }
        
        global $wpdb, $order_detail_table;
        
        $order_id = $args['order_id'];

        $sql = $wpdb->prepare("insert into $order_detail_table (id, itemname, price, quantity, product_type) 
                               values (%d, %s, %f, 1, %s)",
                $order_id, __('Sales Tax', 'yak'), $sales_tax, SALES_TAX_PRODUCT_TYPE);
                
        $items =& $args['items'];
        $sales_tax_item = new YakItem(null, null, null, 1, SALES_TAX_PRODUCT_TYPE);
        $sales_tax_item->price = $sales_tax;
        $items[] = $sales_tax_item;
                
        $wpdb->query($sql);
    }
}

add_action('yak-options-panels', 'yak_sales_tax_options');
add_action('yak-cart-confirm', 'yak_sales_tax_display');
add_action('yak-order-confirm', 'yak_sales_tax_order_confirm');
?>