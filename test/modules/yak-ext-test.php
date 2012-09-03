<?php
/*
Plugin Name: YAK Add-on Module - Test
Description: Test module for YAK-for-WordPress
Version: 1.0
Author: A Filly Ate It
Author URI: http://www.afillyateit.com
*/

function test_module_shortcode_tag($attrs) {
    return 'MODULE SHORTCODE';
}

add_shortcode('test_module_shortcode', 'test_module_shortcode_tag');
?>