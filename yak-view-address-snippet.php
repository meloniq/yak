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

$sep_billing = (yak_get_option(ADDRESS_SEPARATE_BILLING, '') == 'on');

?>
<table width="100%">
    <tr>
        <th class="yak_small"><?php _e('Shipping Address','yak') ?></th>
        <?php if ($sep_billing) { ?>
        <th class="yak_small"><?php _e('Billing Address','yak') ?></th>
        <?php } ?>
    </tr>
    <tr>
        <td class="yak_small">
<?php
echo str_replace("\n", '<br />', $model['shipping_address']->as_string('country'));
echo '<br />';
echo yak_get_country($model['shipping_address']->country) . '<br />';
?>
        </td>
<?php
    if ($sep_billing) {
?>
        <td class="yak_small">
<?php
        echo str_replace("\n", '<br />', $model['billing_address']->as_string('country'));
        echo '<br />';
        echo yak_get_country($model['billing_address']->country) . '<br />';
?>
        </td>
<?php
    }
?>
    </tr>
</table>