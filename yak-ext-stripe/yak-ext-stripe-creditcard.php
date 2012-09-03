<?php
/*
See yak-for-wordpress.php for information and license terms
*/
require_once('yak-ext-stripe.php');
require_once(ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-creditcard.php');

if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}

global $model;

$api_key = yak_get_option(STRIPE_PUBLIC_KEY, '');

$expiry_months = yak_get_expiry_months();
$expiry_years = yak_get_expiry_years();
?>
<script type="text/javascript" src="https://js.stripe.com/v1/"></script>

<div id="yak_error" class="yak_error"></div>

<form id="cc" method="post" action="<?php echo yak_get_permalink('stage=cc') ?>">
    <table class="yak_left">
        <tr>
            <td colspan="2"><?php _e('Please enter your credit card details', 'yak') ?></td>
        </tr>
        <tr>
            <td><?php echo _e('Credit Card No.', 'yak') ?> : </td>
            <td><input type="text" autocomplete="off" name="cc_number" id="card-number" size="20" /></td>
        </tr>
        <tr>
            <td><?php echo _e('Security Code', 'yak') ?>
                <a style="font-size: x-small" href="http://en.wikipedia.org/wiki/Card_Security_Code" target="_BLANK">[?]</a> : </td>
            <td><input type="text" autocomplete="off" id="card-cvc" size="6" maxlength="4" /></td>
        </tr>
        <tr>
            <td><?php echo _e('Expiry Date', 'yak') ?> : </td>
            <td><?php echo yak_html_select(array('values'=>$expiry_months, 'id'=>'card-expiry-month')) ?> <?php echo yak_html_select(array('values'=>$expiry_years, 'id'=>'card-expiry-year')) ?></td>
        </tr>
        <tr>
            <td colspan="2"><button type="button" id="confirmbutton" class="yak_medium_button" onclick="stripeSubmit()"><?php _e('Next', 'yak') ?></button><br />
                <span class="yak_small"><?php echo _e('Note: You have a final chance to confirm/cancel the order on the following screen.', 'yak')?></span></td>
        </tr>
    </table>        
    <input type="hidden" name="action" value="redirect_to_confirm" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />

    <script type="text/javascript">
        var $j = jQuery.noConflict();
    
        Stripe.setPublishableKey('<?php echo $api_key ?>');
        
        function stripeResponseHandler(status, response) {
            $j('#confirmbutton').removeAttr('disabled');
            if (response.error) {
                $j('#yak_error').html('<p>' + response.error.message + '</p>');
            }
            else {
                var form = $j('#cc');
                var token = response['id'];
                form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                form.submit();
            }
        }
        
        function stripeSubmit() {
            $j('#confirmbutton').attr('disabled', 'disabled');
            
            var ccNum = $j('#card-number').val();
            var cvcNum = $j('#card-cvc').val();
            var month = $j('#card-expiry-month').val();
            var year = $j('#card-expiry-year').val();
            
            Stripe.createToken({
                number: ccNum,
                cvc: cvcNum,
                exp_month: month,
                exp_year: year
            }, stripeResponseHandler);
            return false;
        }
    </script>
    
    <?php
        include ABSPATH . 'wp-content/plugins/yak-for-wordpress/yak-view-shipping-snippet.php';
    ?>
</form>