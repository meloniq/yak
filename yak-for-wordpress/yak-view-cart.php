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

$hide_update_button = yak_get_option(HIDE_UPDATE_BUTTON, 'off');
$payment_types = yak_get_option(PAYMENT_TYPES, array());
$total_price = 0.0;

$has_promos = sizeof(yak_get_promotions(null, true)) > 0;

$referrer = yak_get_referrer();

// hide quantity input if required
if (yak_get_option(HIDE_QUANTITY, 'off') == 'on') {
    $quantity_input_type = 'hidden';
}
else {
    $quantity_input_type = 'text';
}
?>

<form id="cart" name="cart" method="post" action="<?php echo yak_get_permalink('stage=cart') ?>">

<?php    
    if (isset($model['error_message'])) {
?>
    <div class="yak_error"><?php echo $model['error_message'] ?></div>
<?php
    }
?>
    
    <table id="shoppinglist1" class="yak_order">
        <col width="55%" />
        <col width="15%" />
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        
        <tr>
            <th><?php _e('Item', 'yak') ?></th>
            <th class="yak_numeric"><?php _e('Price', 'yak') ?></th>
            <th class="yak_numeric"><?php 
                if ($quantity_input_type != 'hidden') {
                    _e('Qty', 'yak');
                }
            ?></th>
            <th class="yak_numeric"><?php _e('Subtotal', 'yak') ?></th>
            <th><?php _e('Remove', 'yak') ?></th>
        </tr>
<?php
    $total_price = 0;
    $total_quantity = 0;
    $total_weight = 0;
    $total_discount = 0;

    $totals = yak_get_totals($_SESSION[ITEMS_NAME]);
    
    if (!empty($_SESSION['promo_code'])) {
        $promo = yak_get_promotion($_SESSION['promo_code']);
    }
    else {
        $promo = yak_get_promotion_by_threshold($totals->price, $totals->quantity);
    }

    foreach ($_SESSION[ITEMS_NAME] as $key => $item) {
        $item->price = yak_calc_price($item->id, $item->cat_id, $item->price);
        $item->discount = yak_calc_price_discount($item->id, $item->quantity, $item->price, $totals->quantity, $totals->price, $promo);
        $total_discount += ($item->discount * $item->quantity);
        
        $total_price += $item->get_discount_total();
        $total_quantity += $item->quantity;
        
        $qtyid = 'item_' . $item->id . '_' . $item->cat_id;
        if (isset($item->selected_options)) {
            $qtyid .= '_' . rawurlencode(str_replace(' ', '_', implode('_', $item->selected_options)));
        }
?>
        <tr>
            <td class="yak_left"><a href="<?php echo apply_filters('the_permalink', yak_get_blogurl() . '?' . $item->id_type . '=' . $item->id) ?>"><?php echo $item->name ?></a></td>
            <td class="yak_numeric"><?php echo yak_format_money($item->price); ?></td>
            <td class="yak_center"><input id="<?php echo $qtyid ?>" name="<?php echo $qtyid ?>" type="<?php echo $quantity_input_type ?>" style="text-align: right" value="<?php echo $item->quantity ?>" size="<?php echo yak_get_option(QUANTITY_INPUT_SIZE, 3) ?>" maxlength="<?php echo yak_get_option(QUANTITY_INPUT_SIZE, 3) ?>" onkeydown="if (event.keyCode == 13) {return false;}" /></td>
            <td class="yak_numeric"><?php echo yak_format_money($item->get_total()) ?></td>
            <td><button id="deletebutton_<?php echo $item->id . '_' . $item->cat_id ?>" class="yak_button" type="submit" onclick="return deleteProduct('#<?php echo $qtyid ?>')"><?php _e(' - ', 'yak') ?></button></td>
        </tr>
<?php
        if (isset($item->selected_options)) {
?>
        <tr>
            <td class="yak_left" colspan="5">
<?php
            foreach ($item->selected_options as $mitem) {
                echo "&nbsp;&nbsp;" . __($mitem, 'yak') . "<br />";
            }
?>          
            </td>
        </tr>
<?php
        }

        if (count($item->meta) > 0) {
?>
        <tr>
            <td class="yak_left" colspan="5">
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
    
    if ($promo != null && $total_discount > 0) {
?>
        <tr>
            <td class="yak_left"><?php _ye('Total Discount (%s)', 'yak', __($promo->description, 'yak')) ?></td>
            <td></td>
            <td></td>
            <td class="yak_numeric">-<?php echo yak_format_money($total_discount) ?></td>
            <td></td>
        </tr>
<?php
    }
?>
        <tr>
            <td colspan="3" class="yak_total"><?php _e('Total', 'yak') ?></td>
            <td class="yak_total"><?php echo yak_format_money($total_price, true) ?></td>
            <td></td>
        </tr>
        
        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>
        
<?php
        if ($has_promos) {
?>
        <tr>
            <td colspan="3"><?php _e('Enter a promotion code, if you have one:', 'yak') ?></td>
            <td class="yak_right"><input type="text" id="promo_code" name="promo_code" maxlength="20" size="10" value="<?php echo $_SESSION["promo_code"] ?>" onkeydown="if (event.keyCode == 13) {return false;}" /></td>
            <td></td>
        </tr>
<?php
        }
        
        // only display the update button if the quantity input is to be displayed
        if ($quantity_input_type != 'hidden' && $hide_update_button != 'on') {
?>
        <tr>
            <td colspan="3"></td>
            <td class="yak_right"><button id="updatebutton" class="yak_medium_button" type="submit" onclick="return updateQuantities()"><?php _e('Update', 'yak') ?></button></td>
            <td></td>
        </tr>
<?php
        }
?>

        <tr>
            <td class="yak_right"><?php if (count($payment_types) > 1) { _e('How would you like to pay?', 'yak'); } ?></td>
            <td class="yak_right" colspan="2">
<?php       if (count($payment_types) > 1) {
                $sel = '';
                if (isset($_COOKIE['selected_payment_type'])) {
                    $i = 0;
                    foreach ($payment_types as $key=>$val) {
                        if ($i == $_COOKIE['selected_payment_type']) {
                            $sel = $key;
                        }
                        $i++;
                    }
                }
                echo yak_html_select(array('name'=>PAYMENT_TYPE, 'selected'=>$sel, 'values'=>array_keys($payment_types), 'nokey'=>true)); 
            } 
            else {
                foreach ($payment_types as $key=>$value) {
?>
        <input type="hidden" name="payment_type" value="<?php echo $key ?>" />
<?php           
                }
            }
?></td> 
            <td class="yak_right"><button id="buybutton" class="yak_medium_button" type="submit" onclick="javascript:document.forms['cart'].action.value = 'address'"><?php _e('Checkout', 'yak') ?></button></td>
            <td></td>
        </tr>

        <?php
                if (!empty($referrer)) {
        ?>
        <tr>
            <td class="yak_left" colspan="5"><br /><?php echo $referrer ?></td>
        </tr>
        <?php          
                }
        ?>
    </table>
        
    <input type="hidden" id="yakaction" name="action" value="" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
</form>