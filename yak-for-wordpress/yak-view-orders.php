<?php
/*
See yak-for-wordpress.php for information and license terms
*/
global $model, $countries;

if (empty($_POST['orders_query']) && empty($_POST['year_order_date'])) {
    $year_order_date = date('Y');
}
else {
    $year_order_date = $_POST['year_order_date'];
}

$order_actions = array(
    ''=>'', 
    'payment_processed' => 'Payment Processed', 
    'send_stock' => 'Send Stock',
    'cancel_order' => 'Cancel Order',
    'reset' => 'Reset',
    'delete' => 'Delete',
    'refund' => 'Refund'
);

$order_types = array(
    '' => '%',
    __('show fulfilled orders', 'yak-admin') => STOCK_SENT,
    __('show paid orders', 'yak-admin') => PAYMENT_PROCESSED,
    __('show unfulfilled orders', 'yak-admin') => '',
    __('show cancelled orders', 'yak-admin') => CANCELLED,
    __('show error orders', 'yak-admin') => ERROR,
    __('show refunded orders', 'yak-admin') => REFUNDED
);

$selected_type = '%';
if (isset($_POST['status'])) {
    $selected_type = $_POST['status'];
}

$payment_types = yak_get_order_payment_types();
?>

<div class="wrap">
<form method="post" action="#">
<h2>Orders</h2>
  
<div class="tablenav">
    <div class="alignleft">
        <table>
            <tr>
                <td><?php _e('Type of order', 'yak-admin') ?></td>
                <td><?php echo yak_html_select(array('id'=>'status', 'name'=>'status', 'selected'=>$selected_type, 'values'=>$order_types, 'reverse_key_value'=>true)) ?></td>
                <td>&nbsp;</td>
                <td><?php _e('Payment Type', 'yak-admin') ?></td>
                <td><?php echo yak_html_select(array('id'=>'payment_type', 'name'=>'payment_type', 'selected'=>yak_default($_POST['payment_type'], ''), 'values'=>$payment_types)) ?></td>
            </tr>
            <tr>
                <td><?php _e('Date', 'yak-admin') ?></td>
                <td><?php yak_date_control('order_date', $year_order_date, $_POST['month_order_date'], '', false) ?></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><?php _e('Search', 'yak-admin') ?></td>
                <td colspan="4"><input type="text" id="search" name="search" value="<?php echo $_POST['search'] ?>" size="50" /></td>
            </tr>
        </table>
    </div>
    <?php do_action('yak-order-form', array()) ?>
</div>

<p class="clear">&nbsp;</p>

<p><input type="submit" name="orders_query" value="<?php _e('Find orders', 'yak-admin') ?>" class="button-secondary" />
    <?php
        if (current_user_can('edit_yak_orders')) {
    ?>
    <input type="submit" name="orders_update" value="<?php _e('Update orders', 'yak-admin') ?>" class="button" />
    <?php
        }
    ?>
</p>

<p class="clear">&nbsp;</p>

<?php if ($orders) { ?>  
<table class="widefat collapsible">
    <thead>
        <tr>
            <th scope="col"><?php _e('Order #', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Date', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Order Value', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Payment Type', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Funds Received', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Status', 'yak-admin') ?></th>
            <th scope="col"><?php _e('Action', 'yak-admin') ?></th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
<?php 
    $count = 0;
    foreach ($orders as $order) {
        $count++;
        $class = 'even' == $class ? '' : 'even';
        if (!empty($model['messages'][$order->id])) {
            $msg = '<p class="error">' . $model['messages'][$order->id] . '</p>';
            $expand_class = "expanded";
        }
        else {
            $msg = "";
            $expand_class = "expand-child";
        }
?>
    <tr class="<?php echo $class ?>">
        <td><?php echo $order->order_num ?><input type="hidden" name="id[]" value="<?php echo $order->id ?>" /></td>
        <td><?php echo $order->time ?></td>
        <td class="yak_right"><?php echo yak_format_money($order->total + $order->shipping_cost, 2) ?></td>
        <td><?php echo $order->payment_type ?></td>
        <td><input class="yak_right" type="text" name="funds_received[]" value="<?php echo $order->funds_received ?>" />
            <input class="yak_right" type="hidden" name="original_funds_received[]" value="<?php echo $order->funds_received ?>" /></td>
        <td><?php echo $order->status ?></td>
        <td><?php echo yak_html_select(array('name'=>'action[]', 'values'=>$order_actions)) ?></td>
        <td class="collapsible"></td>
    </tr>
    <tr class="<?php echo "$expand_class $class" ?>">
        <td colspan="8"><?php echo $msg ?>
            <table class="lowlight">
                <tr>
                    <td width="25%"><h4><?php _e('Shipping Address', 'yak-admin') ?></h4>
                        <pre><?php echo $order->get_shipping_address_string() ?></pre>
                    </td>
                    <td width="25%"><h4><?php _e('Billing Address', 'yak-admin') ?></h4>
                        <pre><?php echo $order->get_billing_address_string() ?></pre>
                    </td>
                    <td width="50%"><h4><?php _e('Order Details', 'yak-admin') ?></h4>
                        <table class="widefat">
                            <thead>              
                                <tr>
                                    <th><?php _e('Item', 'yak-admin') ?></th>
                                    <th><?php _e('Qty', 'yak-admin') ?></th>
                                    <th><?php _e('Price', 'yak-admin') ?></th>
                                    <th><?php _e('Subtotal', 'yak-admin') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                foreach ($order->items as $item) {
                                    if ($item->post_id == null) {
                                        continue;
                                    } 
                                    $classy = 'even' == $classy ? '' : 'even';
                            ?>
                                <tr class="<?php echo $classy ?>">
                                    <td><?php 
                                        echo $item->itemname;
                                        if (count($item->meta) > 0) {
                                            foreach ($item->meta as $key=>$val) {
                                                echo '<br />&nbsp;&nbsp;' . $key . ': ' . $val;
                                            }
                                        } ?></td>
                                    <td><?php echo $item->quantity ?></td>
                                    <td class="yak_numeric"><?php echo yak_format_money($item->price) ?></td>
                                    <td class="yak_numeric"><?php echo yak_format_money($item->total) ?></td>
                                </tr>
                            <?php 
                                } 
                            ?>
                            </tbody>
                        </table>
                        <p><strong><?php _e('Shipping costs', 'yak-admin') ?> (<?php echo $order->selected_shipping_type ?>):</strong> <?php echo yak_format_money($order->shipping_cost) ?><br />
                        <?php
                            foreach ($order->items as $item) {
                                if ($item->post_id != null) {
                                    continue;
                                } 
                        ?>
                        <strong><?php echo $item->itemname ?>:</strong> <?php echo yak_format_money($item->total) ?><br />
                        <?php  
                            }
                        ?>
                        <strong><?php _e('Total', 'yak-admin') ?>:</strong> <?php echo yak_format_money($order->total + $order->shipping_cost) ?></p>       
                    </td>
                </tr>
            </table>

            <?php if (isset($order->meta) && count($order->meta) > 0) { ?>
            <h4><?php _e('Additional Information', 'yak-admin') ?></h4>
            <table width="100%" class="lowlight">
            <?php foreach ($order->meta as $name=>$val) { 
                $meta_key = str_replace(' ', '-', $name);
                $meta_value = apply_filters('yak-display-meta-value-' . $meta_key, $val);
                ?>
                <tr>
                    <td width="25%"><?php echo $name ?></td>
                    <td id="<?php echo "row" . $count . "-" . str_replace(' ', '-', $name) ?>" width="75%"><?php echo $meta_value ?></td>
                </tr>
            <?php } ?>
            </table>
            <?php } ?>
        
            <?php if (isset($order->log) && count($order->log) > 0) { ?>
            <h4><?php _e('Order Log', 'yak-admin') ?></h4>
            <table width="100%" class="lowlight">
            <?php foreach ($order->log as $log) { ?>
                <tr>
                    <td width="25%"><?php echo $log->time ?></td>
                    <td width="75%"><?php echo $log->message ?></td>
                </tr>
            <?php } ?>
            </table>
        <?php } ?>

        <p><strong>Add Note:</strong> <input type="text" name="note[]" value="" size="120" /></p>
       
        </td>
    </tr>
    <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="8"><?php _e('Number of orders: ', 'yak-admin') ?><span id="number-of-orders"><?php echo $count ?></span></th>
        </tr>
    </tfoot>
</table>
<?php } ?>
<p><a id="export-data-link" target="BLANK" onclick="return exportData('<?php echo yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress/yak-orders-dl.php?type=excel&n=' . rand(1, 1000000) ?>')">Export data</a></p>
</form>
</div>