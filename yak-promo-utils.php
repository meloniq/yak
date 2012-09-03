<?php
if (!function_exists('yak_allowed_promo')) {
    /**
     * return true if the current user is allowed access to a promo.
     * Note: defaults to true if the promo has no users assigned.
     */
    function yak_allowed_promo($promo) {
        global $wpdb, $user_ID;
        
        if (count($promo->users) > 0) {
            if (!isset($user_ID) || $user_ID == null) {
                return false;
            }
            $sql = $wpdb->prepare("select user_nicename
                                   from $wpdb->users
                                   where ID = %d", $user_ID);
            $row = $wpdb->get_row($sql);
            if (!in_array($row->user_nicename, $promo->users)) {
                return false;
            }
        }
        $rtn = apply_filters('yak-allowed-promo', $promo);
        return $rtn != null;
    }
}

if (!function_exists('yak_product_allowed_in_promo')) {
    function yak_product_allowed_in_promo($promo, $product_id) {
        if ($promo == null) {
            return false;
        }
        else if ($promo->products == null || count($promo->products) == 0) {
            return true;
        }
        
        $in = in_array($product_id, $promo->products);
        if ($promo->products_inclusion == 0) {
            $in = !$in;
        }
        
        return $in;
    }
}


if (!function_exists('yak_calc_discount_price')) {
    /**
     * Calculate a price discount, or return 0 if not a pricing promotion
     */
    function yak_calc_price_discount($product_id, $item_quantity, $price, $total_items, $total_price, $promo=null) {
        if ($promo != null && yak_product_allowed_in_promo($promo, $product_id)) {
            if (yak_str_contains($promo->promo_type, 'pricing_perc')) {
                return ($promo->value / 100.0) * $price;
            }
            else if (yak_str_contains($promo->promo_type, 'pricing_val')) {
                if ($total_price > 0) {
                    $item_perc_of_total = $price / $total_price;
                    return $promo->value * $item_perc_of_total;
                }
            }
        }
        return 0;
    }
}

if (!function_exists('yak_calc_discount_shipping')) {
    /**
     * Calculate a shipping discount, or return 0 if not a shipping discount
     */
    function yak_calc_shipping_discount($shipping_cost, $promo=null, $items) {
        if ($promo != null && ($promo->products == null || yak_contains_product($items, $promo->products))) {
            if (yak_str_contains($promo->promo_type, 'shipping_perc')) {
                return ($promo->value / 100.0) * $shipping_cost;
            }
            else if (yak_str_contains($promo->promo_type, 'shipping_val')) {
                return min($promo->value, $shipping_cost);
            }
        }
        
        return 0;
    }
}


if (!function_exists('yak_get_promotion')) {
    function yak_get_promotion($code) {
        // find by code
        $promos = yak_get_promotions($code, true);
        if (sizeof($promos) >= 1) {
            return $promos[0];
        }
        
        // find by coupon
        $set = yak_get_coupon_by_code($code);
        if ($coupon_set != null && $set->used_datetime == null) {
            $promos = yak_get_promotions($set->coupon_set, true);
            if (sizeof($promos) >= 1) {
                return $promos[0];
            }
        }
        
        // no promo found
        return new YakPromotion('', null, null, null, 0, '', '');
    }
}


if (!function_exists('yak_get_promotion_by_threshold')) {
    function yak_get_promotion_by_threshold($price_threshold, $qty_threshold = null) {
        $promos = yak_get_promotions(null, true, $price_threshold, null);
        if (sizeof($promos) >= 1) {
            return $promos[0];
        }
        if ($qty_threshold != null) {
            $promos = yak_get_promotions(null, true, null, $qty_threshold);
        }
        
        if (sizeof($promos) >= 1) {
            return $promos[0];
        }
        else {
            return null;
        }
    }
}


if (!function_exists('yak_get_promotions')) {
    function yak_get_promotions($code = null, $valid = false, $price_threshold = null, $qty_threshold = null, $types = null) {
        global $wpdb, $promo_table, $promo_users_table, $coupon_table;
                
        $args = array();
        $sql = "select promo_id, code, promo_type, description, threshold, value, products_inclusion, expiry_date, products
                from $promo_table 
                where 1 = 1 ";
        
        // search by code        
        if (!empty($code)) {
            $sql .= "and ((code = %s ";
            $sql .= "and promo_type in ('shipping_perc', 'shipping_val', 'pricing_perc', 'pricing_val')) ";
            $args[] = $code;
            
            $sql .= "or code in (select coupon_set from $coupon_table where coupon_code = %s and used_datetime is null)) ";
            $args[] = $code;
        }
        // or search by price threshold
        else if (!empty($price_threshold)) {
            $sql .= "and (threshold is not null and %f >= threshold ";
            $sql .= "and promo_type in ('shipping_perc_threshold', 'shipping_val_threshold', 'pricing_perc_threshold', 'pricing_val_threshold')) ";
            $args[] = $price_threshold;
        }
        // or search by qty threshold
        else if (!empty($qty_threshold)) {
            $sql .= "and (threshold is not null and %d >= threshold ";
            $sql .= "and promo_type in ('pricing_perc_qty_threshold', 'pricing_val_qty_threshold', 'shipping_perc_qty_threshold', 'shipping_val_qty_threshold')) ";
            $args[] = $qty_threshold;
        }
        
        // check for validity
        if ($valid) {
            $sql .= "and (expiry_date is null or expiry_date >= current_date) ";
        }
        
        if ($types != null) {
            $sql .= "and promo_type in (";
            
            $count = count($types);
            for ($i = 0; $i < $count; $i++) {
                $sql .= '"' . $types[$i] . '"';
                if ($i < $count - 1) {
                    $sql .= ',';
                }
            }
            $sql .= ") ";
        }
        
        // final sql
        if (!empty($price_threshold) || !empty($qty_threshold)) {
            $sql .= "order by threshold desc limit 1";
        }
        else {
            $sql .= "order by promo_id asc";
        }
        
        $sql = $wpdb->prepare($sql, $args);
        $results = $wpdb->get_results($sql);
        
        $promos = array();
        foreach ($results as $result) {
            $sql = $wpdb->prepare("select user_nicename
                                   from $wpdb->users u
                                   where exists (select 1
                                                 from $promo_users_table pu 
                                                 where pu.promo_id = %d
                                                 and pu.user_id = u.ID)", $result->promo_id);
            $rows = $wpdb->get_results($sql);
            $users = array();
            foreach ($rows as $row) {
                $users[] = $row->user_nicename;
            }
            
            if (!empty($result->products)) {
                $products = explode(',', $result->products);
            }
            else {
                $products = null;
            }
            
            $promo = new YakPromotion($result->promo_id, $result->code, $result->promo_type, $result->threshold, $result->value, 
                                      $result->description, $result->expiry_date, $users, $products);
            $promo->products_inclusion = $result->products_inclusion;
            $promos[] = $promo;
        }
        
        return $promos;
    }
}

if (!function_exists('yak_coupons_options_link')) {
    function yak_coupons_options_link() {
        echo '<li><a id="coupons-tab" href="#coupons"><span>' . __('Coupons', 'yak-admin') . '</span></a></li>';
    }
}

if (!function_exists('yak_coupons_options')) {
    function yak_coupons_options() {
?>
        <div>    
        <form name="settingsFrmCoupons" method="post" enctype="multipart/form-data" action="#coupons">
<?php
            global $wpdb;

            $imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';
            $coupon_sets = yak_get_coupon_sets();

?>
            <h2><?php _e('Coupons', 'yak-admin') ?></h2>

            <p></p>

<?php 
                if (!empty($_REQUEST['error_message'])) { 
?>
                <div class="error"><?php echo $_REQUEST['error_message'] ?></div>
<?php
                }
?>

<?php
                if (count($coupon_sets) > 0) {
?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Coupon Set Name</th>
                        <th># of coupons</th>
                        <th># of used coupons</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
<?php
                    $x = 1;
                    foreach ($coupon_sets as $coupon_set) {
?>
                    <tr>
                        <td id="<?php echo 'coupon_set_' . $x ?>"><?php echo $coupon_set->coupon_set ?></td>
                        <td id="<?php echo 'total_' . $x ?>"><?php echo $coupon_set->total_coupons ?></td>
                        <td id="<?php echo 'used_' . $x ?>"><?php echo $coupon_set->used_coupons ?></td>
                        <td><button class="image-button" type="button" onclick="javascript:removeCouponSet('<?php echo $coupon_set->coupon_set ?>')"><img src="<?php echo $imgbase ?>/delete.gif" alt="flip" border="0" /></button></td>
                    </tr>
<?php
                        $x++;
                    }
?>
                </tbody>
            </table>
<?php
                }
?>

            <input type="hidden" id="delete_coupon_set" name="delete_coupon_set" value="" />

            <p>&nbsp;</p>
            <hr />

            <h3><?php _e('Upload a new coupon file', 'yak-admin') ?></h3>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Coupon Set Name', 'yak-admin') ?></th>
                    <td><input type="text" name="coupon_set_name" id="coupon_set_name" title="<?php _e('Name of the coupon set.', 'yak-admin') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Replace existing set', 'yak-admin') ?></th>
                    <td><input type="checkbox" name="replacement" id="replacement" title="<?php _e('If the coupon set name already exists should we replace the coupons, or just add to it?', 'yak-admin') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Filename', 'yak-admin') ?></th>
                    <td><input type="file" name="coupon_file" id="coupon_file" title="<?php _e('Select the file containing coupon codes.', 'yak-admin') ?>" /></td>
                </tr>
            </table>

<?php
                if (current_user_can('edit_yak_settings')) {
?>
            <p class="submit">
                <input type="hidden" name="yak-update-options-coupons" value="yak-update-options-coupons" />
                <input type="submit" id="options_update_coupons" name="options_update_coupons" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrmCoupons', 'coupons')" />
            </p>
<?php
                }
?>
        </form>
        </div>
<?php
    }
}

if (!function_exists('yak_coupons_update_options')) {
    function yak_coupons_update_options() {
        if (!empty($_POST['delete_coupon_set'])) {
            yak_delete_coupon_set($_POST['delete_coupon_set']);
        }
        else {
            $coupon_set = $_POST['coupon_set_name'];
            $replace = $_POST['replacement'];
            
            if ($replace == 'on') {
                yak_delete_coupon_set($coupon_set);
            }
            
            $f = @fopen($_FILES['coupon_file']['tmp_name'], 'r');
            $count = 0;
            if ($f != null) {
                while (!feof($f)) {
                    $buffer = fgets($f);
                    $buffer = trim($buffer);
                    yak_log($buffer);
                    if ($buffer != '') {
                        yak_add_coupon($coupon_set, $buffer);
                        $count++;
                    }
                }
                fclose($f);

                $coupon_set = yak_get_coupon_sets($coupon_set);
                if ($coupon_set == null || $coupon_set->total_coupons == 0) {
                    $_REQUEST['error_message'] = __('No coupons were inserted (either duplicates in the file, or pre-existing codes)', 'yak-admin');
                }
                else if ($count != $coupon_set->total_coupons) {
                    $_REQUEST['error_message'] = __('Not all coupons were inserted (either duplicates in the file, or pre-existing codes)', 'yak-admin');
                }
            }
        }
    }
}

if (!function_exists('yak_add_coupon')) {
    function yak_add_coupon($coupon_set, $code) {
        global $wpdb, $coupon_table;
        
        $sql = $wpdb->prepare("insert into $coupon_table (coupon_code, coupon_set) values (%s, %s)", $code, $coupon_set);
        $wpdb->query($sql);
        return $wpdb->insert_id;
    }
}

if (!function_exists('yak_get_coupon_sets')) {
    function yak_get_coupon_sets($name = null, $code = null) {
        global $wpdb, $coupon_table;
        
        $sql = "select c1.coupon_set, count(c1.coupon_id) as total_coupons, 
        	        (select count(coupon_id) 
        	         from $coupon_table c2 
        	         where c2.coupon_set = c1.coupon_set 
        	         and c2.used_datetime is not null) as used_coupons
                from $coupon_table c1";

        $args = array();
        if ($name != null) {
            $sql .= " where c1.coupon_set = %s";
            $args[] = $name;
        }
        else if ($code != null) {
            $sql .= " where c1.coupon_code = %s";
            $args[] = $code;
        }
        
        $sql .= " group by c1.coupon_set";
        
        $sql = $wpdb->prepare($sql, $args);
        $rows = $wpdb->get_results($sql);
        
        if (count($rows) > 0 && ($name != null || $code != null)) {
            return $rows[0];
        }
        else {
            return $rows;
        }
    }
}

if (!function_exists('yak_delete_coupon_set')) {
    function yak_delete_coupon_set($coupon_set) {
        global $wpdb, $coupon_table;
        
        $sql = $wpdb->prepare("delete from $coupon_table where coupon_set = %s", $coupon_set);
        $wpdb->query($sql);
    }
}

if (!function_exists('yak_get_coupon_by_code')) {
    function yak_get_coupon_by_code($code) {
        global $wpdb, $coupon_table;
        
        $sql = $wpdb->prepare("select *
                               from $coupon_table
                               where coupon_code = %s", $code);
        return $wpdb->get_row($sql);
    }
}

if (!function_exists('yak_use_coupon')) {
    function yak_use_coupon($coupon_set, $code) {
        global $wpdb, $coupon_table;
        
        $sql = $wpdb->prepare("update $coupon_table set used_datetime = current_timestamp()
                               where coupon_set = %s and coupon_code = %s", $coupon_set, $code);
        $wpdb->query($sql);
    }
}

add_action('yak-misc-options-link', 'yak_coupons_options_link');
add_action('yak-misc-options', 'yak_coupons_options');
add_action('yak-update-options-coupons', 'yak_coupons_update_options');
?>