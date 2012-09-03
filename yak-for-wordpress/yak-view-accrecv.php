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

$label = yak_get_option(ACC_RECV_LABEL, 'Account');

$msg = __('Please enter your %LABEL% details', 'yak');
$msg = str_replace('%LABEL%', $label, $msg);

$number_label = __('%LABEL% number', 'yak');
$number_label = str_replace('%LABEL%', $label, $number_label);

$name_label = __('%LABEL% name', 'yak');
$name_label = str_replace('%LABEL%', $label, $name_label);

if (isset($model['error_message'])) {
?>

<div class="yak_error"><?php echo $model['error_message'] ?></div>

<?php
}
?>

<form name="accrecv" method="post" action="<?php echo yak_get_permalink('stage=accrecv') ?>">
    <table class="yak_left">
        <tr>
            <td colspan="2"><?php echo $msg ?></td>
        </tr>
        <tr>
            <td><?php echo $number_label ?> : </td>
            <td><input type="text" name="accrecv_number" value="<?php echo $_POST['accrecv_number'] ?>" size="20" /></td>
        </tr>
        <tr>
            <td><?php echo $name_label ?> : </td>
            <td><input type="text" name="accrecv_name" value="<?php echo $_POST['accrecv_name'] ?>" size="20" maxlength="40" /></td>
        </tr>
        <tr>
            <td colspan="2"><button id="confirmbutton" class="yak_medium_button" type="submit"><?php _e('Next', 'yak') ?></button></td>
        </tr>
    </table>        
    <input type="hidden" name="action" value="confirm_accrecv" />
    <input type="hidden" name="<?php echo $model['param_name'] ?>" value="<?php echo $model['post_id'] ?>" />
<?php
    include 'yak-view-shipping-snippet.php';
?>
</form>
