<?php
define("YAK_WPABSPATH", dirname(__FILE__) . '/../../../');

require_once(YAK_WPABSPATH . 'wp-config.php');
require_once(YAK_WPABSPATH . 'wp-includes/functions.php');
if (file_exists(YAK_WPABSPATH . 'wp-includes/pluggable.php')) {
    require_once(YAK_WPABSPATH . 'wp-includes/pluggable.php');
}
else {
    require_once(YAK_WPABSPATH . 'wp-includes/pluggable-functions.php');
}
require_once(YAK_WPABSPATH . 'wp-admin/upgrade-functions.php');
?>