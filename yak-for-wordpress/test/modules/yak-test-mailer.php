<?php
/*
Plugin Name: YAK Add-on Module - Test Mailer
Description: Test mail module for YAK-for-WordPress
Version: 1.0
Author: A Filly Ate It
Author URI: http://www.afillyateit.com
*/
global $yak_mail_table;
$yak_mail_table = 'wp_yak_test_mail';

function yak_testmailer($attrs) {
    return 'yak_test_sendmail';
}

function yak_test_sendmail($from, $to, $subject, $message) {
    global $wpdb, $yak_mail_table;
    $sql = $wpdb->prepare("insert into $yak_mail_table (from_address, to_address, subject, message)
                  values (%s, %s, %s, %s)", $from, $to, $subject, $message);
    $wpdb->query($sql);
}

function yak_testmailer_install() {
    global $wpdb, $yak_mail_table;
    
    if ($wpdb->get_var("show tables like '$yak_mail_table'") != $yak_mail_table) {
        $sql = "create table $yak_mail_table (
                id mediumint(9) primary key not null auto_increment,
                from_address varchar(255),
                to_address varchar(255),
                subject varchar(255),
                message text
                ) $charset_collate;";
        yak_log("creating table $yak_mail_table");
        $wpdb->query($sql);
    }
}

if (function_exists('add_filter')) {
    add_filter('yak-mailer', 'yak_testmailer');
    add_action('activate_yak-test-mailer.php', 'yak_testmailer_install');
}
else {
    error_reporting(E_ALL ^ E_DEPRECATED);
    require_once('yak-for-wordpress/yak-standalone.php');
    
    global $wpdb, $yak_mail_table;
    
    $action = $_GET['mail-action'];
    if ($action == 'delete-all') {
        $wpdb->query("delete from $yak_mail_table");
        $wpdb->query("alter table $yak_mail_table auto_increment=1");
    }
    
    $id = $_GET['mail-id'];
    
    echo '<html><body><table border="1">
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>To</th>
                <th>Subject</th>
            </tr>';
    
    $sql = $wpdb->prepare("select * from $yak_mail_table order by id desc");
    
    $msg = null;
    
    $count = 0;
    foreach ($wpdb->get_results($sql) as $row) {
        $count++;
        $url = $_SERVER['PHP_SELF'] . '?mail-id=' . $row->id;
        $row_id = 'row_' . $count;
        echo "<tr>
            <td id=\"$row_id\"><a href=\"$url\">$row->id</a></td>
            <td>$row->from_address</td>
            <td>$row->to_address</td>
            <td>$row->subject</td>
        </tr>";
        
        if (!empty($id) && $row->id == $id) {
            $from = $row->from_address;
            $to = $row->to_address;
            $subject = $row->subject;
            $msg = $row->message;
        }
    }
    
    echo '</table>';

    if (!empty($msg)) {
        if (yak_str_contains($msg, '<html')) {
            $msg = str_replace('</html>', '', str_replace('<html>', '', $msg));
            $msg = str_replace('</body>', '', str_replace('<body>', '', $msg));
        }
        else {
            $msg = '<pre>' . $msg . '</pre>';
        }
        
        echo "<p>From: $from <br />
To: $to <br />
Subject: $subject </p>
<p>
$msg
</p>";
    }
    
    echo '</body></html>';
}
?>