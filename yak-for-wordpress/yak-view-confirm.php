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

require_once('yak-promo-utils.php');

global $model;

$GLOBALS['yak-add-to-price'] = array();

$shipping_notes = yak_get_option(SHIPPING_NOTES, '');

$default_special_instructions = stripslashes(yak_get_option(DEFAULT_SPECIAL_INSTRUCTIONS, ''));

$terms_and_conditions = stripslashes(yak_get_option(TERMS_AND_CONDITIONS, ''));
if ($terms_and_conditions == '') {
    $include_tandc = 'false';
}
else {
    $include_tandc = 'true';
}

$sep_billing = (yak_get_option(ADDRESS_SEPARATE_BILLING, '') == 'on');

if ($_POST[SHIPPING_IS_BILLING] == 'on' || empty($_POST[BILLING_COUNTRY])) {
    $billing_state = $_POST[SHIPPING_STATE];
    $billing_country = $_POST[SHIPPING_COUNTRY];
}
else {
    $billing_state = $_POST[BILLING_STATE];
    $billing_country = $_POST[BILLING_COUNTRY];
}

$ptype = $_SESSION[PAYMENT_TYPE];

$shipping_options = $model[SHIPPING_OPTIONS];

$err = null;
if (isset($model['error_message'])) {
    $err = $model['error_message'];
}
else if (isset($_POST['error_message'])) {
    $err = $_POST['error_message'];
}

$hide_quantity = yak_get_option(HIDE_QUANTITY, 'off') == 'on';

if ($err != null) {
?>
<div class="yak_error"><?php echo $err ?></div>
<?php
}
?>

<h3><?php _e('Confirm your order', 'yak'); ?></h3>

<?php 
include 'yak-view-address-snippet.php'; 
?>
        
<p>&nbsp;</p>

<h3><?php _e('Order Detail', 'yak'); ?></h3>

<form name="confirm2" method="post" action="<?php echo yak_get_permalink('stage=confirm') ?>">
    <table class="yak_order">
        <tr>
            <th><?php _e('Item', 'yak'); ?></th>
            <th class="yak_numeric"><?php _e('Price', 'yak') ?></th>
            <th class="yak_numeric"><?php if (!$hide_quantity) { _e('Qty', 'yak'); } ?></th>
            <th class="yak_numeric"><?php _e('Subtotal', 'yak') ?></th>
        </tr>
        
<?php
    $total_price = 0;
    $total_quantity = 0;
    $total_weight = 0;
    $total_discount = 0;
    
    $totals = yak_get_totals($model['items']);
    
    if (!empty($model['promo_code'])) {
        $promo = yak_get_promotion($model['promo_code']);
    }
    else {
        $promo = yak_get_promotion_by_threshold($totals->price, $totals->quantity);
    }
    
    foreach ($model['items'] as $key => $item) {
        $item->price = yak_calc_price($item->id, $item->cat_id, $item->price);
        $item->discount = yak_calc_price_discount($item->id, $item->quantity, $item->price, $totals->quantity, $totals->price, $promo);
        $total_discount += ($item->discount * $item->quantity);
        
        $total_price += $item->get_discount_total();
        $total_quantity += $item->quantity;
        
        $total_weight += $item->get_total_weight();
        
        $act_shipping_cost = 0;
?>
        <tr>
            <td class="yak_left"><?php echo $item->name ?></td>
            <td class="yak_numeric"><?php echo yak_format_money($item->price) ?></td>
            <td class="yak_numeric"><?php if (!$hide_quantity) { echo $item->quantity; } ?></td>
            <td class="yak_numeric"><?php echo yak_format_money($item->get_total()) ?></td>
        </tr>
<?php
        if (isset($item->selected_options)) {
?>
        <tr>
            <td class="yak_left" colspan="4">
<?php
            foreach ($item->selected_options as $mitem) {
                echo "&nbsp;&nbsp;" . __($mitem) . "<br />";
            }
?>          
            </td>
        </tr>
<?php
        }
        
        if (count($item->meta) > 0) {
?>
        <tr>
            <td class="yak_left" colspan="4">
<?php
            foreach ($item->meta as $key=>$val) {
                if (!empty($val)) {
                    echo "&nbsp;&nbsp;" . __($key, 'yak') . ":" . __($val, 'yak') . "<br />";
                }
            }
?>
            </td>
        </tr>
<?php            
        }
    }
    
    do_action('yak-cart-confirm', array('total_cost'=>$total_price, 'state'=>$billing_state, 'country'=>$billing_country));
    $add_to_price =& $GLOBALS['yak-add-to-price'];
    foreach ($add_to_price as $add) {
        $total_price += $add;
    }
    
    $grand_total = $total_price;

    // calculate shipping
    if (yak_get_option(INCLUDE_SHIPPING_COSTS, 'no') == 'yes') {
        global $shipping_costs;
        
        $shipping_opt = $_POST[SELECTED_SHIPPING_OPTION];
        if (empty($shipping_opt)) {
            $shipping_options = yak_get_shipping_options();
            $shipping_opt = reset($shipping_options);
        }
        
        $cty = str_replace(' ', '_', $_POST[SHIPPING_COUNTRY]);
        $act_shipping_cost = yak_calc_shipping($total_weight, $totals->quantity, $cty, $shipping_opt);
        $grand_total += $act_shipping_cost;
        
        if ($promo != null) {
            $shipping_discount = yak_calc_shipping_discount($act_shipping_cost, $promo, $model['items']);
            
            if ($shipping_discount > 0) {
                $grand_total -= $shipping_discount;
                $total_discount = $shipping_discount;
            }
        }
?>
        <tr id="yak_shipping">
            <td class="yak_left" colspan="3"><?php _e('Shipping costs', 'yak') ?>
            <?php if (!empty($shipping_opt) && count($shipping_options) > 1) { echo '(' . $shipping_opt . ')'; } ?></td>
            <td id="shipping_cost" class="yak_numeric"><?php echo yak_format_money($act_shipping_cost) ?></td>
        </tr>
<?php
        if ($shipping_notes != '') {
?>
        <tr>
            <td id="shipping_notes" class="yak_small"><?php echo $shipping_notes ?></td>
            <td colspan="3"></td>
        </tr>
<?php
        }
    }
?>
<?php
        if ($promo != null && $total_discount > 0) {
?>
        <tr>
            <td class="yak_left"><?php _ye('Total Discount (%s)', 'yak', __($promo->description, 'yak')) ?></td>
            <td></td>
            <td></td>
            <td id="total_discount" class="yak_numeric">-<?php echo yak_format_money($total_discount) ?></td>
        </tr>
<?php
        }
?>
        <tr>
            <td class="yak_total" colspan="3"><?php _e('Total', 'yak') ?></td>
            <td id="grand_total" class="yak_total"><?php echo yak_format_money($grand_total, true) ?></td>
        </tr>
        
        <tr>
            <td class="yak_small" colspan="4"><?php _e('Payment method', 'yak') ?>: <strong id="payment_method"><?php echo __($ptype, 'yak') ?></strong></td>
        </tr>

<?php
        if (!empty($default_special_instructions)) {
?>
        <tr>
            <td valign="top"><?php echo _e('Special Instructions', 'yak') ?> : </td>
            <td colspan="3" align="right">
                <textarea id="special_instructions" class="yak_small" name="special_instructions" cols="32" rows="4" onclick="this.focus(); this.select()" onKeyUp="limitTextArea(this, 250)"><?php echo $default_special_instructions ?></textarea>
            </td>
        </tr>
<?php
        }
        
        do_action('yak-cart-confirm-base'); 
?>

<?php
        if (!empty($terms_and_conditions)) {
?>
        <tr>
            <td colspan="4" align="left"><?php echo $terms_and_conditions ?> <input type="checkbox" id="tandcConfirmation" name="tandcConfirmation" /></td>
        </tr>
<?php
        }
?>
        <tr>
            <td class="yak_left" colspan="4"><button id="confirmorderbutton" class="yak_button" onclick="return yakConfirmOrder(<?php echo $include_tandc ?>)" type="submit"><?php _e('Confirm order', 'yak') ?></button></td>
        </tr>

    </table>
    <input type="hidden" name="totalprice" value="<?php echo $grand_total ?>" />
    <input type="hidden" name="action" value="confirm2" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
    
<?php
    do_action('yak-cart-confirm-hidden');

    include 'yak-view-shipping-snippet.php';

    if (count($selected_payment_options) == 1) {        
?>
    <input type="hidden" name="payment_type" value="<?php echo $selected_payment_options[0] ?>" />

<?php
    }
?>

</form>