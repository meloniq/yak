<?php
/*
See yak-for-wordpress.php for information and license terms
*/
if (!function_exists('yak_redirect')) {
    /**
     * Redirect to a specific page to provide instructions for payment.
     */
    function yak_redirect($payment_type, $order_id, $items, $shippingcost) {
        $payment_types = yak_get_option(PAYMENT_TYPES, null);
        
        $url = $payment_types[$payment_type];
        return yak_redirect_page($order_id, $items, $shippingcost, true, $url);
    }
}

if (!function_exists('yak_redirect_payment_options')) {
    function yak_redirect_payment_options($payments) {
        $payment_pages = &$payments['pages'];
        $options = &$payments['options'];
        
        if (function_exists('pause_exclude_pages')) {
            pause_exclude_pages();
        }
        $pages = get_pages();
        if (function_exists('resume_exclude_pages')) {
            resume_exclude_pages();
        }
        
        foreach ($pages as $page) {
            $p = '?page_id=' . $page->ID;
            $payment_pages[$p] = 'PAGE: ' . $page->post_title;
            $options[$p] = 'redirect';
        }
        
        return $payments;
    }
}

add_filter('yak-redirect-redirect', 'yak_redirect', 10, 4);
add_filter('yak-payment-options', 'yak_redirect_payment_options');
?>