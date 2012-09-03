<?php
/*
See yak-for-wordpress.php for information and license terms
*/
if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}

global $model, $user_ID;

global $sep_billing;
$sep_billing = (yak_get_option(ADDRESS_SEPARATE_BILLING, '') == 'on');

$shipping_options = $model[SHIPPING_OPTIONS];

$recipient = null;
$email = null;
if (isset($user_ID)) {
    $fn = get_usermeta($user_ID, 'first_name');
    $ln = get_usermeta($user_ID, 'last_name');
    if ($fn != null && $ln != null) {
        $recipient = $fn . ' ' . $ln;
    }
    $user = get_userdata($user_ID);
    $email = $user->user_email;
}

if (!function_exists('address_entry')) {
    function address_entry($prefix, $recipient = null, $email = null) {
        global $countries, $sep_billing, $states, $canada_states;

        if ($recipient == null) {
            $recipient = $_POST[$prefix . '_recipient'];
        }

        if ($email == null) {
            $email = $_POST[$prefix . '_email'];
        }

        $enabled_countries = yak_get_option(ENABLED_COUNTRIES, $countries);
?>

    <table class="yak_left">
        <col width="30%" />
        <col width="70%" />
        <tr>
            <th colspan="2">
                <?php 
                    if ($prefix == 'shipping') {
                        _e("Enter a new shipping address", 'yak');
                    }
                    else {
                        _e("Enter a new billing address", 'yak');                        
                    }
                ?>
            </th>
        </tr>
<?php
        if (yak_get_option(ADDRESS_NAME, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Name', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_recipient" value="<?php echo $recipient ?>" size="40" maxlength="60" /></td>
        </tr>
<?php
        }

        if (yak_get_option(ADDRESS_COMPANY_NAME, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Company Name', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_company_name" value="<?php echo $_POST[$prefix . '_company_phone'] ?>" size="40" maxlength="400" /></td>
        </tr>
<?php
        }
?>        

        <tr>
            <td><?php echo _e('Email', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_email" value="<?php echo $email ?>" size="40" maxlength="80" /></td>
        </tr>
<?php
        
        if (yak_get_option(ADDRESS_PHONE, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Phone Number', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_phone" value="<?php echo $_POST[$prefix . '_phone'] ?>" size="15" maxlength="15" /></td>
        </tr>
<?php
        }
        
        if (yak_get_option(ADDRESS, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Address Line 1', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_addr1" value="<?php echo $_POST[$prefix . '_addr1'] ?>" size="40" maxlength="60"  /></td>
        </tr>
        <tr>
            <td><?php echo _e('Address Line 2', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_addr2" value="<?php echo $_POST[$prefix . '_addr2'] ?>" size="40" maxlength="60" /></td>
        </tr>
<?php
        if (yak_get_option(ADDRESS_SUBURB, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Suburb', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_suburb" value="<?php echo $_POST[$prefix . '_suburb'] ?>" size="20" maxlength="40"  /></td>
        </tr>
<?php
        }
?>
        <tr>
            <td><?php echo _e('City', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_city" value="<?php echo $_POST[$prefix . '_city'] ?>" size="20" maxlength="40" /></td>
        </tr>
        <tr>
            <td id="<?php echo $prefix . '_region_label' ?>"><?php echo _e('Region', 'yak') ?>&nbsp;: </td>
            <td><input id="<?php echo $prefix . '_region_input' ?>" type="text" name="<?php echo $prefix ?>_region" value="<?php echo $_POST[$prefix . '_region'] ?>" size="20" maxlength="20" />
                <?php echo yak_html_select(array('id'=>$prefix . '_state_input', 'name'=>$prefix . '_state', 'selected'=>yak_default($_POST[$prefix . '_state'], ''), 'values'=>$states)) ?>
                <?php echo yak_html_select(array('id'=>$prefix . '_ca_state_input', 'name'=>$prefix . '_state', 'selected'=>yak_default($_POST[$prefix . '_ca_state'], ''), 'values'=>$canada_states)) ?></td>
        </tr>
<?php
        if (yak_get_option(ADDRESS_POSTCODE, '') == 'on') {
?>
        <tr>
            <td><?php echo _e('Zip/Postcode', 'yak') ?>&nbsp;: </td>
            <td><input type="text" name="<?php echo $prefix ?>_postcode" value="<?php echo $_POST[$prefix . '_postcode'] ?>" size="10" maxlength="20" /></td>
        </tr>
<?php
        }
?>
        <tr>
            <td><?php echo _e('Country', 'yak') ?>&nbsp;: </td>
            <td><?php echo yak_html_select(array('id'=>$prefix . '_country_input', 'name'=>$prefix . '_country', 'selected'=>yak_default($_POST[$prefix . '_country'], yak_get_option(DEFAULT_COUNTRY, '')), 'values'=>$enabled_countries, 'onchange'=>"changeRegion('$prefix')")) ?></td>
        </tr>
<?php
        }

        if ($prefix == 'shipping' && $sep_billing) {
?>
        <tr>
            <td><?php echo _e('Use shipping address as billing address', 'yak') ?></td>
            <td><input type="checkbox" id="shipping_is_billing" name="shipping_is_billing" checked="checked" onclick="javascript:billing()" /></td>
        </tr>
<?php
        }
?>
    </table>
<?php
    }
}

if (isset($model['error_message'])) {
?>
<div class="yak_error"><?php echo $model['error_message'] ?></div>
<?php
}
?>

<p class="yak_left">
    <?php 
        if (count($shipping_options) > 1) {
    ?>
    <?php _e('Select your shipping method', 'yak') ?>: <?php echo yak_html_select(array('id'=>'shipping_opt', 'name'=>SELECTED_SHIPPING_OPTION, 'values'=>$shipping_options)) ?>
    <?php 
        }
        else if (count($shipping_options) == 1) {
            echo '<select class="yak_hidden" id="shipping_opt" name="' , SELECTED_SHIPPING_OPTION , '"><option value="' , reset($shipping_options) , '"></option></select>';
        }
    ?>
</p>

<?php
if (isset($model['shipping_address']) && $model['shipping_address'] != null) {
?>
<form id="oldaddress" name="oldaddress" method="post" action="<?php echo yak_get_permalink('stage=address') ?>">

<?php
    include 'yak-view-address-snippet.php';
    
    yak_address_hidden_input($model['shipping_address'], 'shipping');
    yak_address_hidden_input($model['billing_address'], 'billing');
?>

    <p class="yak_left">
        <button id="shippingbutton" class="yak_button" type="submit" onclick="copyValue('#shipping_opt', '#old_address_shipping_option'); return true;"><?php _e('Use this address', 'yak') ?></button>
        <button id="editaddress" class="yak_button" type="button" onclick="editAddress()"><?php _e('Edit this address', 'yak') ?></button>
    </p>

    <input type="hidden" name="action" value="confirm" />
    <input type="hidden" id="old_address_shipping_option" name="<?php echo SELECTED_SHIPPING_OPTION ?>" value="" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
</form>
        
<?php
}
?>

<form id="address" name="address" method="post" action="<?php echo yak_get_permalink('stage=address') ?>">    
<?php
    address_entry('shipping', $recipient, $email);

    if ($sep_billing) {
?>
    <div id="billing" style="visibility: hidden; display: none">
<?php    
        address_entry('billing', $recipient, $email);
?>
    </div>
<?php
    }
?>
    <p class="yak_left">
        <button id="billingbutton" class="yak_button" type="submit" onclick="copyValue('#shipping_opt', '#address_shipping_option'); return true;"><?php _e('Use this address', 'yak') ?></button>
    </p>        
    <input type="hidden" name="action" value="confirm" />
    <input type="hidden" id="address_shipping_option" name="<?php echo SELECTED_SHIPPING_OPTION ?>" value="<?php echo $_POST[SELECTED_SHIPPING_OPTION] ?>" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
</form>
<script type="text/javascript">
<!--
var $j = jQuery.noConflict();
$j(document).ready(function() {
    changeRegion('shipping');
    changeRegion('billing');    
});
-->
</script>