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

global $model;

if (isset($model['error_message'])) {
?>

<div class="yak_error"><?php echo $model['error_message'] ?></div>

<?php
}
?>

<form name="cc" method="post" action="<?php echo yak_get_permalink('stage=cc') ?>">
    <table class="yak_left">
        <tr>
            <td colspan="2"><?php _e('Please enter your credit card details', 'yak') ?></td>
        </tr>
        <tr>
            <td><?php echo _e('Card Type', 'yak') ?> : </td>
            <td><?php echo yak_html_select(array('name'=>'cc_type', 'values'=>$model['cc_types'])) ?></td>
        </tr>
        <tr>
            <td><?php echo _e('Credit Card No.', 'yak') ?> : </td>
            <td><input type="text" autocomplete="off" name="cc_number" size="20" /></td>
        </tr>
        <tr>
            <td><?php echo _e('Security Code', 'yak') ?>
                <a style="font-size: x-small" href="http://en.wikipedia.org/wiki/Card_Security_Code" target="_BLANK">[?]</a> : </td>
            <td><input type="text" autocomplete="off" name="cc_security_code" size="6" maxlength="4" /></td>
        </tr>
        <tr>
            <td><?php echo _e('Cardholder\'s Name', 'yak') ?> : </td>
            <td><input type="text" name="cc_name" size="20" maxlength="40" /></td>
        </tr>
        <tr>
            <td><?php echo _e('Expiry Date', 'yak') ?> : </td>
            <td><?php echo yak_html_select(array('name'=>'cc_expiry_month', 'values'=>$model['cc_expiry_months'])) ?> <?php echo yak_html_select(array('name'=>'cc_expiry_year', 'values'=>$model['cc_expiry_years'])) ?></td>
        </tr>
        <tr>
            <td colspan="2"><button id="confirmbutton" class="yak_medium_button" type="submit"><?php _e('Next', 'yak') ?></button><br />
                <span class="yak_small"><?php echo _e('Note: You have a final chance to confirm/cancel the order on the following screen.', 'yak')?></span></td>
        </tr>
    </table>        
    <input type="hidden" name="action" value="confirm_cc" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
<?php
    include 'yak-view-shipping-snippet.php';
?>
</form>
