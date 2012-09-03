<?php
/**
 * Standalone version of the YAK order widget. Used by the AJAX version of the buy button
 * so that we can refresh the widget when the customer clicks 'buy'.
 */

require_once('yak-standalone.php');

if (session_id() == "") {
    @session_start();
}

require_once('yak-utils.php');
require_once('yak-view-order-widget.php');

$widget = new YakOrderWidget('yak_order');

$args = array(
    'before_widget' => '<li id="yak_order-3" class="widget-container yak_order_widget">',
    'after_widget' => '</li>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>'
);

$widget->display_callback($args, 2);
?>