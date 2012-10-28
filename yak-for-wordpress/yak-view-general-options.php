<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-utils.php');
require_once('yak-currencies.php');

global $model, $payment_url_examples, $countries, $promo_types, $order_number_types, $duplicate_handling, $currencies, $order_statuses;

$imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';

wp_nonce_field('update-options');

$payments = yak_get_payment_opts();
$payment_pages = $payments['pages'];

$product_inclusions = array('1'=>'include', '0'=>'exclude');

$used_options = array();

?>

<br />
<input type="hidden" name="section" value="options" />

<ul class="tabs">
    <li><a id="about-tab" href="#about"><span><?php _e('About', 'yak-admin') ?></span></a></li>
    <li><a id="basic-tab" href="#basic"><span><?php _e('Basic', 'yak-admin') ?></span></a></li>
    <li><a id="priceqty-tab" href="#products"><span><?php _e('Products price/quantity', 'yak-admin') ?></span></a></li>
    <li><a id="download-tab" href="#download"><span><?php _e('Download', 'yak-admin') ?></span></a></li>
    <li><a id="payments-tab" href="#payments"><span><?php _e('Payments', 'yak-admin') ?></span></a></li>
    <li><a id="advanced-tab" href="#advanced"><span><?php _e('Advanced', 'yak-admin') ?></span></a></li>
    <li><a id="promotions-tab" href="#promotions"><span><?php _e('Promotions', 'yak-admin') ?></span></a></li>
</ul>

<div class="panes">
<div>
<h2><?php _e('About YAK for WordPress', 'yak-admin') ?></h2>

<?php
    if (ini_get('session.auto_start') == '1') {
?>

    <p style="color: red; font-weight: bold">
        WARNING: session.auto_start is turned on in your php.ini file. YAK <i>may</i> not function correctly with this setting.<br />
        If you experience problems and don't have access to your php.ini, you may be able to resolve it by adding a <code>.htaccess</code>
        file in the WordPress root directory and adding: <code>php_value session.auto_start 0</code>
    </p>

<?php
    }
?>

    <p>YAK (Yet Another Kart) is a shopping cart plugin for WordPress, allowing a product to be associated
       with a post or a page, and provides support for online payment (such as PayPal), and downloadable content.<br />
       Basic step-by-step installation instructions can be found <a href="http://wordpress.org/extend/plugins/yak-for-wordpress/installation/" target="_BLANK">here</a>.
       For detailed instructions, including how to setup external payments gateways (such as Authorize.net, PayPal, etc), 
       consider purchasing the YAK <a href="http://afillyateit.com/yak-for-wordpress/handbook">Handbook</a>.</p>
       
    <p class="yak_warning_box">
       Make sure you check the <a href="http://wordpress.org/extend/plugins/yak-for-wordpress/changelog/">changelog</a> each 
        time you upgrade.</p>

    <h3>Coding</h3>
    
    <p>Jason R Briggs</p>

    <h3>Design</h3>
    
    <p><a href="http://vizualbod.com/">František Malina</a></p>
    
    <h3>Additional Libraries/Resources</h3>
    
    <p><a href="http://teethgrinder.co.uk/open-flash-chart/index.php">Open Flash Chart</a></p>
    
    <h3>International Team</h3>
    
    <p><a href="http://www.mobitemple.net">Ronny</a> (JA, TW, ZH), Soichi Yokoyama (JA), AB (TH), František Malina (SK), Radek Kavan (CS), 
        MK (DE), Mark Teipe (FR), Romaric Drigon (FR), Roberto Mogliotti (IT), Rishi Giovanni Gatti (IT), Josep Jordana (ES), Tom Boersma (NO), 
        Marco Izzo (SE), Maciej Wolfart (PL), Álvaro Góis Santos (PT), Miha Novak (SI)</p>
    
    <h3>Testing, Ideas, Contributors, Investors, Generally-Helpful-People</h3>
    
    <p>Enrico Battocchi, Wes Chyrchel, AB, Lance Hodges, Luke Chao, František Malina, Ari Kontiainen, Chris Guthrie, Thomas Herold, <a href="http://artiststechguy.com">Brett MacDonald</a>, Brandon Parker</p>

</div>

<div>

<form name="settingsFrm2" method="post" action="#basic">

    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><?php _e('Maintenance mode', 'yak-admin') ?></label></th>
            <td><input type="checkbox" name="<?php echo MAINTENANCE_MODE ?>" <?php yak_html_checkbox($model[MAINTENANCE_MODE]) ?>
                    title="<?php _e('Turn on maintenance mode to disable all buy buttons in the system', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Show "Out of Stock" message', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>SHOW_OUT_OF_STOCK_MSG, 'selected'=>$model[SHOW_OUT_OF_STOCK_MSG], 'values'=>array("yes"=>"yes", "no"=>"no"),
                                'title'=>__('Should a message be shown if a product is out of stock?', 'yak-admin'))) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Custom "Out of Stock" message', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo CUSTOM_OUT_OF_STOCK_MSG ?>" value="<?php echo $model[CUSTOM_OUT_OF_STOCK_MSG] ?>"
                    title="<?php _e('Specify an alternate out of stock message. Leave this blank to use the default.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Confirmation Email', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo CONFIRMATION_EMAIL_ADDRESS ?>" value="<?php echo $model[CONFIRMATION_EMAIL_ADDRESS] ?>" size="40"
                    title="<?php _e('Email address (or addresses, comma-separated) to use for the order confirmation. Leave this blank if you don\'t want to send a confirmation message.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Confirmation Subject', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo CONFIRMATION_SUBJECT ?>" value="<?php echo $model[CONFIRMATION_SUBJECT] ?>" size="60" 
                    title="<?php _e('Subject to use for the order confirmation. Leave this blank to use the default (localised) subject "Order Confirmation".', 'yak-admin') ?>" /></td>
        </tr>
    
        <tr valign="top">
            <th scope="row"><?php _e('Confirmation Message', 'yak-admin') ?></th>
            <td><textarea cols="60" rows="8" name="<?php echo CONFIRMATION_MESSAGE ?>"
                    title="<?php _e('Enter the content of the order confirmation - either plain text or html. If the html tag is found YAK will automatically send the message as html.', 'yak-admin') ?>
<?php _e('Note, the following tags can be used in the message', 'yak-admin') ?>:<br />
<?php _e('[order_detail] for the order detail', 'yak-admin') ?>,<br />
<?php _e('[html_order_detail] for the order detail in html table format', 'yak-admin') ?>,<br />
<?php _e('[order_id] for the order number', 'yak-admin') ?>,<br />
<?php _e('[order_cost] for the total order cost', 'yak-admin') ?>,<br />
<?php _e('[payment_type] for the payment type', 'yak-admin') ?>,<br />
<?php _e('[shipping_type] for the shipping type', 'yak-admin') ?>,<br />
<?php _e('[shipping_address] for the shipping address', 'yak-admin') ?>,<br />
<?php _e('[billing_address] for the billing address', 'yak-admin') ?>,<br />
<?php _e('[html_shipping_address] for the shipping address in html format', 'yak-admin') ?>,<br />
<?php _e('[html_billing_address] for the billing address in html format', 'yak-admin') ?>,<br />
<?php _e('[name] for the recipient name', 'yak-admin') ?>,<br />
<?php _e('[phone] for the recipient\s phone number', 'yak-admin') ?>,<br />
<?php _e('[special_instructions] for any special instructions the customer entered', 'yak-admin') ?>"><?php echo $model[CONFIRMATION_MESSAGE] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th></th>
            <td><button id="test-confirmation-email" title="<?php _e('Note: This will also save any changes you have currently made to these configuration settings.', 'yak-admin') ?>"
                onclick="return testConfEmail('#<?php echo TEST_CONFIRMATION_EMAIL ?>', '#options_update2')"><?php _e('Test confirmation message', 'yak-admin') ?></button></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Default special instructions', 'yak-admin') ?></th>
            <td><textarea cols="40" rows="4" name="<?php echo DEFAULT_SPECIAL_INSTRUCTIONS ?>"
                    title="<?php _e('Special instructions appear on the final screen of the cart. Enter information for your customers here.', 'yak-admin') ?>"><?php echo $model[DEFAULT_SPECIAL_INSTRUCTIONS] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Terms &amp; conditions', 'yak-admin') ?></th>
            <td><textarea cols="40" rows="4" name="<?php echo TERMS_AND_CONDITIONS ?>"
                    title="<?php _e('Leave blank if you don\'t need to include a T&amp;C\'s checkbox', 'yak-admin') ?>"><?php echo $model[TERMS_AND_CONDITIONS] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Empty basket message', 'yak-admin') ?></th>
            <td><textarea cols="40" rows="4" name="<?php echo EMPTY_BASKET_MESSAGE ?>"
                    title="<?php _e('An alternative message to be displayed if the customer selects checkout without having added anything to their basket.
                       Leave blank to use the default message.', 'yak-admin') ?>"><?php echo $model[EMPTY_BASKET_MESSAGE] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Redirect on buy to', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>REDIRECT_ON_BUY_TO, 'selected'=>$model[REDIRECT_ON_BUY_TO], 'values'=>$model[PAGE_IDS],
                            'title'=>__('Select the shopping cart page to redirect to when the customer clicks on the buy button (leave blank to disable redirection)', 'yak-admin'))) ?></td>
        </tr>
                
        <tr valign="top">
            <th scope="row"><?php _e('Display product options', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo DISPLAY_PRODUCT_OPTIONS ?>" <?php yak_html_checkbox($model[DISPLAY_PRODUCT_OPTIONS]) ?>
                    title="<?php _e('Display product options drop-down if there is only a single option?', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Product category name', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>PRODUCT_CATEGORY_NAME, 'selected'=>$model[PRODUCT_CATEGORY_NAME], 'values'=>$model[CATEGORIES],
                                'title'=>__('The name of the category used for product posts (e.g. "products").  If you want a post to appear as a product you must use this category or a sub-category.', 'yak-admin'))) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Product page size', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo PRODUCT_PAGE_SIZE ?>" value="<?php echo $model[PRODUCT_PAGE_SIZE] ?>"
                    title="<?php _e('The number of products that should appear on the product page before we start "paging" (i.e. add previous/next links).', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Order number type', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>ORDER_NUMBER_TYPE, 'selected'=>$model[ORDER_NUMBER_TYPE], 'values'=>$order_number_types,
                            'title'=>__('Order numbers can be a basic sequence, or a randomised unique number.', 'yak-admin'))) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Use SSL checkout', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo USE_SSL ?>" <?php yak_html_checkbox($model[USE_SSL]) ?>
                    title="<?php _e('Use SSL during the checkout process (this will be automatically switched on if you choose credit card payment).', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Default duplicate handling', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>DUPLICATE_HANDLING, 'selected'=>$model[DUPLICATE_HANDLING], 'values'=>$duplicate_handling,
                            'title'=>__('What should happen when the customer tries to add a duplicate item to the cart?', 'yak-admin'))) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('AJAX Buy Button', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo AJAX_BUY_BUTTON ?>" <?php yak_html_checkbox($model[AJAX_BUY_BUTTON]) ?>
                    title="<?php _e('The buy button will use asynchronous javascript to submit (so the page won\'t reload when the customer hits the button).', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Hide Update button', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo HIDE_UPDATE_BUTTON ?>" <?php yak_html_checkbox($model[HIDE_UPDATE_BUTTON]) ?>
                    title="<?php _e('Tick the checkbox if you want to hide the Update button on the checkout.', 'yak-admin') ?>" /></td>
        </tr>
    </table>
    
    <input type="hidden" id="<?php echo TEST_CONFIRMATION_EMAIL ?>" name="<?php echo TEST_CONFIRMATION_EMAIL ?>" value="" />
    
    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
    <p class="submit">
        <input type="submit" id="options_update2" name="options_update2" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm2', 'basic')" />
    </p>
    <?php
        }
    ?>
</form>

</div>
 
<div>

<form name="settingsFrm3" method="post" action="#products">
    
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Auto discount', 'yak-admin') ?></th><!--/label-->
            <td><input type="text" name="<?php echo AUTO_DISCOUNT ?>" value="<?php echo $model[AUTO_DISCOUNT] ?>"
                    title="<?php _e('Enter a fraction, or 1 for no discount', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Currency', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>SELECTED_CURRENCY, 'selected'=>$model[SELECTED_CURRENCY], 'values'=>$currencies, 'array_index'=>5,
                            'title'=>__('The currency (including monetary format) to use in the cart', 'yak-admin'))) ?></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e('Price rounding', 'yak-admin') ?></th><!--/label-->
            <td><input type="text" name="<?php echo PRICE_ROUNDING ?>" value="<?php echo $model[PRICE_ROUNDING] ?>"
                    title="<?php _e('Enter the number of decimal places to round to (e.g. 2 decimal places, or 0 for currencies that don\'t typically use smaller units -- thai baht being an example)', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Include price in options dropdown', 'yak-admin') ?></th><!--/label-->
            <td><input type="checkbox" name="<?php echo OPTIONS_DROPDOWN_INCLUDE_PRICE ?>" <?php yak_html_checkbox($model[OPTIONS_DROPDOWN_INCLUDE_PRICE]) ?>
                    title="<?php _e('Tick the checkbox if you want to include the price in the buy button dropdown.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Hide quantity input', 'yak-admin') ?></th><!--/label-->
            <td><input type="checkbox" name="<?php echo HIDE_QUANTITY ?>" <?php yak_html_checkbox($model[HIDE_QUANTITY]) ?>
                    title="<?php _e('Tick the checkbox if you want to hide the quantity input box on the cart.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Empty quantity considered unlimited', 'yak-admin') ?></th><!--/label-->
            <td><input type="checkbox" name="<?php echo UNLIMITED_QUANTITY ?>" <?php yak_html_checkbox($model[UNLIMITED_QUANTITY]) ?>
                    title="<?php _e('Tick the checkbox if you want blank (ie. no) quantity to be considered infinite/unlimited.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Quantity Input Size', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo QUANTITY_INPUT_SIZE ?>" value="<?php echo $model[QUANTITY_INPUT_SIZE] ?>" 
                    title="<?php _e('Specify how many digits are allowed in a quantity input box', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Add quantity input to buy button', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo QUANTITY_INPUT_BUY_BUTTON ?>" <?php yak_html_checkbox($model[QUANTITY_INPUT_BUY_BUTTON]) ?>
                    title="<?php _e('Customer can enter the quantity they want to purchase before clicking the buy button.', 'yak-admin') ?>" /></td>
        </tr>
    </table>
    
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Low stock notification threshold', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo LOW_STOCK_THRESHOLD ?>" value="<?php echo $model[LOW_STOCK_THRESHOLD] ?>"
                    title="<?php _e('Threshold trigger when a notification email should be sent', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Low stock notification email', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo LOW_STOCK_EMAIL ?>" value="<?php echo $model[LOW_STOCK_EMAIL] ?>" size="40" 
                    title="<?php _e('Low stock email address', 'yak-admin') ?>" /></td>
        </tr>
    </table>

    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
    <p class="submit">
        <input type="submit" name="options_update3" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm3', 'products')" />
    </p>
    <?php
        }
    ?>
</form>
</div>

<div>

<form name="settingsFrm4" method="post" action="#download">
    <table class="form-table">    
        <tr valign="top">
            <th scope="row"><?php _e('Download uri', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo DOWNLOAD_URI; ?>" value="<?php echo $model[DOWNLOAD_URI] ?>" size="60"
                    title="<?php _e('Enter an override uri for the yak-dl.php file which will be sent to the customer. You only need to enter this if you use mod_rewrite (or equivalent) to change the actual path to the script. If that doesn\'t make sense to you, leave this blank.', 'yak-admin') ?>" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e('Download Email', 'yak-admin') ?></th>
            <td><textarea cols="40" rows="4" name="<?php echo DOWNLOAD_EMAIL; ?>"
                    title="<?php _e('A message containing a list of download URIs for purchased products ([downloads] is replaced with the URI list)', 'yak-admin') ?>"><?php echo $model[DOWNLOAD_EMAIL] ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Download Email address', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo DOWNLOAD_EMAIL_ADDRESS ?>" value="<?php echo $model[DOWNLOAD_EMAIL_ADDRESS] ?>" size="40"
                    title="<?php _e('Email address from which download messages are sent', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Use X-SendFile', 'yak-admin') ?></th>
            <td><input type="checkbox" name="<?php echo DOWNLOAD_USE_XSENDFILE ?>" <?php yak_html_checkbox($model[DOWNLOAD_USE_XSENDFILE]) ?>
                    title="<?php _e('Use mod_xsendfile to transmit the download files. Note that you will also need to change either .htaccess or your web server configuration (for advanced users).', 'yak-admin') ?>" /></td>
        </tr>
    </table>

    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
    <p class="submit">
        <input type="submit" name="options_update4" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm4', 'download')" />
    </p>
    <?php
        }
    ?>
</form>
</div>

<div>

<form name="settingsFrm5" method="post" action="#payments">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><strong><?php _e('Redirects for Payment', 'yak-admin') ?></strong></th>
            <td>
                <table id="payment_types" class="form-table">
                    <tr>
                        <th><?php _e('Type Name', 'yak-admin') ?> <button class="image-button" type="button" onclick="javascript:copyRow('#payment_types')"><img src="<?php echo $imgbase ?>/add.gif" alt="flip" border="0" /></button></th>
                        <th><?php _e('Redirect To', 'yak-admin') ?></th>
                    </tr>
<?php
if (isset($model[PAYMENT_TYPES])) {
    foreach ($model[PAYMENT_TYPES] as $key=>$value) { 
?>
                    <tr>
                        <td><input type="text" name="payment_type_names[]" value="<?php echo $key ?>" size="40" /></td>
                        <td><?php echo yak_html_select(array('name'=>'payment_type_redirects[]', 'selected'=>$value, 'values'=>$payment_pages)) ?></td>
                        <td></td>
                    </tr>
<?php 
    }
} 
?>
                    <tr>
                        <td><input type="text" name="payment_type_names[]" value="" size="40" /></td>
                        <td><?php echo yak_html_select(array('name'=>'payment_type_redirects[]', 'values'=>$payment_pages)) ?></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php
    do_action('yak-payment-settings');
    
    if (current_user_can('edit_yak_settings')) {
    ?>    
    <p class="submit">
        <input type="hidden" name="options_update5" value="options_update5" />
        <input type="submit" id="options_update5" name="options_update5" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm5', 'payments')" />
    </p>
    <?php
    }
    ?>
</form>
</div>

<div>

<form name="settingsFrm6" method="post" action="#advanced"> 
    <h3><?php _e('Miscellaneous', 'yak-admin') ?></h3>

    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Special options text', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo SPECIAL_OPTIONS_TEXT ?>" value="<?php echo $model[SPECIAL_OPTIONS_TEXT] ?>" 
                    title="<?php _e('Use special options text if you want to offer free gifts with a product (this will be the label next to the special options dropdown box presented to the customer). Add a custom field \'yak_special_options\' to the product with values separated by new lines.', 'yak-admin') ?>
                <?php _e('These values will be split and used in a drop down box for the customer to select.', 'yak-admin') ?> 
                <?php _e('When a customer selects a special option it is added to the meta data for the order.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('No caching', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('name'=>NO_CACHE_PAGES . '[]', 'selected'=>$model[NO_CACHE_PAGES], 'values'=>$model[PAGE_IDS], 'class'=>'" style="height: 10em', 'multiple'=>5,
                        'title'=>__('Specify the pages where caching should be turned off (in other words the checkout/cart page). Use this to stop Internet Explorer displaying the message "webpage is expired".', 'yak-admin'))) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Order Tracker Text', 'yak') ?></th>
            <td>
                <table> 
                <?php
                foreach ($order_statuses as $status) {
                    $name = 'yak_ordertracker_' . str_replace(' ', '_', $status);
                    $value = yak_get_option($name);
                ?>
                    <tr>
                        <td><?php echo yak_default(ucwords(strtolower($status)), __('Default/blank action', 'yak')) ?></td>
                        <td><input type="text" name="<?php echo $name ?>" value="<?php echo $value ?>" size="50" /></td>
                    </tr>
                <?php
                }
                ?>
                </table>
                <i><?php _e('Enter the text you want to display to customers for each order action on the order tracker.', 'yak') ?></i>
            </td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Customer Blacklist</th>
            <td><textarea name="<?php echo BLACKLIST ?>" cols="60" rows="10"
                    title="<?php _e('Enter suspect email addresses here - anyone using these addresses will get an error when trying to purchase. Separate each with a newline.', 'yak-admin') ?>"><?php echo $model[BLACKLIST] ?></textarea>
            <i></i></td>
        </tr>
    </table>
    
    <h3><?php _e('Remote Products', 'yak-admin') ?></h3>
    
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('HTTP Proxy Address', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo HTTP_PROXY_URL ?>" value="<?php echo $model[HTTP_PROXY_URL] ?>" size="60"
                    title="<?php _e('Enter the url to use on hosts which require http proxying of remote requests (note: currently only setup to work with CURL (if your server doesn\'t have php-curl installed this won\'t work).', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Remote Grab Server', 'yak-admin') ?></th>
            <td><input type="text" name="<?php echo REMOTE_GRAB_SERVER ?>" value="<?php echo $model[REMOTE_GRAB_SERVER] ?>" size="60"
                    title="<?php _e('Enter the remote server to use in association with the [yak_get_remote...] tag.', 'yak-admin') ?>  <?php _e('Include protocol, domain and port (e.g. http://www.myserver.com:8080)', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Remote Grab Path</th>
            <td><input type="text" name="<?php echo REMOTE_GRAB_PATH ?>" value="<?php echo $model[REMOTE_GRAB_PATH] ?>"
                    title="<?php _e('Enter the remote path to use in association with the [yak_get_remote...] tag. For example:  /wordpress or /mysite', 'yak-admin') ?>" /></td>
        </tr>
    </table>
    
    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
    <p class="submit">
        <input type="submit" name="options_update6" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm6', 'advanced')" />
    </p>
    <?php
        }
    ?>
</form>    
</div>

<div>

<form name="settingsFrm7" method="post" action="#promotions">
    <table id="promotions" class="form-table">
        <tr>
            <th><?php _e('Code / Threshold', 'yak-admin') ?><span class="yak_mandatory">*</span><br />
                <?php _e('User list', 'yak-admin') ?></th>
            <th><?php _e('Description', 'yak-admin') ?><br />
                <?php _e('Products list', 'yak-admin') ?></th>
            <th><?php _e('Type', 'yak-admin') ?><span class="yak_mandatory">*</span><br />
            <?php _e('Products Inclusion', 'yak-admin') ?></th>
            <th><?php _e('Value', 'yak-admin') ?><span class="yak_mandatory">*</span></th>
            <th><?php _e('Expiry', 'yak-admin') ?><br /><span style="font-size: x-small">(<?php _e('Year-Month-Day', 'yak-admin') ?>)</span></th>
            <th></th>
        </tr>
        <?php
        if (isset($model[PROMOTIONS])) {
            foreach ($model[PROMOTIONS] as $promo) {
                $expiry_year = substr($promo->expiry_date, 0, 4);
                $expiry_month = substr($promo->expiry_date, 5, 2);
                $expiry_day = substr($promo->expiry_date, 8, 2);
        ?>
        <tr>
            <td><input type="text" name="promo_code[]" value="<?php echo $promo->code ?>" /><input type="hidden" name="promo_id[]" value="<?php echo $promo->promo_id ?>" /><br />
                <input type="text" name="promo_users[]" value="<?php echo $promo->get_users_string() ?>" /></td>
            <td><input type="text" name="promo_description[]" value="<?php echo $promo->description ?>" maxlength="250" />
                <input type="text" name="promo_products[]" value="<?php echo $promo->get_products_string() ?>" /></td>
            <td><?php echo yak_html_select(array('name'=>'promo_type[]', 'selected'=>$promo->promo_type, 'values'=>$promo_types)) ?><br />
            <?php echo yak_html_select(array('name'=>'promo_products_inclusion[]', 'selected'=>$promo->products_inclusion, 'values'=>$product_inclusions)) ?></td>
            <td><input type="text" name="promo_value[]" value="<?php echo $promo->value ?>" /></td>
            <td><input type="date" name="promo_expiry[]" value="<?php echo $promo->expiry_date ?>" /></td>
            <td><button class="image-button" type="button" onclick="javascript:removeRow(this, 'promo_code')"><img src="<?php echo $imgbase ?>/delete.gif" alt="flip" border="0" /></button></td>
        </tr>
        <?php 
            }
        } 
        ?>
    </table>
    
    <p>&nbsp;</p>
    
    <hr />
    
    <h3><?php _e('Add a new promotion', 'yak-admin') ?></h3>
    
    <table id="new-promotion" class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Code', 'yak-admin') ?><span class="yak_mandatory">*</span></th>
            <td><input name="promo_code[]" id="new_promo_code" type="text" value="" size="40" aria-required="true"
                    title="<?php _e('The code for the promotion, or the name of a coupon set, or the threshold which triggers a promotion.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Description', 'yak-admin') ?></th>
    	    <td><input name="promo_description[]" id="new_promo_description" type="text" value="" size="60" aria-required="true"
    	            title="<?php _e('Short description of the promotion.', 'yak-admin') ?>" /></td>
        </tr>
    
        <tr valign="top">
            <th scope="row"><?php _e('Users', 'yak-admin') ?></th>
            <td><input name="promo_users[]" id="new_promo_users" type="text" value="" size="40" aria-required="true"
                    title="<?php _e('Comma-separated list of users who can activate this promo.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Products', 'yak-admin') ?></th>
            <td><input name="promo_products[]" id="new_promo_products" type="text" value="" size="40" aria-required="true"
                    title="<?php _e('Comma-separated list of products for which this promotion is valid.', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Products Inclusion', 'yak-admin') ?></th>
            <td><?php echo yak_html_select(array('id'=>'new_promo_products_inclusion', 'name'=>'promo_products_inclusion[]', 'values'=>$product_inclusions,
                        'selected'=>'1',
                        'title'=>__('Include the specified products in the promotion, or exclude them.', 'yak-admin') )) ?></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Type', 'yak-admin') ?><span class="yak_mandatory">*</span></th>
            <td><?php echo yak_html_select(array('id'=>'new_promo_type', 'name'=>'promo_type[]', 'values'=>$promo_types,
                        'title'=>__('Type of promotion.', 'yak-admin') )) ?></td>
    	</tr>
    	
    	<tr valign="top">
            <th scope="row"><?php _e('Value', 'yak-admin') ?><span class="yak_mandatory">*</span></th>
            <td><input type="text" name="promo_value[]" id="new_promo_value" value="" size="20" 
                    title="<?php _e('Value of the promotion (fixed value or percentage, depending upon the type of promotion).', 'yak-admin') ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Expiration date', 'yak-admin') ?></th>
            <td><input type="date" name="promo_expiry[]" id="new_promo_expiry" value=""
                    title="<?php _e('Date the promotion expires.', 'yak-admin') ?>" /></td>
        </tr>
    </table>
    
    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
    <p class="submit">
        <input type="submit" name="options_update7" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrm7', 'promotions')" />
    </p>
    <?php
        }
    ?>
</form>
</div>

</div><!--/tabs-container-->

</form>

<script type="text/javascript">
var $j = jQuery.noConflict();
$j(function() {
    setupAdminPage();
});
</script>