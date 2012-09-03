<?php
/*
Plugin Name: YAK Add-on Module - Third Party
Description: Test Third Party module for YAK-for-WordPress
Version: 1.0
Author: A Filly Ate It
Author URI: http://www.afillyateit.com
*/

function yak_check_order_3p($args) {
    $order_id = $args['order_id'];
    $email = $args['email'];
    $recipient = $args['recipient'];
    $total_cost = $args['total_cost'];
    $shipping_cost = $args['shipping_cost'];
    $actual_cost = $args['actual_cost'];
    $country_code = $args['country'];
    $user_id = $args['user_id'];
    
    $msg = "<table id=\"third-party\" style=\"visibility:hidden; display: none\">
        <tr>
            <td id=\"third-party-id\">$order_id</td>
            <td id=\"third-party-email\">$email</td>
            <td id=\"third-party-recipient\">$recipient</td>
            <td id=\"third-party-cost\">$total_cost</td>
            <td id=\"third-party-shipping\">$shipping_cost</td>
            <td id=\"third-party-actual-cost\">$actual_cost</td>
            <td id=\"third-party-country-code\">$country_code</td>
            <td id=\"third-party-user-id\">$user_id</td>
        </tr>
    </table>";
    
    if (yak_str_contains($_SERVER['REQUEST_URI'], 'page=yak')) {
        echo $msg;
    }
    yak_log($msg);
}

add_action('yak-order', 'yak_check_order_3p');
?>