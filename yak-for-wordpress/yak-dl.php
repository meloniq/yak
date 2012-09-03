<?php
/*
See yak-for-wordpress.php for information and license terms
*/
error_reporting(E_ALL ^ E_DEPRECATED);
require_once('yak-standalone.php');

global $wpdb, $order_dl_table;

$uid = $_GET['uid'];

$sql = $wpdb->prepare("select * from $order_dl_table where uid = %s", $uid);
$order_dl = $wpdb->get_row($sql);

$addr = $_SERVER[REMOTE_ADDR];

if (isset($order_dl->download_address) && $order_dl->download_address != '' && $order_dl->download_address != $addr) {
    die('Unable to download, there was a previous download attempt from another ip address');
}

$filename = $order_dl->dl_file;

if (strstr($_SERVER['HTTP_USER_AGENT'], "Opera")) {
    $browser = "Opera";
}
else if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
    $browser = "IE";
}
else {
    $browser = '';
}

$sfilename = basename($filename);
if ($browser == 'IE') {
    $sfilename = preg_replace('/\./', '%2e', $sfilename, substr_count($sfilename, '.') - 1);
}

$mime_type = ($browser == 'IE' || $browser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
$disposition = $browser == 'IE' ? 'inline' : 'attachment';

// make sure the file exists before sending headers
if (!$fdl = @fopen($filename, 'rb')) {
    error_log("Unable to open file " . $filename);
    die("Sorry, an error occurred, unable to open the file");
}

$size = intval(sprintf("%u", filesize($filename)));     

$use_xsendfile = yak_get_option(DOWNLOAD_USE_XSENDFILE, 'off');

if ($use_xsendfile == 'on') {
    header("X-Sendfile: " . $filename);
}
header("Pragma: ");
header("Cache-Control: ");
header("Expires: 0");
header("Content-Type: $mime_type");
header("Content-Disposition: $disposition; filename=" . $sfilename);
header("Content-Transfer-Encoding: 8-bit");
header("Content-Length:" . $size);
sleep(1);

if (empty($use_xsendfile) || $use_xsendfile != 'on') {
    $chunksize = 1 * (1024 * 1024);
    if ($size > $chunksize) { 
        $buffer = '';
        while (!feof($fdl)) { 
            $buffer = fread($fdl, $chunksize); 
            echo $buffer; 
            ob_flush(); 
            flush(); 
        }
    }
    else {
        fpassthru($fdl);
    }
}
fclose($fdl);

$sql = $wpdb->prepare("update $order_dl_table set download_address = %s, download_attempts = download_attempts + 1 
                       where uid = %s", $addr, $uid);
$wpdb->query($sql);
yak_insert_orderlog($order_dl->order_id, "Download from ip address $addr");
?>