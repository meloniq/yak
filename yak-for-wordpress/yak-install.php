<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once('yak-static.php');
require_once('yak-db.php');
require_once('yak-utils.php');

global $wpdb, $order_table, $order_detail_table, $order_detail_index, $order_num_index, $order_log_table, $order_meta_table, 
        $order_meta_index, $order_dl_table, $product_table, $product_detail_table, $coupon_table, $coupon_set_table, $coupon_set_index,
        $coupon_index, $promo_table, $promo_index, $promo_users_table, $address_table;

if (!function_exists('yak_column_exists')) {
    /**
     * Return true if a column exists on a table
     */
    function yak_column_exists($table, $column) {
        global $wpdb;
        return $wpdb->get_var("select count(*)
                            from information_schema.columns 
                            where table_name = '$table' 
                            and column_name = '$column' 
                            and table_schema = schema()") == 1;
    }
}

if (!function_exists('yak_column_size')) {
    function yak_column_size($table, $column) {
        global $wpdb;
        return $wpdb->get_var("select character_maximum_length
                            from information_schema.columns 
                            where table_name = '$table' 
                            and column_name = '$column' 
                            and table_schema = schema()");
    }
}


if ($wpdb->has_cap('collation')) {
	if (!empty($wpdb->charset)) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		$convert_charset_collate = "CHARACTER SET $wpdb->charset";
	}
	if (!empty($wpdb->collate)) {
		$charset_collate .= " COLLATE $wpdb->collate";
		$convert_charset_collate .= " COLLATE $wpdb->collate";
	}
}

$current_version = get_option(YAK_VERSION);

// installation defaults        
if (yak_get_option(ADDRESS_NAME, '') == '') {
    update_option(ADDRESS_NAME, 'on');
}

if (yak_get_option(ADDRESS_PHONE, '') == '') {
    update_option(ADDRESS_PHONE, 'on');
}

if (yak_get_option(ADDRESS_SUBURB, '') == '') {
    update_option(ADDRESS_SUBURB, 'on');
}

if (yak_get_option(ADDRESS_POSTCODE, '') == '') {
    update_option(ADDRESS_POSTCODE, 'on');
}

if (yak_get_option(ADDRESS, '') == '') {
    update_option(ADDRESS, 'on');
}

if (yak_get_option(ADDRESS_SEPARATE_BILLING, '') == '') {
    update_option(ADDRESS_SEPARATE_BILLING, 'on');
}

// Order table        
if ($wpdb->get_var("show tables like '$order_table'") != $order_table) {
    $sql = "create table " . $order_table . " (
            id mediumint(9) primary key not null auto_increment,
            time timestamp not null,
            user_id bigint(20) unsigned,
            recipient_name varchar(200),
            address text not null,
            country_code varchar(3),
            funds_received float default '0.0',
            shipping_cost float default '0.0',
            payment_type varchar(30),
            status varchar(10) default '' not null,
            billing_address text,
            billing_country_code varchar(3),
            order_num varchar(32),
            index $order_num_index (order_num)
            ) $charset_collate;";
    yak_log("creating table $order_table");
    $wpdb->query($sql);
}

// Order Meta table
if ($wpdb->get_var("show tables like '$order_meta_table'") != $order_meta_table) {
    $sql = "create table $order_meta_table (
            id mediumint(9) primary key not null auto_increment,
            order_id mediumint(9) not null,
            name varchar(100) not null,
            value varchar(255),
            index $order_meta_index (order_id, name)
            ) $charset_collate;";
    yak_log("creating table $order_meta_table");
    $wpdb->query($sql);
}

// Order Detail table
if ($wpdb->get_var("show tables like '$order_detail_table'") != $order_detail_table) {
    $sql = "create table $order_detail_table (
            id mediumint(9) not null,
            itemname text not null,
            price float not null,
            quantity smallint default '1' not null,
            post_id bigint(20) unsigned,
            cat_id bigint(20),
            index $order_detail_index (id)
            ) $charset_collate;";
    yak_log("creating table $order_detail_table");
    $wpdb->query($sql);
}
        
// Order Log table
if ($wpdb->get_var("show tables like '$order_log_table'") != $order_log_table) {
    $sql = "create table $order_log_table (
            time timestamp not null,
            message text not null,
            order_id mediumint(9)
            ) $charset_collate;";
    yak_log("creating table $order_log_table");
    $wpdb->query($sql);
} 

// Order Download table
if ($wpdb->get_var("show tables like '$order_dl_table'") != $order_dl_table) {
    $sql = "create table $order_dl_table (
            id mediumint(9) primary key not null auto_increment,
            order_id mediumint(9) not null,
            uid text,
            dl_file text not null,
            download_attempts smallint,
            download_address text
            ) $charset_collate;";
    yak_log("creating table $order_dl_table");
    $wpdb->query($sql);  
}

// Product table
if ($wpdb->get_var("show tables like '$product_table'") != $product_table) {
    $sql = "create table $product_table (
            post_id mediumint(9) not null,
            product_code varchar(30),
            price float not null,
            alt_title varchar(255),
            primary key (post_id)
            ) $charset_collate;";
    yak_log("creating table $product_table");
    $wpdb->query($sql);
}

// Product Detail table
if ($wpdb->get_var("show tables like '$product_detail_table'") != $product_detail_table) {
    $sql = "create table $product_detail_table (
            post_id mediumint(9) not null,
            cat_id bigint(20) not null,
            quantity mediumint,
            dl_file varchar(255),
            weight int,
            primary key (post_id, cat_id)
            ) $charset_collate;";
    yak_log("creating table $product_detail_table");
    $wpdb->query($sql);
}

// Promotions table
if ($wpdb->get_var("show tables like '$promo_table'") != $promo_table) {
    $sql = "create table $promo_table (
            promo_id mediumint(9) primary key not null auto_increment,
            code varchar(20) not null,
            promo_type varchar(20) not null,
            description varchar(250),
            value float not null,
            expiry_date date
            ) $charset_collate;";
    yak_log("creating table $promo_table");
    $wpdb->query($sql);
    
    $wpdb->query("alter table $promo_table add unique index $promo_index (code)");
}

if ($wpdb->get_var("show tables like '$promo_users_table'") != $promo_users_table) {
    $sql = "create table $promo_users_table (
            promo_id mediumint(9) not null,
            user_id bigint(20) unsigned not null
            ) $charset_collate";
    yak_log("creating table $promo_users_table");
    $wpdb->query($sql);
    
    $wpdb->query("alter table $promo_users_table add foreign key (promo_id) references $promo_table (promo_id)");
}

// automatically add the Checkout page if it hasn't been created
$chk_count = @$wpdb->get_var("select count(*) from " . $wpdb->posts . " 
                              where post_content like '%[yak_checkout]%' 
                              and post_type = 'page'");
if (isset($chk_count) && $chk_count != '1') {
    wp_insert_post(array('post_status'=>'publish', 
                         'post_type'=>'page',
                         'post_title'=>__('Checkout', 'yak'),
                         'post_content'=>'[yak_checkout]'));
}

// automatically setup the default category, if it hasn't been created
if (yak_get_option(PRODUCT_CATEGORY_NAME, '') == '') {
    $cat_id = wp_insert_category(array('cat_name'=>'products', 'category_description'=>'root category for YAK products'));
    wp_insert_category(array('cat_name'=>'default', 'category_description'=>'default category for YAK products', 'category_parent'=>$cat_id));
    update_option(PRODUCT_CATEGORY_NAME, 'products');
}

// upgrade for previous versions of yak
if (empty($current_version)) {
    $result = $wpdb->get_results("show columns from $order_table where field = 'order_num'");
    if (sizeof($result) == 0) {
        $wpdb->query("alter table $order_table add column order_num varchar(32)");
        $wpdb->query("alter table $order_table add index $order_num_index (order_num)");
    }
    $wpdb->query("alter table $order_table modify recipient_name varchar(200) null");
}

$ver = yak_calc_version_number($current_version);
$_GLOBALS['yak_current_version'] = $ver;
error_log("YAK installation, current version " . $ver);

if ($ver < 1001002) {
    $wpdb->query("alter table $product_detail_table add column override_price float null");
}

if ($ver < 1002001) {
    if (!yak_column_exists($order_table, 'country_code')) {
        $wpdb->query("alter table $order_table add column country_code varchar(3)");
    }
    
    if (!yak_column_exists($order_table, 'billing_country_code')) {
        $wpdb->query("alter table $order_table add column billing_country_code varchar(3)");
    }
}

if ($ver < 1002007) {
    if (!empty($convert_charset_collate)) {
        $wpdb->query("alter table $order_table convert to $convert_charset_collate");
        $wpdb->query("alter table $order_log_table convert to $convert_charset_collate");
        $wpdb->query("alter table $order_meta_table convert to $convert_charset_collate");
        $wpdb->query("alter table $order_detail_table convert to $convert_charset_collate");
        $wpdb->query("alter table $order_dl_table convert to $convert_charset_collate");
        $wpdb->query("alter table $product_table convert to $convert_charset_collate");
        $wpdb->query("alter table $product_detail_table convert to $convert_charset_collate");
        $wpdb->query("alter table $promo_table convert to $convert_charset_collate");
    }
}

if ($ver < 1003001) {
    if (!yak_column_exists($order_table, 'user_id')) {
        $wpdb->query("alter table $order_table add column user_id bigint(20) unsigned");
    }
}

if ($ver < 1003009) {
    $wpdb->query("alter table $product_detail_table add column sku varchar(13)");
}

if ($ver < 1008000) {
    $wpdb->query("alter table $product_table add column multi_select_options text");
    $wpdb->query("alter table $product_table add column multi_select_min int");
    $wpdb->query("alter table $product_table add column multi_select_max int");
    $wpdb->query("alter table $product_table add column discount_override float");
    
    $wpdb->query("alter table $promo_table add column threshold float");
    $wpdb->query("alter table $promo_table modify column promo_type varchar(30) not null");
}

if ($ver < 2000000) {
    $wpdb->query("alter table $order_table modify column status varchar(20)");
    $wpdb->query("alter table $order_table add column deleted tinyint");
    
    global $countries;
    
    update_option(SHIPPING_OPTION_NAMES, 'default');
    $zones_list = array();
    foreach ($countries as $cty=>$name) {
        $key = 'yak_' . $cty . '_zone';
        $zone = '6';
        if ($cty == 'GB') {
            $zone = '1';
            
            $prefix = 'yak_default_' . $zone;
            update_option($prefix . '_fixed', yak_get_option('yak_GB_shipping_fixed', ''));
            update_option($prefix . '_fixeditem', yak_get_option('yak_GB_shipping_fixeditem', ''));
            update_option($prefix . '_fixeditemfirst', yak_get_option('yak_GB_shipping_fixeditemfirst', ''));
            update_option($prefix . '_weight', yak_get_option('yak_GB_shipping_weight', ''));
            update_option($prefix . '_weightfirst', yak_get_option('yak_GB_shipping_weightfirst', ''));
        }
        else if ($cty == 'AT' || $cty == 'BE' || $cty == 'FR' || $cty == 'DK' || $cty == 'DE'
                || $cty == 'LU' || $cty == 'NL' || $cty == 'CH' || $cty == 'IE' || $cty == 'NO'
                || $cty == 'SE' || $cty == 'PT' || $cty == 'IT' || $cty == 'ES') {
            $zone = '2';
        }
        else if ($cty == 'US' || $cty == 'CA') {
            $zone = '3';
            
            $prefix = 'yak_default_' . $zone;
            update_option($prefix . '_fixed', yak_get_option('yak_US_shipping_fixed', ''));
            update_option($prefix . '_fixeditem', yak_get_option('yak_US_shipping_fixeditem', ''));
            update_option($prefix . '_fixeditemfirst', yak_get_option('yak_US_shipping_fixeditemfirst', ''));
            update_option($prefix . '_weight', yak_get_option('yak_US_shipping_weight', ''));
            update_option($prefix . '_weightfirst', yak_get_option('yak_US_shipping_weightfirst', ''));
        }
        else if ($cty == 'AU' || $cty == 'NZ') {
            $zone = '4';
            
            $prefix = 'yak_default_' . $zone;
            update_option($prefix . '_fixed', yak_get_option('yak_NZ_shipping_fixed', ''));
            update_option($prefix . '_fixeditem', yak_get_option('yak_NZ_shipping_fixeditem', ''));
            update_option($prefix . '_fixeditemfirst', yak_get_option('yak_NZ_shipping_fixeditemfirst', ''));
            update_option($prefix . '_weight', yak_get_option('yak_NZ_shipping_weight', ''));
            update_option($prefix . '_weightfirst', yak_get_option('yak_NZ_shipping_weightfirst', ''));
        }
        update_option($key, $zone);
        $_POST[$key] = $zone;
    }
    yak_admin_options_shipping_updzones();
}

if ($ver < 2001000) {
    $wpdb->query("alter table $order_table add price_discount float");
    $wpdb->query("alter table $order_table add shipping_discount float");
    $wpdb->query("alter table $product_table add require_login tinyint default 0");
}

if ($ver < 2001003) {
    $wpdb->query("alter table $order_detail_table add product_type varchar(20)");
}

if ($ver < 2001007) {
    $wpdb->query("alter table $order_table add selected_shipping_type varchar(20)");
}

if ($ver < 2002002) {
    $sql = "create table " . $address_table . " (
            id mediumint(9) primary key not null auto_increment,
            address_type varchar(8) not null,
            recipient varchar(100),
            company_name varchar(400),
            email_address varchar(400),
            phone_number varchar(20),
            address_line1 varchar(200),
            address_line2 varchar(200),
            suburb varchar(200),
            city varchar(200),
            state varchar(100),
            region varchar(100),
            country_code varchar(2),
            postcode varchar(10)
            ) $charset_collate;";
    yak_log("creating table $address_table");
    $wpdb->query($sql);

    $wpdb->query("alter table $order_table add shipping_address_id mediumint(9)");
    $wpdb->query("alter table $order_table add billing_address_id mediumint(9)");
    
    $wpdb->query("alter table $order_table modify address text");
}

if ($ver < 2002005 || !yak_db_column_exists($product_table, 'description')) {
    if (!yak_column_exists($product_table, 'description')) {
        $wpdb->query("alter table $product_table add description varchar(255)");
    }
}

if ($ver < 2003000) {
    yak_admin_options_set('yak_cc_types', array('visa', 'mastercard', 'american express'));
}

if ($ver < 2003004) {
    $sql = "create table " . $coupon_table . " (
                coupon_id mediumint(9) primary key not null auto_increment,
                coupon_code varchar(20) not null,
                coupon_set varchar(20) not null,
                used_datetime timestamp null default null
            ) $charset_collate";
    yak_log("creating table $coupon_table");
    $wpdb->query($sql);
    
    $wpdb->query("create unique index " . $coupon_table . "_idx1 on $coupon_table (coupon_code)");
    
    if (yak_get_option(SELECTED_CURRENCY, '') == '') {
        update_option(SELECTED_CURRENCY, 'USD');
    }
    
    $wpdb->query("alter table $product_table add unlimited_quantity tinyint(1) default 0");
}

if ($ver < 2003005) {
    $wpdb->query("alter table $order_table modify selected_shipping_type varchar(60)");
    
    $admin_role = get_role('administrator');
    $admin_role->add_cap('edit_yak_orders');
    $admin_role->add_cap('view_yak_orders');
    $admin_role->add_cap('edit_yak_settings');
    $admin_role->add_cap('view_yak_settings');
    $admin_role->add_cap('view_yak_sales_reports');
}

if ($ver < 2003006) {
    $wpdb->query("alter table $product_table add multi_select_cols tinyint(1)");
}

if ($ver < 3000000) {
    $admin_role = get_role('administrator');
    $admin_role->add_cap('view_yak_admin');    
}

if ($ver < 3001000) {
    function yak_activated_plugin($plugin) {
        if ($plugin == 'yak-for-wordpress/yak-for-wordpress.php' && $_GLOBALS['yak_current_version'] < 3001000) {
            activate_plugin('yak-ext-accrecv/yak-ext-accrecv.php');
            activate_plugin('yak-ext-authorizenet/yak-ext-authorizenet.php');
            activate_plugin('yak-ext-google-checkout/yak-ext-google-checkout.php');
            activate_plugin('yak-ext-manualcc/yak-ext-manualcc.php');
            activate_plugin('yak-ext-paypal-pro/yak-ext-paypal-pro.php');
            activate_plugin('yak-ext-salestax/yak-ext-salestax.php');
        }
    }
    
    add_action('activated_plugin', 'yak_activated_plugin');
}

// updates for version 3.1.5
if (!yak_column_exists($order_meta_table, 'post_id')) {
    $wpdb->query("alter table $order_meta_table add post_id bigint null");
}

if (!yak_column_exists($order_meta_table, 'cat_id')) {
    $wpdb->query("alter table $order_meta_table add cat_id bigint null");
}

// updates for version 3.1.9
if (!yak_column_exists($promo_table, 'products')) {
    $wpdb->query("alter table $promo_table add products text null");
}

if ($ver < 3002004) {
    if (yak_get_option(SELECTED_CURRENCY, '') == '') {
        update_option(SELECTED_CURRENCY, 'USD');
    }
}

if (!yak_column_exists($product_table, 'custom_price')) {
    $wpdb->query("alter table $product_table add custom_price tinyint default 0");
}

// updates for version 3.4.5
if (!yak_column_exists($promo_table, 'products_inclusion')) {
    $wpdb->query("alter table $promo_table add products_inclusion tinyint default 1");
}

// updates for version 3.4.6
if (yak_column_size($order_meta_table, 'value') <= 255) {
    $wpdb->query("alter table $order_meta_table modify value varchar(8000)");
}

if (!isset($current_version)) {
    add_option(YAK_VERSION, YAK_VERSION_NUMBER);    
}
else {
    update_option(YAK_VERSION, YAK_VERSION_NUMBER);   
}
?>