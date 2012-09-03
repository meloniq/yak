<?php
/*
See yak-for-wordpress.php for information and license terms
*/
$pid = $_GET['post'];

$page = yak_is_page();

$price = '';
$title = '';

if (isset($pid) && $pid != null) {
    $product = yak_get_product($pid);
    $price = $product->price;
    $title = $product->title;
    $product->types = yak_get_product_categories($pid, $product->status, true, true);
}
else {
    $product = new YakProduct('', '', '', '', '');
    $product->types = array();
}

if ($page && count($product->types) == 0) {
    $product->types[] = new YakProductType($pid, -1, 'default', '', 0, null, null, '');
}
?>

<input type="hidden" name="yak_action" value="Update-Product" />
<p><?php _e('Set the price of your product', 'yak-admin') ?>: <input type="text" name="yak_price" value="<?php echo $price ?>" size="10" /></p>
<p><?php _e('Allow custom price entry', 'yak-admin') ?>: <input type="checkbox" name="<?php echo CUSTOM_PRICE ?>" <?php yak_html_checkbox($product->custom_price) ?> /></p>
<p><?php _e('Set the display title', 'yak-admin') ?>: <input type="text" name="yak_title" value="<?php echo $title ?>" size="50" /></p>
<p><?php _e('Set a short description', 'yak-admin') ?>: <input type="text" name="yak_description" value="<?php echo $product->description ?>" size="80" maxlength="255" /></p>
<p><?php _e('Set an override discount', 'yak-admin') ?>: <input type="text" name="yak_discount_override" value="<?php echo $product->discount_override ?>" size="10" /> <?php _e('Note: enter a fraction (e.g. 0.75 for 25% discount)', 'yak-admin') ?></p>
<p><?php _e('Require login to purchase this product', 'yak-admin') ?>: <input type="checkbox" name="<?php echo REQUIRE_LOGIN ?>" <?php yak_html_checkbox($product->require_login) ?> /></p>
<p><?php _e('Add multiselect options', 'yak-admin') ?>:<br />
    <textarea name="yak_multi_select" cols="80" rows="3"><?php echo $product->multi_select_options ?></textarea>
</p>
<p><?php _e('Set the minimum and maximum number of multi-select options', 'yak-admin') ?>:
    <input type="text" name="yak_multi_select_min" value="<?php echo $product->multi_select_min ?>" size="5" /> - <input type="text" name="yak_multi_select_max" value="<?php echo $product->multi_select_max ?>" size="5" />
</p>
<p><?php _e('Number of columns in the multi-select table', 'yak-admin') ?>: <input type="text" name="yak_multi_select_cols" value="<?php echo $product->multi_select_cols ?>" size="4" maxlength="2" /></p>
<?php
    $pid = $_GET['post'];
    include 'yak-view-product-snippet.php';
?>
