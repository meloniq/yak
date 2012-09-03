<?php
/*
Plugin Name: YAK Add-on Module - Manual Credit Card Payment
Description: Manual Credit Card add-on module for YAK-for-WordPress
Version: 3.3.5
Author: a filly ate it
Author URI: http://afillyateit.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define("CREDIT_CARD", "Credit Card");
define("CC_LANDING_PAGE", "yak_cc_landing_page");
define("CC_IMMEDIATE_CONFIRMATION", "yak_cc_immediate_confirm");
define("CC_PUBLIC_KEY", "yak_cc_public_key");
define("CC_TYPES", "yak_cc_types");

if (file_exists(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php')) {
    require_once(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php');
}

function yak_manualcc_decrypt($content) {
    if (!empty($_REQUEST['private-key'])) {
        $pkey = openssl_pkey_get_private($_REQUEST['private-key']);
        
        if (openssl_private_decrypt(base64_decode($content), $output, $pkey)) {
            return $output;
        }
        yak_log("ERROR: Unable to decrypt content");
    }
    return $content;
}

function yak_manualcc_order_form($args) {
    $public_key = yak_get_option(CC_PUBLIC_KEY);
    if (!empty($public_key)) {
        $label = __('Private key', 'yak-admin');
        $title = __('Enter the private key you\'ve generated on YAK\'s payments settings screen.', 'yak-admin');
        echo <<<EOD
    <div class="alignright">
        <table>
            <tr>
                <td>$label</td>
            </tr>
            <tr>
                <td><textarea name="private-key" cols="50" rows="5" title=""></textarea></td>
            </tr>
        </table>
    </div>
EOD;
    }
}

if (!function_exists('yak_manualcc_settings')) {
    function yak_manualcc_settings() {
        global $cards, $model;
        
        $model[CC_LANDING_PAGE] = yak_get_option(CC_LANDING_PAGE, '');
        $model[CC_IMMEDIATE_CONFIRMATION] = yak_get_option(CC_IMMEDIATE_CONFIRMATION, '');
        $model[CC_PUBLIC_KEY] = yak_get_option(CC_PUBLIC_KEY, '');
        $model[CC_TYPES] = yak_get_option(CC_TYPES, array());
        
        $card_keys = array();
        foreach ($cards as $name=>$card) {
            $card_keys[$name] = $card['name'];
        }
        ?>
        <h3><?php _e('Manual Credit Card settings', 'yak-admin') ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e('Landing Page', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>CC_LANDING_PAGE, 'selected'=>$model[CC_LANDING_PAGE], 'values'=>$model[PAGES],
                            'title'=>__('Final page for a successful credit card order.', 'yak-admin'))) ?></td>
            </tr>
            <tr>
                <th><?php _e('Immediate confirmation', 'yak-admin') ?></th>
                <td><input type="checkbox" name="<?php echo CC_IMMEDIATE_CONFIRMATION ?>" <?php yak_html_checkbox($model[CC_IMMEDIATE_CONFIRMATION]) ?>
                        title="<?php _e('Send email confirmation immediately, or once the credit card has been processed.', 'yak-admin') ?>" /></td>
            </tr>
            <?php if (function_exists('openssl_public_encrypt')) { ?>
            <tr>
                <th><?php _e('Public key', 'yak-admin') ?></th>
                <td><textarea name="<?php echo CC_PUBLIC_KEY ?>" cols="80" rows="10"
                        title="<?php echo _e('The public key is used to encrypt credit card details - the private key to decrypt', 'yak-admin') ?>"><?php echo $model[CC_PUBLIC_KEY] ?></textarea><br />
                <button name="generate-key" value="generate-key">Generate</button>
                <?php if (!empty($_REQUEST['private-key'])) { ?>
                <p><i><?php _e('Store this key (below) in a secure location. You\'ll use it to decrypt credit card details, so it is essential you don\'t lose it', 'yak-admin') ?><br />
                        <?php _e('(Recommendation: at the very least, save the key in a file, add the file to an encrypted zip, then store on your local machine with a second copy on a USB key). Do not store on your web server!', 'yak-admin') ?></i></p>
                <p><pre id="private-key"><?php echo $_REQUEST['private-key'] ?></pre></p>
                <?php } ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th><?php _e('Allowed credit card types', 'yak-admin') ?></th>
                <td><?php echo yak_html_select(array('name'=>CC_TYPES . '[]', 'selected'=>$model[CC_TYPES], 'values'=>$card_keys, 'style'=>'height: 10em', 'multiple'=>5,
                            'title'=>__('Select the credit card types you allow customers to purchase with.', 'yak-admin'))) ?></td>
            </tr>
        </table>
        <?php
    }
}

if (!function_exists('yak_manualcc_apply_settings')) {
    function yak_manualcc_apply_settings() {
        if (!empty($_POST['generate-key'])) {
            $res = openssl_pkey_new();

            openssl_pkey_export($res, $privatekey);
            $publickey = openssl_pkey_get_details($res);
            $publickey = $publickey["key"];

            $_POST[CC_PUBLIC_KEY] = $publickey;
            $_REQUEST['private-key'] = $privatekey;
        }
        
        yak_admin_options_set(CC_LANDING_PAGE);
        yak_admin_options_set(CC_IMMEDIATE_CONFIRMATION, 'off');
        yak_admin_options_set(CC_PUBLIC_KEY);
        yak_admin_options_set(CC_TYPES, null, true);        
    }
}

if (!function_exists('yak_manualcc_confirm_order')) {
    function yak_manualcc_confirm_order($order_id, $items) {
        global $wpdb, $order_meta_table;
    
        if (isset($_SESSION['cc'])) {        
            $cc = $_SESSION['cc'];
            $cc_type = $cc['type'];
            $cc_security_code = $cc['security_code'];
            $cc_number = $cc['number'];
            $cc_name = $cc['name'];
            $cc_expiry = $cc['expiry'];
        
            $cc_public_key = yak_get_option(CC_PUBLIC_KEY);

            if (!empty($cc_public_key) && function_exists('openssl_public_encrypt')) {
                $publickey = openssl_pkey_get_public($cc_public_key);
                if ($publickey != null) {
                    openssl_public_encrypt($cc_number, $cc_number_enc, $publickey);
                    openssl_public_encrypt($cc_security_code, $cc_security_code_enc, $publickey);

                    $cc_number = base64_encode($cc_number_enc);
                    $cc_security_code = base64_encode($cc_security_code_enc);
                }
                else {
                    yak_log("ERROR: Unable to get OpenSSL public key");
                }
            }
        
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value) 
                                   values (%d, 'CC type', %s)", $order_id, $cc_type);
            $wpdb->query($sql);
                      
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value) 
                          values (%d, 'CC number', %s)", $order_id, $cc_number);
            $wpdb->query($sql);

            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value) 
                          values (%d, 'CC security code', %s)", $order_id, $cc_security_code);
            $wpdb->query($sql);
                      
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value)
                          values (%d, 'CC name', %s)", $order_id, $cc_name);
            $wpdb->query($sql);
                      
            $sql = $wpdb->prepare("insert into $order_meta_table (order_id, name, value)
                          values (%d, 'CC expiry', %s)", $order_id, $cc_expiry);
            $wpdb->query($sql);
            
            unset($_SESSION['cc']);
        }    
    }
}

if (!function_exists('yak_manualcc_redirect')) {
    function yak_manualcc_redirect($payment_type, $order_id, $items, $shippingcost) {
        $uri = yak_get_option(CC_LANDING_PAGE, '');
    
        if (yak_get_option(CC_IMMEDIATE_CONFIRMATION, '') == 'on') {
            $send_conf = true;
        }
        else {
            $send_conf = false;            
        }
    
        return yak_redirect_page($order_id, $items, $shippingcost, $send_conf, $uri);
    }
}

if (!function_exists('yak_manualcc_finalise')) {
    function yak_manualcc_finalise($order_id) {
        if (yak_get_option(CC_IMMEDIATE_CONFIRMATION, '') != 'on') {
            yak_send_confirmation_email($order_id);
        }
    }
}


if (!function_exists('yak_manualcc_payment_options')) {
    function yak_manualcc_payment_options($payments) {
        $pages = &$payments['pages'];
        $options = &$payments['options'];
        
        $pages[CREDIT_CARD] = 'SPECIAL: ' . CREDIT_CARD;
        $options[CREDIT_CARD] = 'manualcc';
        
        return $payments;
    }
}

if (!function_exists('yak_manualcc_next_page')) {
    function yak_manualcc_next_page() {
        return 'yak-view-cc.php';
    }
}

add_filter('yak-display-meta-value-CC-number', 'yak_manualcc_decrypt');
add_filter('yak-display-meta-value-CC-security-code', 'yak_manualcc_decrypt');
add_action('yak-order-form', 'yak_manualcc_order_form');
add_action('yak-payment-settings', 'yak_manualcc_settings');
add_action('yak-payment-apply-settings', 'yak_manualcc_apply_settings');
add_action('yak-confirm-order-manualcc', 'yak_manualcc_confirm_order', 10, 2);
add_action('yak-finalise-order-manualcc', 'yak_manualcc_finalise');
add_filter('yak-redirect-manualcc', 'yak_manualcc_redirect', 10, 4);
add_filter('yak-payment-options', 'yak_manualcc_payment_options');  
add_filter('yak-next-page-manualcc', 'yak_manualcc_next_page');
?>