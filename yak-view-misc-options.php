<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-utils.php');

global $model;

$imgbase = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/images';

wp_nonce_field('update-options');
?>

<br />
<input type="hidden" name="section" value="options" />

<ul class="tabs">
    <li><a id="analytics-tab" href="#analytics"><span><?php _e('Google Analytics', 'yak-admin') ?></span></a></li>
    <li><a id="resenddl-tab" href="#resenddl"><span><?php _e('Resend DL', 'yak-admin') ?></span></a></li>
    <?php 
        do_action('yak-misc-options-link');
    ?>
</ul>

<div class="panes">
    <div>
        <form name="settingsFrmAnalytics" method="post" action="#analytics">

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><strong><?php _e('Analytics Profile ID', 'yak-admin') ?></strong></th>
                <td><input type="text" name="<?php echo GOOGLE_ANALYTICS_ID ?>" value="<?php echo yak_get_option(GOOGLE_ANALYTICS_ID, '') ?>" size="20"
                        title="<?php _e('Google Analytics Profile ID. Make sure that you include the tracker code for this ID in your theme.', 'yak-admin') ?>" /></td>
            </tr>

            <tr>
                <th scope="row"><strong><?php _e('Tax Calc', 'yak-admin') ?></strong></th>
                <td><input type="text" name="<?php echo GOOGLE_ANALYTICS_TAX_CALC ?>" value="<?php echo yak_get_option(GOOGLE_ANALYTICS_TAX_CALC, '') ?>" size="5"
                        title="<?php _e('Enter the fraction (i.e. 0.1) to use for tax calculation', 'yak-admin') ?>" /></td>
            </tr>

            <tr>
                <th scope="row"><strong><?php _e('Affiliation', 'yak-admin') ?></strong></th>
                <td><input type="text" name="<?php echo GOOGLE_ANALYTICS_AFFILIATION ?>" value="<?php echo yak_get_option(GOOGLE_ANALYTICS_AFFILIATION, '') ?>" size="30"
                        title="<?php _e('Enter the affiliation (i.e. if you want to separate orders from your YAK store from orders through another mechanism)', 'yak-admin') ?>" /></td>
            </tr>
        </table>
    
    <?php
        if (current_user_can('edit_yak_settings')) {
    ?>
        <p class="submit">
            <input type="hidden" name="update-options-google-analytics" value="update-options-google-analytics" />
            <input type="submit" id="options_update_analytics" name="options_update_analytics" value="<?php _e('Update options', 'yak-admin') ?>" onclick="return submitSettingsForm('settingsFrmAnalytics', 'analytics')" />
        </p>
    <?php
        }
    ?>
        </form>
    </div>

    <div id="resenddl" class="tabs-container">    
    <form name="settingsResendDL" method="post" action="#resenddl">
        <p><?php _e('Use this page to re-send the download link to customers when you\'ve updated the download file.', 'yak-admin') ?></p>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Product', 'yak-admin') ?></th>
                <td><select name="product" title="<?php echo _e('Select the downloadable product to re-send.', 'yak-admin') ?>">
                    <?php
                    $products = yak_get_products('date', 'desc', null);
                    foreach ($products as $ppost) {
                        $types = yak_get_product_categories($ppost->id, $ppost->status, true, true);
                    
                        foreach ($types as $type) {
                            if (!empty($type->dl_file)) {
                                $key = $ppost->id . ',' . $type->cat_id;
                                if ($_REQUEST['product'] == $key) {
                                    $selected = 'selected="selected"';
                                }
                                else {
                                    $selected = "";
                                }
                                $cat = "";
                                if (!empty($type->cat_name)) {
                                    $cat = ' (' . $type->cat_name . ')';
                                }
                                echo '<option value="' . $key . '" ' . $selected . '>' . $ppost->post_title . $cat . '</option>';
                            }
                        }
                    }
                    ?>
                    </select></td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Subject', 'yak-admin') ?></th>
                <td><input name="subject" value="<?php echo stripslashes($_REQUEST['subject']) ?>" size="50"
                        title="<?php _e('Subject line for the email.', 'yak-admin') ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Email', 'yak-admin') ?></th>
                <td><textarea name="email" cols="60" rows="10" 
                        title="<?php _e('A message containing the download URI for the updated product ([downloads] is replaced with the URI - same as the standard downloads email)', 'yak-admin') ?>"><?php echo stripslashes($_REQUEST['email']) ?></textarea></td>
            </tr>
        </table>
    
        <?php
            $count = $_REQUEST['email_count'];
            if (!empty($count)) {
                echo '<p>' . $count . '</p><p>';
                $prod = explode(',', $_REQUEST['product']);
                $rows = yak_get_order_details($prod[0], $prod[1]);
                foreach ($rows as $row) {
                    echo $row->email_address . '<br />';
                }
                echo '</p>';
            }
        ?>

        <?php
            if (current_user_can('edit_yak_settings')) {
        ?>
        <p class="submit">
            <input type="hidden" name="update-options-resenddl" value="update-options-resenddl" />
            <input type="submit" id="options_update_resenddl" name="yak-resenddl" value="<?php _e('Check', 'yak-admin') ?>" />
            <input type="submit" id="options_update_resenddl" name="yak-resenddl" value="<?php _e('Send', 'yak-admin') ?>" />
        </p>
        <?php
            }
        ?>
    </form>
    </div>


<?php 
    do_action('yak-misc-options');
?>

</div>

<script type="text/javascript">
var $j = jQuery.noConflict();
$j(function() {
    setupAdminPage();
});
</script>