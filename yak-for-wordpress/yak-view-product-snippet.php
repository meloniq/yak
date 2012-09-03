<?php
$prod_types = yak_get_product_types(true, $product->types);
$table_id = "product_types_" . $product->id;
$imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';

if (yak_is_page()) {
    $include_types = false;
}
else {
    $include_types = true;
}
?>
<table id="<?php echo $table_id ?>" class="lowlight">
    <tr>
        <th colspan="2">Type <?php if ($include_types) { ?><button class="image-button" type="button" onclick="javascript:addProductRow('#<?php echo $table_id ?>')"><img src="<?php echo $imgbase ?>/add.gif" alt="flip" border="0" /></button><?php } ?></th>
        <th>Quantity</th>
        <th>Override Price</th>
        <th>Weight</th>
        <th>Download file</th>
        <th>SKU</th>
        <th></th>
    </tr>
<?php 
    if (!empty($product->types)) {
        foreach ($product->types as $type) { 
            $name_suffix = $product->id . '_' . $type->cat_id;
?>
    <tr>
        <td colspan="2"><?php echo $type->name ?></td>
        <td><input type="text" size="6" name="qty_<?php echo $name_suffix ?>" value="<?php echo $type->qty ?>" />
            <input type="hidden" name="oldqty_<?php echo $name_suffix ?>" value="<?php echo $type->qty ?>" /></td>
        <td><input type="text" size="8" name="price_<?php echo $name_suffix ?>" value="<?php echo $type->override_price ?>" /></td>
        <td><input type="text" size="8" name="weight_<?php echo $name_suffix ?>" value="<?php echo $type->weight ?>" /></td>
        <td><input type="text" size="20" name="dl_file_<?php echo $name_suffix ?>" value="<?php echo $type->dl_file ?>" /></td>
        <td><input type="text" size="13" name="sku_<?php echo $name_suffix ?>" value="<?php echo $type->sku ?>" /></td>
        <td><input type="hidden" name="delete_<?php echo $name_suffix ?>" value="" /><?php if ($include_types) { ?><button class="image-button" type="button" onclick="javascript:removeExistingProductType(this, 'delete_<?php echo $name_suffix ?>')"><img src="<?php echo $imgbase ?>/delete.gif" alt="flip" border="0" /></button><?php } ?></td>
    </tr>
<?php   
        }
    }
?>

    <tr style="visibility: hidden">
        <td><?php echo yak_html_select(array('name'=>'newtype_' . $product->id . '[]', 'values'=>$prod_types)) ?>
            <input style="visibility: hidden; display: none" type="text" name="newtype_name_<?php echo $product->id ?>[]" value="" size="8" /></td>
        <td><button class="image-button" type="button" onclick="javascript:newProductToggle('#<?php echo $table_id ?>')"><img src="<?php echo $imgbase ?>/flip.gif" alt="flip" border="0" /></button></td>
        <td><input type="text" size="6" name="newtype_qty_<?php echo $product->id ?>[]" value="" /></td>
        <td><input type="text" size="8" name="newtype_price_<?php echo $product->id ?>[]" value="" /></td>
        <td><input type="text" size="8" name="newtype_weight_<?php echo $product->id ?>[]" value="" /></td>
        <td><input type="text" size="20" name="newtype_dl_file_<?php echo $product->id ?>[]" value="" /></td>
        <td><input type="text" size="13" name="newtype_sku_<?php echo $product->id ?>[]" value="" /></td>
        <td></td>
    </tr>
</table>