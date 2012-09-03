<?php
/*
See yak-for-wordpress.php for information and license terms
*/
global $model;
?>
<div class="wrap">
<form method="post" action="#">
<h2><?php _e('Products', 'yak-admin') ?></h2>

<div class="tablenav">
<div class="alignright">
  <input type="submit" name="products_update" value="<?php _e('Update products', 'yak-admin') ?>" class="button" />
</div><br class="clear" />
</div><br class="clear" />

    <table class="widefat collapsible">
    <colgroup>
        <col width="10%" />
        <col width="45%" />
        <col width="20%" />
        <col width="20%" />
        <col width="5%" />
    </colgroup>
    <thead>
        <tr>
            <th scope="col"><?php _e('ID', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Page/Post Title', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Product Title', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Price', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Require Login', 'yak-admin') ?></th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>     

<?php
if ($products) {
foreach ($products as $product) {
    
$class = 'even' == $class ? '' : 'even';
?>
        <tr class="<?php echo $class ?>">
            <td><?php echo $product->id ?><input type="hidden" name="id[]" value="<?php echo $product->id; ?>" /></td>
            <td><strong><a href="<?php echo get_settings('siteurl'); ?>/wp-admin/post.php?action=edit&post=<?php echo $product->id ?>"><?php echo $product->post_title ?></a></strong></td>
            <td><input type="text" size="40" name="title_<?php echo $product->id ?>" value="<?php echo yak_fix_escaping($product->title) ?>" /></td>
            <td><input type="text" size="6" name="price_<?php echo $product->id ?>" value="<?php echo $product->price ?>" /></td>
            <td><input type="checkbox" name="<?php echo REQUIRE_LOGIN ?>_<?php echo $product->id ?>" <?php yak_html_checkbox($product->require_login) ?> /></td>
            <td class="collapsible" id="product-<?php echo $product->id ?>"></td>
        </tr>
        <tr class="expand-child <?php echo $class ?>">
            <td colspan="6">
                <table>
                    <tr>
                        <td><?php _e('Short Description', 'yak-admin') ?>:</td>
                        <td>
                            <input type="text" name="description_<?php echo $product->id ?>" value="<?php echo $product->description ?>" size="100" maxlength="255" />
                        </td>                    
                    </tr>
                    <tr>
                        <td><?php _e('Multi-select options', 'yak-admin') ?>:</td>
                        <td>
                            <textarea name="multi_select_<?php echo $product->id ?>" cols="80" rows="3"><?php echo $product->multi_select_options ?></textarea>
                        </td>                    
                    </tr>
                    <tr>
                        <td><?php _e('Minimum/maximum multi-select options', 'yak-admin') ?>:</td>
                        <td><input type="text" name="multi_select_min_<?php echo $product->id ?>" value="<?php echo $product->multi_select_min ?>" size="4" /> - <input type="text" name="multi_select_max_<?php echo $product->id ?>" value="<?php echo $product->multi_select_max ?>" size="4" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php
                                $pid = $product->id;
                                include 'yak-view-product-snippet.php';
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
<?php }} ?>
    </tbody>
    </table>
    <div class="submit">
    <input type="submit" name="products_update" value="<?php _e('Update products', 'yak-admin') ?>" />
    </div>

</form>

</div>
