<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-utils.php');

global $model, $countries;

$imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';

wp_nonce_field('update-options');

$weight_val = $model[SHIPPING_WEIGHT_CALC];
if (empty($weight_val)) {
    $weight_val = DEFAULT_SHIPPING_WEIGHT_CALC;
}
?>

<br />
<input type="hidden" name="section" value="options" />

<ul class="tabs">
    <li><a id="basic-tab" href="#basic"><span><?php _e('Basic', 'yak-admin') ?></span></a></li>
    <li><a id="zones-tab" href="#zones"><span><?php _e('Zones', 'yak-admin') ?></span></a></li>
    <li><a id="options-tab" href="#options"><span><?php _e('Options', 'yak-admin') ?></span></a></li>
    <li><a id="options-tab" href="#paymentshippingpairs"><span><?php _e('Payment-Shipping Pairs', 'yak-admin') ?></span></a></li>
</ul>    

<div class="panes">
    <div>        
        <form name="settingsFrm1" method="post" action="#basic">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Include Shipping Costs', 'yak-admin') ?></th>
                    <td><?php echo yak_html_select(array('name'=>INCLUDE_SHIPPING_COSTS, 'selected'=>$model[INCLUDE_SHIPPING_COSTS], 'values'=>array("yes"=>"yes", "no"=>"no"),
                                'title'=>__('Include shipping costs in order totals?', 'yak-admin'))) ?></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Cookie Lifetime (days)', 'yak-admin') ?></th>
                    <td><input type="text" name="<?php echo COOKIE_LIFETIME ?>" value="<?php echo $model[COOKIE_LIFETIME] ?>"
                            title="<?php _e('How long should the customer\'s address cookie be stored on their machine?', 'yak-admin') ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Default Country', 'yak-admin') ?></th>
                    <td><?php echo yak_html_select(array('name'=>DEFAULT_COUNTRY, 'selected'=>$model[DEFAULT_COUNTRY], 'values'=>$countries,
                                'title'=>__('What country should be preselected in the dropdown?', 'yak-admin'))) ?></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Shipping Address', 'yak-admin') ?></th>
                    <td>
                    <label for="<?php echo ADDRESS_NAME ?>"> <input type="checkbox" id="<?php echo ADDRESS_NAME ?>" name="<?php echo ADDRESS_NAME ?>" <?php yak_html_checkbox($model[ADDRESS_NAME]) ?> /> <?php _e('Name', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS_COMPANY_NAME ?>"> <input type="checkbox" id="<?php echo ADDRESS_COMPANY_NAME ?>" name="<?php echo ADDRESS_COMPANY_NAME ?>" <?php yak_html_checkbox($model[ADDRESS_COMPANY_NAME]) ?> /> <?php _e('Company Name', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS_PHONE ?>"> <input type="checkbox" id="<?php echo ADDRESS_PHONE ?>" name="<?php echo ADDRESS_PHONE ?>" <?php yak_html_checkbox($model[ADDRESS_PHONE]) ?> /> <?php _e('Phone', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS_SUBURB ?>"> <input type="checkbox" id="<?php echo ADDRESS_SUBURB ?>" name="<?php echo ADDRESS_SUBURB ?>" <?php yak_html_checkbox($model[ADDRESS_SUBURB]) ?> /> <?php _e('Suburb', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS_POSTCODE ?>"> <input type="checkbox" id="<?php echo ADDRESS_POSTCODE ?>" name="<?php echo ADDRESS_POSTCODE ?>" <?php yak_html_checkbox($model[ADDRESS_POSTCODE]) ?> /> <?php _e('Postcode/Zipcode', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS ?>"> <input type="checkbox" id="<?php echo ADDRESS ?>" name="<?php echo ADDRESS ?>" <?php yak_html_checkbox($model[ADDRESS]) ?> /> <?php _e('Address', 'yak-admin') ?></label><br />
                    <label for="<?php echo ADDRESS_SEPARATE_BILLING ?>"> <input type="checkbox" id="<?php echo ADDRESS_SEPARATE_BILLING ?>" name="<?php echo ADDRESS_SEPARATE_BILLING ?>" <?php yak_html_checkbox($model[ADDRESS_SEPARATE_BILLING]) ?> /> <?php _e('Separate Billing Address', 'yak-admin') ?></label></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Shipping Notes', 'yak-admin') ?></th>
                    <td><textarea cols="40" rows="2" name="<?php echo SHIPPING_NOTES ?>"
                            title="<?php _e('Notes about shipping to add to the bottom of the confirmation screen.', 'yak-admin') ?>"><?php echo $model[SHIPPING_NOTES] ?></textarea></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Shipping weight calculation value', 'yak-admin') ?></th>
                    <td><input type="text" id="<?php echo SHIPPING_WEIGHT_CALC ?>" name="<?php echo SHIPPING_WEIGHT_CALC ?>" value="<?php echo $model[SHIPPING_WEIGHT_CALC] ?>"
                            title="<?php _e('The weight value, in grams, that should be used for calculating shipping cost. This defaults to 100gms if not set and is used for the "shipping first" calculation and then the subsequent "per x grams" calculation.', 'yak-admin') ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Shipping options', 'yak-admin') ?></th>
                    <td><textarea id="<?php echo SHIPPING_OPTION_NAMES ?>" name="<?php echo SHIPPING_OPTION_NAMES ?>"
                            title="<?php _e('A list of shipping options (separated by newlines). These will be displayed in a dropdown from which the customer can select.', 'yak-admin') ?>"><?php echo $model[SHIPPING_OPTION_NAMES] ?></textarea></td>
                </tr>
            </table>
            
            <?php
                if (current_user_can('edit_yak_settings')) {
            ?>
            <p class="submit">
                <input type="submit" name="options_update1" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm1', 'basic')" />
            </p>
            <?php
                }
            ?>
        </form>
    </div>

    <div>
        <form id="settingsFrm2" name="settingsFrm2" method="post" action="#zones">
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Country', 'yak-admin') ?></th>
                    <th><?php _e('Zone', 'yak-admin') ?> <button id="clear_zones" type="button" onclick="clearInputs('#settingsFrm2', 'text')"><?php _e('Clear zones', 'yak-admin') ?></button></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($countries as $country=>$ignore) { 
                $ctyname = $countries[$country];    
            ?>
                <tr>
                    <td><?php echo $ctyname ?></td>
                    <td><input type="text" size="2" name="<?php echo 'yak_' . $country . '_zone' ?>" value="<?php echo $model['yak_' . $country . '_zone'] ?>"
                            title="<?php _ye('Enter the shipping zone for %s', 'yak-admin', $ctyname) ?>" /></td>
                </tr>
            <?php } ?>
                </tbody>
            </table>
            
            <?php
                if (current_user_can('edit_yak_settings')) {
            ?>
            <p class="submit">
                <input type="submit" name="options_update2" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm2', 'zones')" />
            </p>
            <?php
                }
            ?>
        </form>
    </div>
    
    <div>
        <form name="settingsFrm3" method="post" action="#options">
        <?php foreach ($model[SHIPPING_OPTIONS] as $shipping_option) { ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Shipping Option:', 'yak-admin') ?> <?php echo $shipping_option->name ?></th>
                    <th colspan="3"></th>
                </tr>
                <tr>
                    <th><?php _e('Zone', 'yak-admin') ?></th>
                    <th><?php _e('Fixed Total Cost', 'yak-admin') ?></th>
                    <th><?php _e('Fixed Cost per item', 'yak-admin') ?><br />
                        <?php _e('(First/Subsequent)', 'yak-admin') ?></th>
                    <th><?php _ye('Cost per %s gm', 'yak-admin', $weight_val) ?><br />
                        <?php _ye('(First %s gm/Subsequent)', 'yak-admin', $weight_val) ?></th>
                </tr>
                <?php foreach ($shipping_option->zones as $zone) { 
                    $fixed = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixed');
                    $fixeditemfirst = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixeditemfirst');
                    $fixeditem = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixeditem');
                    $weightfirst = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'weightfirst');
                    $weight = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'weight');
                    
                    $idfixed = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixed', false);
                    $idfixeditemfirst = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixeditemfirst', false);
                    $idfixeditem = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'fixeditem', false);
                    $idweightfirst = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'weightfirst', false);
                    $idweight = yak_get_shipping_varname($shipping_option->code, $zone->zone, 'weight', false);
                ?>
                <tr>
                    <th><?php _ye('Zone %s', 'yak-admin', $zone->zone) ?> <span style="font-size: xx-small">(<?php echo $zone->get_countries_list() ?>)</span></th>
                    <td><input type="text" size="5" id="<?php echo $idfixed ?>" name="<?php echo $fixed ?>" value="<?php echo $zone->fixed ?>"
                            title="<?php _ye('Fixed shipping code for zone %s (no matter how many items)', 'yak-admin', $zone->zone) ?>" /></td>
                    <td><input type="text" size="5" id="<?php echo $idfixeditemfirst ?>" name="<?php echo $fixeditemfirst ?>" value="<?php echo $zone->fixeditemfirst ?>"
                            title="<?php _ye('Fixed shipping code for zone %s (first item)', 'yak-admin', $zone->zone) ?>" />
                        <input type="text" size="5" id="<?php echo $idfixeditem ?>" name="<?php echo $fixeditem ?>" value="<?php echo $zone->fixeditem ?>"
                            title="<?php _ye('Fixed shipping code for zone %s (subsequent items)', 'yak-admin', $zone->zone) ?>" /></td>
                    <td><input type="text" size="5" id="<?php echo $idweightfirst ?>" name="<?php echo $weightfirst ?>" value="<?php echo $zone->weightfirst ?>"
                            title="<?php _ye('Cost by weight for zone %s (first)', 'yak-admin', $zone->zone) ?>" />
                        <input type="text" size="5" id="<?php echo $idweight ?>" name="<?php echo $weight ?>" value="<?php echo $zone->weight ?>"
                            title="<?php _ye('Cost by weight for zone %s (subsequent multiples of %s)', 'yak-admin', $zone->zone, $weight_val) ?>" /></td>
                </tr>
                <?php } ?>
            </thead>
            <tbody>
        </table>
        <br />
        <?php } ?>

        <?php
            if (current_user_can('edit_yak_settings')) {
        ?>            
        <p class="submit">
            <input type="submit" name="options_update3" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm3', 'options')" />
        </p>
        <?php
            }
        ?>
        
        </form>
        
    </div>
    
    <div>
        <form name="settingsFrm4" method="post" action="#paymentshippingpairs">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><strong><?php _e('Set Payment-Shipping Pairs', 'yak-admin') ?></strong></th>
            <td>
                <table id="payment_shipping_pairs" class="form-table">
                    <tr>
                        <th><?php _e('Payment', 'yak-admin') ?> <button class="image-button" type="button" onclick="javascript:copyRow('#payment_shipping_pairs')"><img src="<?php echo $imgbase ?>/add.gif" alt="flip" border="0" /></button></th>
                        <th><?php _e('Shipping Type', 'yak-admin') ?></th>
                    </tr>
					<?php 
					    foreach ($model[SHIPPING_OPTIONS] as $shipment) {
							$shipments[$shipment->name] = $shipment->name;
						}
						$payments[] = ""; 
						if (isset($model[PAYMENT_TYPES])) {
    						$payments = array_merge($payments, array_flip($model[PAYMENT_TYPES]));
    						if (isset($model[PAYMENT_SHIPPING_PAIRS])) {
    						    foreach ($model[PAYMENT_SHIPPING_PAIRS] as $key=>$arr) {
    								foreach ($arr as $ikey=>$value) { 
					?>
                    <tr>
                        <td><?php echo yak_html_select(array('name'=>'payment_type_names[]', 'selected'=>$key, 'values'=>$payments, 'nokey'=>true, 'debug'=>true)) ?></td>
                        <td><?php echo yak_html_select(array('name'=>'shipping_type_names[]', 'selected'=>$value, 'values'=>$shipments, 'debug'=>true)) ?></td>
                        <td></td>
                    </tr>
					<?php 
    						        }
    						    }
    					    }
    					}   
					?>
					<tr>
                        <td><?php echo yak_html_select(array('name'=>'payment_type_names[]', 'values'=>$payments, 'nokey'=>true)) ?></td>
                        <td><?php echo yak_html_select(array('name'=>'shipping_type_names[]', 'values'=>$shipments)) ?></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>            
        <p class="submit">
            <input type="submit" name="options_update4" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm4', 'paymentshippingpairs')" />
        </p>
    <?php
        }
    ?>
        
        </form>
        
    </div>


<script type="text/javascript">
var $j = jQuery.noConflict();
$j(function() {
    setupAdminPage();
});
</script>