<?php
require_once('yak-currencies.php');

class YakOrderWidget extends WP_Widget {
    
    /**
     * Constructor for the widget
     */
    function YakOrderWidget() {
        $widget_ops = array('classname' => 'yak_order_widget', 'description' => __( "YAK Order Panel Widget") );
        $control_ops = array();
        $this->WP_Widget('yak_order', __('YAK Order Panel'), $widget_ops, $control_ops);
    }

    /**
     * Display the widget
     */
    function widget($args, $instance) {
        extract($args);
        
        $val = $_SESSION['current_order_value'];
        $items = $_SESSION['current_order_items'];
        if ($instance['display-if-empty'] == 'on' && !isset($val)) {
            $val = 0;
            $items = 0;
        }
        
        if (isset($val)) {
            echo $before_widget;
            echo $before_title , __('Your Shopping Basket', 'yak') , $after_title;
            echo __('Items in basket', 'yak') , ': ' , $items , '<br />';
            echo __('Subtotal', 'yak') , ': ' , yak_format_money($val, true);
            if (!empty($instance['checkout'])) {
                $link = get_page_link($instance['checkout']);
                if (yak_get_option(USE_SSL, 'off') == 'on') {
                    $link = str_replace('http:', 'https:', $link);
                }
                echo '<br /><a class="yak-checkout-link" href="' , $link , '">' 
                        , __('Proceed to Checkout', 'yak') , '</a><br />';
            }
            echo $after_widget;
        }
    }

    /**
     * Store the widget data
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['checkout'] = strip_tags(stripslashes($new_instance['checkout']));
        $instance['display-if-empty'] = $new_instance['display-if-empty'];
        return $instance;
    }

    /**
     * Creates the edit form for the widget
     */
    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('checkout'=>''));

        $checkout = htmlspecialchars($instance['checkout']);
        $display_if_empty = $instance['display-if-empty'];

        $pages = get_pages();
        $page_ids = array();
        $page_ids[''] = '';
        foreach ($pages as $page) {
            $page_ids[$page->ID] = $page->post_title;
        }

        echo '<p>' , __('Checkout redirect', 'yak-admin') , '<br />';
        echo yak_html_select(array('name'=>$this->get_field_name('checkout'), 'selected'=>$checkout, 'values'=>$page_ids)) , '</p>';
        echo '<p>' , __('Display if empty?', 'yak-admin') , '<br />';
        echo '<input name="' , $this->get_field_name('display-if-empty') , '" type="checkbox" ';
        if ($display_if_empty == 'on') {
            echo 'checked="checked"';
        }
        echo '/></p>';
    }
}

function YakOrderInit() {
    register_widget('YakOrderWidget');
}

add_action('widgets_init', 'YakOrderInit');
?>