<?php
/*
See yak-for-wordpress.php for information and license terms
*/
error_reporting(E_ALL ^ E_DEPRECATED);
require_once('yak-standalone.php');

global $post, $wpdb, $user_level, $countries, $order_table;

if ($user_level < 10) {
    die("No access allowed");
}

if ($_GET['type'] == 'excel') {
    header("Content-Type: application/ms-excel");
    header("Content-Disposition: attachment; filename=orders.xls");
    $delim = "\t";
}
else {
    header("Content-Type: text/plain");
    $delim = ",";
}
$order_year = $_GET['year'];
$order_month = $_GET['month'];
$status = $_GET['status'];
$payment_type = $_GET['payment_type'];
$search = $_GET['search'];

echo "order id" , $delim , "order number" , $delim , "order date" , $delim , "order value" , $delim , "recipient" , $delim , "email" , $delim , "address\n";
$orders = yak_get_orders($status, null, $order_year, $order_month, true, true, true, null, null, $payment_type, $search);
foreach ($orders as $order) {
    $addr = $order->get_shipping_address();
    echo $order->id , $delim , $order->order_num , $delim , $order->time , $delim , number_format($order->total + $order->shipping_cost, 2, '.', '') , $delim
        , $addr->recipient , $delim , $addr->email , $delim;
    if ($addr != null) {
        echo $addr->as_csv('phone', 'recipient', 'email', 'country');
    }
    echo ',';
    if ($addr != null) {
        $countries[$addr->country];
    }
    echo "\n";
}
?>