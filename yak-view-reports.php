<?php
/*
See yak-for-wordpress.php for information and license terms
*/
global $model;

$dir = yak_get_blogurl() . '/wp-content/plugins/yak-for-wordpress';
$jqplot_dir = $dir . "/resources/jqplot";

wp_enqueue_script('jqplot', "$jqplot_dir/jquery.jqplot.min.js", null, '1.0.0b2');
wp_register_style('jqplot-css', "$jqplot_dir/jquery.jqplot.min.css", null, '1.0.0b2', 'screen');
wp_enqueue_style('jqplot-css');

$data = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
foreach ($model['monthly_totals'] as $month) {
    $data[$month->desc - 1] = $month->total;
}
?>

<div class="wrap">
<form method="post" action="#">
<h2>Reports</h2>

<div class="tablenav">
<div class="alignleft">
Sales for: <?php echo yak_html_select(array('name'=>'year', 'selected'=>$model['year'], 'values'=>$model['years'], 'nokey'=>true)) ?>
<input type="submit" value="Requery" class="button" />
</div><br class="clear" />
</div><br class="clear" />


<div id="chart1" style="height:300px; width:100%;"></div>

<br />

<table class="widefat">
<thead>
        <tr>
        <th><?php _e('Month', 'yak-admin') ?></th>
        <th><?php _e('Total', 'yak-admin') ?>: <?php echo yak_format_money($model['year_total'], true) ?></th>
        </tr>
</thead>
<tbody>
<?php foreach ($model['monthly_totals'] as $mt) { ?>
        <tr>
        <td><?php echo yak_format_month($mt->desc - 1) ?></td>
        <td><?php echo yak_format_money($mt->total) ?></td>
        </tr>
<?php } ?>
</tbody>
</table>
    
<h3><?php _ye('Best sellers for %s', 'yak-admin', $model['year']) ?></h3>
<table class="widefat">
<thead>
        <tr>
        <th><?php _e('Product Title', 'yak-admin') ?></th>
        <th><?php _e('Total Sales', 'yak-admin') ?></th>
        <th><?php _e('Total Value', 'yak-admin') ?></th>
        </tr>
</thead>
<tbody>
<?php foreach ($model['year_best'] as $total) { ?>
        <tr>
        <td><?php echo $total->desc ?></td>
        <td><?php echo $total->total ?></td>
        <td><?php echo yak_format_money($total->secondary_total) ?></td>
        </tr>
<?php } ?>
</tbody>
</table>

    
<h3><?php _ye('Best sellers by month in %s', 'yak-admin', $model['year']) ?></h3>
<table class="widefat">
<thead>
        <tr>
        <th><?php _e('Product Title', 'yak-admin') ?></th>
        <th><?php _e('Total Sales', 'yak-admin') ?></th>
        <th><?php _e('Total Value', 'yak-admin') ?></th>
        </tr>
</thead>
<tbody>
<?php   
foreach ($model['month_best'] as $totals) { 
$month = $totals[0]->desc;
?>
        <tr>
        <th colspan="3"><?php echo $month ?></th>
        </tr>
<?php foreach ($totals as $total) { ?>
        <tr>    
        <td><?php echo $total->secondary_desc ?></td>
        <td><?php echo $total->total ?></td>
        <td><?php echo yak_format_money($total->secondary_total) ?></td>
        </tr>
<?php } } ?>
</tbody>
</table>
</form>

</div>

<script type="text/javascript">
var data = [[
<?php
    for ($x = 0; $x < count($data); $x++) {
        echo $data[$x] . ',';
    }
?>
]];

var $j = jQuery.noConflict();
$j(document).ready(function() {
    var plot1 = $j.jqplot('chart1', data, {
        axes: {
            xaxis: {
                ticks: [ [0, ''], [1, 'Jan'], [2, 'Feb'], [3, 'Mar'], [4, 'Apr'], [5, 'May'], [6, 'Jun'],
                        [7, 'Jul'], [8, 'Aug'], [9, 'Sep'], [10, 'Oct'], [11, 'Nov'], [12, 'Dec'], [13, ''] ]
            }
        }
    });
});
</script>