<?php
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

global $wpdb;

$wpdb->query("delete from $wpdb->options where option_name like 'yak_%'");

$wpdb->query("drop table " . $wpdb->prefix . "_yak_order");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_order_detail");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_order_dl");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_order_log");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_order_meta");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_product");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_product_detail");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_promotion_users");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_promotions");
$wpdb->query("drop table " . $wpdb->prefix . "_yak_coupon");
?>