<?php
/*
See yak-for-wordpress.php for information and license terms
*/
global $model, $countries, $states, $canada_states;

$imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';

wp_nonce_field('update-options');
?>

<br />
<input type="hidden" name="section" value="options" />

<ul class="tabs">
    <li><a id="basic-tab" href="#basic"><span><?php _e('Basic', 'yak-admin') ?></span></a></li>
    <li><a id="cty-tax-tab" href="#countrytax"><span><?php _e('Country Tax', 'yak-admin') ?></span></a></li>
    <li><a id="us-tax-tab" href="#usstatetax"><span><?php _e('US State Tax', 'yak-admin') ?></span></a></li>
    <li><a id="ca-tax-tab" href="#canadastatetax"><span><?php _e('CA State Tax', 'yak-admin') ?></span></a></li>
</ul>    

<div class="panes">
    <div>
        <form name="settingsFrm1" method="post" action="#basic">

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Enable sales tax', 'yak-admin') ?></th>
                    <td><input type="checkbox" name="<?php echo ENABLE_SALES_TAX ?>" <?php yak_html_checkbox(yak_get_option(ENABLE_SALES_TAX, 'off')) ?> 
                            title="<?php _e('Should sales tax be calculated?', 'yak-admin') ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Display zero tax', 'yak-admin') ?></th>
                    <td><input type="checkbox" name="<?php echo DISPLAY_ZERO_TAX_CALC ?>" <?php yak_html_checkbox(yak_get_option(DISPLAY_ZERO_TAX_CALC, 'off')) ?>
                            title="<?php _e('Should sales tax be displayed if the calculation came to zero?', 'yak-admin') ?>" /></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" id="options_update1" name="options_update1" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm1', 'basic')" />
            </p>
        </form>
    </div>
    
    <div>        
        <form id="settingsFrm2" name="settingsFrm2" method="post" action="#fragment-2">
            <table class="widefat">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <thead>
                    <tr>
                        <th><?php _e('Country', 'yak-admin') ?></th>
                        <th><?php _e('Tax Calc', 'yak-admin') ?> <button type="button" onclick="clearInputs('#settingsFrm2')"><?php _e('Clear', 'yak-admin') ?></button></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($countries as $country=>$ignore) { 
                    $ctyname = $countries[$country];    
                ?>
                    <tr>
                        <td><?php echo $ctyname ?></td>
                        <td><input type="text" size="2" name="<?php echo 'yak_cty_tax_' . $country ?>" value="<?php echo yak_get_option('yak_cty_tax_' . $country) ?>"
                                title="<?php _ye('Enter the tax rate (as a fraction such as 0.125) for %s', 'yak-admin', $ctyname) ?>" /></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="options_update2" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm2', 'countrytax')" />
            </p>
        </form>
    </div>
    
    <div>        
        <form id="settingsFrm3" name="settingsFrm3" method="post" action="#usstatetax">
            <table class="widefat">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <thead>
                    <tr>
                        <th><?php _e('State', 'yak-admin') ?></th>
                        <th><?php _e('Tax Calc', 'yak-admin') ?> <button type="button" onclick="clearInputs('#settingsFrm3')"><?php _e('Clear', 'yak-admin') ?></button></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($states as $state=>$ignore) { 
                    $statename = $states[$state];    
                ?>
                    <tr>
                        <td><?php echo $statename ?></td>
                        <td><input type="text" size="2" name="<?php echo 'yak_us_tax_' . $state ?>" value="<?php echo yak_get_option('yak_us_tax_' . $state) ?>" 
                                title="<?php _ye('Enter the tax rate (as a fraction such as 0.125) for %s', 'yak-admin', $statename) ?>" /></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="options_update3" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm3', 'usstatetax')" />
            </p>
        </form>
    </div>
    
    <div>
        <form id="settingsFrm4" name="settingsFrm4" method="post" action="#canadastatetax">
            <table class="widefat">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <thead>
                    <tr>
                        <th><?php _e('State', 'yak-admin') ?></th>
                        <th><?php _e('Tax Calc', 'yak-admin') ?> <button type="button" onclick="clearInputs('#settingsFrm4')"><?php _e('Clear', 'yak-admin') ?></button></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($canada_states as $state=>$ignore) { 
                    $statename = $canada_states[$state];    
                ?>
                    <tr>
                        <td><?php echo $statename ?></td>
                        <td><input type="text" size="2" name="<?php echo 'yak_ca_tax_' . $state ?>" value="<?php echo yak_get_option('yak_ca_tax_' . $state) ?>" 
                                title="<?php _ye('Enter the tax rate (as a fraction such as 0.125) for %s', 'yak-admin', $statename) ?>" /></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="options_update4" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm4', 'canadastatetax')" />
            </p>
        </form>
    </div>

</div>

<script type="text/javascript">
var $j = jQuery.noConflict();
$j(function() {
    setupAdminPage();
});
</script>